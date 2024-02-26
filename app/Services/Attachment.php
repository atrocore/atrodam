<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

declare(strict_types=1);

namespace Dam\Services;

use Atro\Core\AssetValidator;
use Atro\Core\Exceptions\SuchAssetAlreadyExists;
use Espo\Core\Exceptions\Error;
use Espo\Core\FilePathBuilder;
use Espo\ORM\Entity;
use Imagick;

/**
 * Class Attachment
 *
 * @package Dam\Services
 */
class Attachment extends \Espo\Services\Attachment
{
    /**
     * @param Imagick $imagick
     *
     * @return string|null
     */
    public static function getColorSpace(Imagick $imagick): ?string
    {
        $colorId = $imagick->getImageColorspace();

        if (!$colorId) {
            return null;
        }

        foreach ((new \ReflectionClass($imagick))->getConstants() as $name => $value) {
            if (stripos($name, "COLORSPACE_") !== false && $value == $colorId) {
                $el = explode("_", $name);
                array_shift($el);

                return implode("_", $el);
            }
        }

        return null;
    }

    public function createEntityByUrl(string $url, bool $validateAttachment = true): \Dam\Entities\Attachment
    {
        // parse url
        $parsedUrl = parse_url($url);

        // prepare filename
        $filename = basename($parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path']);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            if (!empty($query['filename'])) {
                $filename = $query['filename'];
            }
        }

        $attachment = new \stdClass();
        $attachment->name = $filename;
        $attachment->relatedType = 'Asset';
        $attachment->field = 'file';
        $attachment->storageFilePath = $this->getEntityManager()->getRepository('Attachment')->getDestPath(FilePathBuilder::UPLOAD);
        $attachment->storageThumbPath = $this->getEntityManager()->getRepository('Attachment')->getDestPath(FilePathBuilder::UPLOAD);

        $fullPath = $this->getConfig()->get('filesPath', 'upload/files/') . $attachment->storageFilePath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $attachment->fileName = $fullPath . '/' . $attachment->name;

        // load file from url
        set_time_limit(0);
        $fp = fopen($attachment->fileName, 'w+');
        if ($fp === false) {
            throw new Error(sprintf($this->getInjection('language')->translate('fileResourceWriteFailed', 'exceptions', 'Asset'), $attachment->name));
        }
        $ch = curl_init(str_replace(" ", "%20", $url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!in_array($responseCode, [200, 201])) {
            if (file_exists($attachment->fileName)) {
                unlink($attachment->fileName);
            }
            throw new Error(sprintf($this->getInjection('language')->translate('urlDownloadFailed', 'exceptions', 'Asset'), $url));
        }

        $entity = parent::createEntity($attachment);

        if ($validateAttachment) {
            $this->validateAttachment($entity, $attachment);
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function createEntity($attachment)
    {
        $entity = parent::createEntity($attachment);

        if ($this->attachmentHasAsset($attachment)) {
            $this->validateAttachment($entity, $attachment);
            $this->createAsset($entity, $attachment);
        }

        return $entity;
    }

    public function findAttachmentDuplicate(\stdClass $attachment): ?Entity
    {
        // skip duplicates checking for stream attachments
        if (property_exists($attachment, 'relatedType') && $attachment->relatedType === 'Note') {
            return null;
        }

        $duplicateParam = $this->getConfig()->get('attachmentDuplicates', 'notAllowByContentAndName');

        if ($duplicateParam == 'notAllowByContent' && property_exists($attachment, 'md5')) {
            return $this->getRepository()->where(['md5' => $attachment->md5])->findOne();
        }

        if ($duplicateParam == 'notAllowByName' && property_exists($attachment, 'name')) {
            return $this->getRepository()->where(['name' => $attachment->name])->findOne();
        }

        if ($duplicateParam == 'notAllowByContentAndName' && property_exists($attachment, 'md5') && property_exists($attachment, 'name')) {
            return $this->getRepository()->where(['md5' => $attachment->md5, 'name' => $attachment->name])->findOne();
        }

        return null;
    }

    public function attachmentHasAsset(\stdClass $input = null): bool
    {
        if (!empty($input) && property_exists($input, 'relatedType') && $input->relatedType === 'Note') {
            return false;
        }

        if (property_exists($input, 'relatedType') && property_exists($input, 'field')) {
            if ($this->getMetadata()->get(['entityDefs', $input->relatedType, 'fields', $input->field, 'noAsset'])) {
                return false;
            }
        }

        return true;
    }

    public function createAsset(Entity $entity, ?\stdClass $attachment = null): void
    {
        if (empty($entity->getAsset())) {
            $type = null;
            if (!empty($attachment) && !empty($attachment->modelAttributes->attributeAssetType)) {
                $type = $attachment->modelAttributes->attributeAssetType;
            }
            $this->getRepository()->createAsset($entity, false, $type);
        }
    }

    public function validateAttachment(Entity $entity, \stdClass $data): void
    {
        $parentType = property_exists($data, 'parentType') ? $data->parentType : '';
        $relatedType = property_exists($data, 'relatedType') ? $data->relatedType : '';
        $field = property_exists($data, 'field') ? $data->field : '';

        if (($parentType === 'Asset' || $relatedType === 'Asset') && in_array($field, ['file', 'files']) && !empty($asset = $entity->getAsset())) {
            throw (new SuchAssetAlreadyExists($this->getInjection('language')->translate('suchAssetAlreadyExists', 'exceptions', 'Asset')))->setAsset($asset);
        }

        $type = $this->getMetadata()->get(['entityDefs', $relatedType, 'fields', $field, 'assetType']);
        if (!empty($data->modelAttributes->type)) {
            $type = $data->modelAttributes->type;
        }
        if (!empty($data->modelAttributes->attributeAssetType)) {
            $type = $data->modelAttributes->attributeAssetType;
        }

        if (!empty($type)) {
            if (is_string($type)) {
                $type = [$type];
            }

            $attachment = clone $entity;
            if (property_exists($data, 'contents')) {
                $attachment->set('contents', $data->contents);
            }

            $this->getInjection(AssetValidator::class)->validateViaTypes($type, $attachment);
        }
    }

    /**
     * @param      $attachment
     * @param null $path
     *
     * @return array
     * @throws \ImagickException
     * @throws \ReflectionException
     */
    public function getImageInfo($attachment, $path = null): array
    {
        if (stripos($attachment->get("type"), "image/") === false) {
            return [];
        }

        $path = $path ?? $this->getPath($attachment);

        $image = new Imagick($path);

        if ($imageInfo = getimagesize($path)) {
            $result = [
                "width"       => $image->getImageWidth(),
                "height"      => $image->getImageHeight(),
                "color_space" => self::getColorSpace($image),
                "color_depth" => $image->getImageDepth(),
                'orientation' => $this->getPosition($image->getImageWidth(), $image->getImageHeight()),
                'mime'        => $image->getImageMimeType(),
            ];
        }

        return $result ?? [];
    }

    /**
     * @param      $attachment
     * @param null $path
     *
     * @return array
     */
    public function getFileInfo($attachment, $path = null): array
    {
        $path = $path ?? $this->getPath($attachment);

        if ($pathInfo = pathinfo($path)) {
            $result['extension'] = $pathInfo['extension'];
            $result['base_name'] = $pathInfo['basename'];
        }

        $result['size'] = filesize($path);

        return $result;
    }

    /**
     * @param \Dam\Entities\Attachment $attachment
     * @param string                   $newName
     *
     * @return mixed
     */
    public function changeName(\Dam\Entities\Attachment $attachment, string $newName)
    {
        return $this->getRepository()->renameFile($attachment, $newName);
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $this->addDependency('fileStorageManager');
        $this->addDependency(AssetValidator::class);
    }

    /**
     * @param \Espo\Entities\Attachment $attachment
     *
     * @return mixed
     */
    private function getPath(\Espo\Entities\Attachment $attachment)
    {
        return $this->getRepository()->getFilePath($attachment);
    }

    /**
     * @param $width
     * @param $height
     *
     * @return string
     */
    private function getPosition($width, $height): string
    {
        $result = "Square";

        if ($width > $height) {
            $result = "Landscape";
        } elseif ($width < $height) {
            $result = "Portrait";
        }

        return $result;
    }
}
