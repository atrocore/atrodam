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
use Dam\Core\PathInfo;
use Espo\Core\Exceptions\Error;
use Dam\Entities\Asset;
use Espo\ORM\Entity;

/**
 * Class Attachment
 *
 * @package Dam\Repositories
 */
class Attachment extends \Espo\Repositories\Attachment
{
    /**
     * @inheritDoc
     */
    public function isPrivate(Entity $entity): bool
    {
        if (!empty($asset = $entity->getAsset())) {
            return $asset->get('private');
        }

        return parent::isPrivate($entity);
    }

    /**
     * @param Entity $entity
     *
     * @return Asset|null
     * @throws Error
     */
    public function getAsset(Entity $entity): ?Asset
    {
        return $this->getEntityManager()->getRepository('Asset')->where(['fileId' => $entity->get('id')])->findOne();
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

        $asset = $this->getEntityManager()->getEntity('Asset');
        $asset->set('name', $name);
        $asset->set('private', $this->getConfig()->get('isUploadPrivate', true));
        $asset->set('fileId', $entity->get('id'));
        $asset->set('type', $this->getMetadata()->get(['entityDefs', $entity->get('relatedType'), 'fields', $entity->get('field'), 'assetType']));

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
            parent::afterRemove($entity, $options);
        }
    }

    /**
     * @param PathInfo $entity
     * @param Entity   $attachment
     *
     * @return string
     */
    private function buildPath(PathInfo $entity, Entity $attachment): string
    {
        return $entity->getPathInfo()[0] . $attachment->get('storageFilePath') . "/" . $attachment->get('name');
    }
}
