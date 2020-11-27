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
 */

declare(strict_types=1);

namespace Dam\Repositories;

use Dam\Core\ConfigManager;
use Dam\Core\FileManager;
use Dam\Core\FileStorage\DAMUploadDir;
use Dam\Core\PathInfo;
use Espo\Core\Exceptions\Error;
use Espo\ORM\Entity;

/**
 * Class Attachment
 *
 * @package Dam\Repositories
 */
class Attachment extends \Treo\Repositories\Attachment
{
    /**
     * @inheritDoc
     */
    public function beforeSave(Entity $entity, array $options = [])
    {
        if ($entity->isNew()) {
            if ($entity->get("contents") && $entity->get('relatedType') === "Asset") {
                $entity->set('hash_md5', md5($entity->get("contents")));
            }
            $entity->set('hash_md5', md5($entity->get("contents")));
        }

        parent::beforeSave($entity, $options);
    }

    /**
     * @inheritDoc
     */
    public function afterSave(Entity $entity, array $options = [])
    {
        // get field type
        $fieldType = $this->getMetadata()->get(['entityDefs', $entity->get('relatedType'), 'fields', $entity->get('field'), 'type']);

        if ($entity->isNew() && $fieldType === 'asset') {
            $this->createAsset($entity);
        }

        parent::afterSave($entity, $options);
    }

    /**
     * Create asset if it needs
     *
     * @param Entity $entity
     *
     * @throws Error
     * @throws \Throwable
     */
    public function createAsset(Entity $entity)
    {
        // prepare name
        $name = explode('.', $entity->get('name'))[0];

        // prepare code
        $code = preg_replace("/[^a-z0-9_?!]/", "", strtolower($name));
        $suffix = '';
        $number = 1;
        while (!empty($this->getEntityManager()->getRepository('Asset')->where(['code' => $code . $suffix])->findOne())) {
            $suffix = '_' . $number;
            $number++;
        }

        $asset = $this->getEntityManager()->getEntity('Asset');
        $asset->set('name', $name);
        $asset->set('nameOfFile', $name);
        $asset->set('private', true);
        $asset->set('fileId', $entity->get('id'));
        $asset->set('type', $this->getMetadata()->get(['entityDefs', $entity->get('relatedType'), 'fields', $entity->get('field'), 'assetType']));
        $asset->set('code', $code . $suffix);

        // get config by type
        $config = $this
            ->getInjection("ConfigManager")
            ->getByType([ConfigManager::getType($asset->get('type'))]);

        try {
            foreach ($config['validations'] as $type => $value) {
                $this->getInjection('Validator')->validate($type, $entity, ($value['private'] ?? $value));
            }
            $this->getEntityManager()->saveEntity($asset);
        } catch (\Throwable $exception) {
            $this->getFileManager()->removeFile([$entity->get('tmpPath')]);
            $this->getEntityManager()->removeEntity($entity);

            throw $exception;
        }
    }

    /**
     * Init
     */
    protected function init()
    {
        parent::init();

        $this->addDependency("DAMFileManager");
        $this->addDependency("Validator");
        $this->addDependency("ConfigManager");
    }

    /**
     * @param Entity              $entity
     * @param \Dam\Entities\Asset $asset
     *
     * @return bool
     * @throws Error
     */
    public function moveFile(Entity $entity, \Dam\Entities\Asset $asset): bool
    {
        $file = $this->getFileStorageManager()->getLocalFilePath($entity);
        $fileManager = $this->getFileManager();

        $path = $asset->get('private') ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH;
        $storePath = $asset->get('path');

        $path = $path . $storePath . "/" . $entity->get('name');

        if ($fileManager->move($file, $path)) {
            $entity->set('storageFilePath', $storePath);
            return $this->save($entity) ? true : false;
        }

        return false;
    }

    /**
     * @param Entity $entity
     * @param null   $role
     *
     * @return |null
     * @throws \Espo\Core\Exceptions\Error
     */
    public function getCopiedAttachment(Entity $entity, $role = null)
    {
        $attachment = $this->get();

        $attachment->set(
            [
                'sourceId'        => $entity->getSourceId(),
                'name'            => $entity->get('name'),
                'type'            => $entity->get('type'),
                'size'            => $entity->get('size'),
                'role'            => $entity->get('role'),
                'storageFilePath' => $entity->get('storageFilePath'),
                'relatedType'     => $entity->get('relatedType'),
                'relatedId'       => $entity->get('relatedId'),
                'hash_md5'        => $entity->get('hash_md5')
            ]
        );

        if ($role) {
            $attachment->set('role', $role);
        }

        $this->save($attachment);

        return $attachment;

    }

    /**
     * @param \Espo\ORM\Entity $entity
     *
     * @return bool
     */
    public function removeThumbs(\Espo\ORM\Entity $entity)
    {
        foreach (DAMUploadDir::thumbsFolderList() as $path) {
            $dirPath = $path . $entity->get('storageFilePath');
            if (!is_dir($dirPath)) {
                continue;
            }

            return $this->getFileManager()->removeInDir($dirPath);
        }
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
        $entity->set("tmpPath", null);

        return $this->save($entity);
    }

    /**
     * @param Entity        $attachment
     * @param string        $newFileName
     * @param PathInfo|null $entity
     *
     * @return bool
     * @throws Error
     */
    public function renameFile(Entity $attachment, string $newFileName, PathInfo $entity = null): bool
    {
        $path = $this->buildPath($entity, $attachment);
        $pathInfo = pathinfo($path);
        if ($pathInfo['basename'] == $newFileName) {
            return true;
        }

        $attachment->setName($newFileName);

        if ($this->getFileManager()->renameFile($path, (string)$attachment->get("name"))) {
            return $this->save($attachment) ? true : false;
        }

        return false;
    }

    /**
     * @param Entity $entity
     * @param array  $options
     */
    protected function afterRemove(\Espo\ORM\Entity $entity, array $options = [])
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
            $this->removeThumbs($entity);
            parent::afterRemove($entity, $options);
        }
    }

    /**
     * @return FileManager
     */
    protected function getFileManager(): FileManager
    {
        return $this->getInjection("DAMFileManager");
    }

    /**
     * @param PathInfo $entity
     * @param Entity   $attachment
     *
     * @return string
     */
    private function buildPath(PathInfo $entity, Entity $attachment): string
    {
        $path = $entity->getPathInfo()[0];
        return $path . $attachment->get('storageFilePath') . "/" . $attachment->get('name');
    }
}
