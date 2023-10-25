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

namespace Dam\Repositories;

use Dam\Core\AssetValidator;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Dam\Entities\Asset;
use Espo\ORM\Entity;
use Throwable;

/**
 * Class Attachment
 *
 * @package Dam\Repositories
 */
class Attachment extends \Espo\Repositories\Attachment
{
    /**
     * @param Entity $entity
     *
     * @return Asset|null
     */
    public function getAsset(Entity $entity): ?Asset
    {
        return $this->getEntityManager()->getRepository('Asset')->where(['fileId' => $entity->get('id')])->findOne();
    }

    /**
     * Create asset if it needs
     *
     * @param Entity      $attachment
     * @param bool        $skipValidation
     * @param string|null $type
     *
     * @throws Error
     * @throws Throwable
     */
    public function createAsset(Entity $attachment, bool $skipValidation = false, string $type = null)
    {
        if (!empty($this->where(['fileId' => $attachment->get('id')])->findOne())) {
            return;
        }

        if ($type === null) {
            $type = $this->getMetadata()->get(['entityDefs', $attachment->get('relatedType'), 'fields', $attachment->get('field'), 'assetType']);
        }

        $asset = $this->getEntityManager()->getEntity('Asset');
        $asset->set('name', $attachment->get('name'));
        $asset->set('private', $this->getConfig()->get('isUploadPrivate', true));
        $asset->set('fileId', $attachment->get('id'));
        if (!empty($type)) {
            $options = $this->getMetadata()->get(['entityDefs', 'Asset', 'fields', 'type', 'options'], []);
            $optionsIds = $this->getMetadata()->get(['entityDefs', 'Asset', 'fields', 'type', 'optionsIds'], []);

            $key = array_search($type, $options);
            if ($key !== false && isset($optionsIds[$key])) {
                $type = $optionsIds[$key];
            }

            $asset->set('type', [$type]);
            if (!$skipValidation) {
                try {
                    $this->getInjection(AssetValidator::class)->validate($asset);
                } catch (Throwable $exception) {
                    $this->getEntityManager()->removeEntity($attachment);
                    throw $exception;
                }
            }
        }

        $this->getEntityManager()->saveEntity($asset);
    }

    protected function init()
    {
        parent::init();

        $this->addDependency(AssetValidator::class);
    }

    /**
     * @param Entity $entity
     * @param string $path
     *
     * @return mixed
     * @throws Error
     */
    public function updateStorage(Entity $entity, string $path)
    {
        $entity->set("storageFilePath", $path);

        return $this->save($entity);
    }

    /**
     * @param Entity $attachment
     * @param string $newFileName
     *
     * @return bool
     * @throws Error
     */
    public function renameFile(Entity $attachment, string $newFile): bool
    {
        $path = $this->getFilePath($attachment);

        $pathParts = explode('/', $path);
        $fileName = array_pop($pathParts);

        if ($fileName == $newFile) {
            return true;
        }

        $newFileParts = explode('.', $newFile);
        array_pop($newFileParts);

        $attachment->setName(implode('.', $newFileParts));

        if ($this->getFileManager()->move($path, $this->getFilePath($attachment))) {
            return $this->save($attachment) ? true : false;
        }

        return false;
    }

    public function beforeSave(Entity $entity, array $options = [])
    {
        $pattern = '/^([^ ]+[^\"\/\x00\r\n\t\:\*\?"<>\|\cA-\cZ]+(\.[^\. ]+)?[^ ]+){1,254}$/';
        if ($entity->isAttributeChanged('name') && !preg_match($pattern, (string)$entity->get('name'))) {
            throw new BadRequest(sprintf($this->translate('suchFileNameNotValid', 'exceptions', 'Asset'), (string)$entity->get('name')));
        }

        parent::beforeSave($entity, $options);
    }

    /**
     * @param Entity $entity
     * @param array  $options
     */
    protected function afterRemove(Entity $entity, array $options = [])
    {
        // if uploaded new attachment with previous name
        $res = $this
            ->where(
                [
                    "relatedId"       => $entity->get("relatedId"),
                    "relatedType"     => $entity->get("relatedType"),
                    "storageFilePath" => $entity->get("storageFilePath"),
                    "name"            => $entity->get("name"),
                    "deleted"         => 0
                ]
            )
            ->count();

        if (!$res) {
            parent::afterRemove($entity, $options);
        }

        if ($this->isPdf($entity)) {
            $dirPath = $this->getConfig()->get('filesPath', 'upload/files/') . $entity->getStorageFilePath();

            $this->getFileManager()->unlink($dirPath . '/page-1.png');
        }
    }

    protected function isPdf(Entity $entity): bool
    {
        if (empty($entity->get('name'))) {
            return false;
        }

        $parts = explode('.', $entity->get('name'));

        return strtolower(array_pop($parts)) === 'pdf';
    }
}
