<?php
/*
 *  This file is part of AtroDAM.
 *
 *  AtroDAM - Open Source DAM application.
 *  Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 *  Website: https://atrodam.com
 *
 *  AtroDAM is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  AtroDAM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with AtroDAM. If not, see http://www.gnu.org/licenses/.
 *
 *  The interactive user interfaces in modified source and object code versions
 *  of this program must display Appropriate Legal Notices, as required under
 *  Section 5 of the GNU General Public License version 3.
 *
 *  In accordance with Section 7(b) of the GNU General Public License version 3,
 *  these Appropriate Legal Notices must retain the display of the "AtroDAM" word.
 *
 *  This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\Services;

use Dam\Core\AssetValidator;
use Dam\Core\Exceptions\SuchAssetAlreadyExists;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\FilePathBuilder;
use Espo\Core\FileStorage\Manager;
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
        $ch = curl_init(str_replace(" ", "%20", $url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (!empty($responseCode) && !in_array($responseCode, [200, 201]) || !file_exists($attachment->fileName)) {
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

        // validate
        $this->validateAttachment($entity, $attachment);

        // create asset
        $this->createAsset($entity, $attachment);

        return $entity;
    }

    /**
     * @param Entity    $entity
     * @param \stdClass $data
     *
     * @throws BadRequest
     */
    protected function validateAttachment(Entity $entity, \stdClass $data): void
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
     * @param \Dam\Entities\Attachment $attachment
     *
     * @return array|mixed
     * @throws Error
     * @throws \ImagickException
     */
    public function getFileMetaData(\Dam\Entities\Attachment $attachment)
    {
        $mime = $attachment->get('type');
        $meta = [];

        switch (true) {
            case (stripos($mime, "image") !== false):
                $meta = $this->getImageMeta($attachment);
                break;
        }

        return $meta;
    }

    /**
     * @param \Dam\Entities\Attachment $attachment
     *
     * @return array
     * @throws Error
     * @throws \ImagickException
     */
    public function getImageMeta(\Dam\Entities\Attachment $attachment)
    {
        $path = $this->getFileStorageManager()->getLocalFilePath($attachment);

        $imagick = new \Imagick();
        $imagick->readImage($path);

        return $imagick->getImageProperties();
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
        if ($attachment->get('sourceId')) {
            $attachment = $this->getRepository()->where(['id' => $attachment->get('sourceId')])->findOne();
        }

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

    /**
     * @return Manager
     */
    protected function getFileStorageManager(): Manager
    {
        return $this->getInjection("fileStorageManager");
    }

    /**
     * @param Entity    $entity
     * @param \stdClass $attachment
     */
    protected function createAsset(Entity $entity, \stdClass $attachment): void
    {
        if ($this->getMetadata()->get(['entityDefs', $attachment->relatedType, 'fields', $attachment->field, 'noAsset'], false)) {
            return;
        }

        if (empty($entity->getAsset())) {
            $type = null;
            if (!empty($attachment->modelAttributes->attributeAssetType)) {
                $type = $attachment->modelAttributes->attributeAssetType;
            }
            $this->getRepository()->createAsset($entity, false, $type);
        }
    }
}
