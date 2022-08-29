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

namespace Dam\Repositories;

use Dam\Core\AssetValidator;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Templates\Repositories\Hierarchy;
use Espo\ORM\Entity;

class Asset extends Hierarchy
{
    public function getNextSorting(string $entityType, string $link, string $entityId): int
    {
        if (in_array($link, ['parents', 'children'])) {
            return 0;
        }

        $relationName = $this->getMetadata()->get(['entityDefs', $entityType, 'links', $link, 'relationName']);

        $table = $this->getEntityManager()->getQuery()->toDb($relationName);
        $column = $this->getEntityManager()->getQuery()->toDb("{$entityType}Id");
        $entityId = $this->getPDO()->quote($entityId);

        $query = "SELECT r.sorting 
                  FROM `$table` r 
                  LEFT JOIN asset a ON a.id=r.asset_id 
                  WHERE r.{$column}=$entityId 
                    AND r.deleted=0 
                    AND a.deleted=0 
                  ORDER BY r.sorting DESC 
                  LIMIT 0,1";

        $max = $this->getPDO()->query($query)->fetch(\PDO::FETCH_COLUMN);

        return empty($max) ? 0 : $max + 10;
    }

    public function updateRelationData(string $relationName, array $setData, string $re1, string $re1Id, string $re2, string $re2Id): void
    {
        $scope = ucfirst(str_replace('asset', '', strtolower($relationName)));
        $foreignRepository = $this->getEntityManager()->getRepository($scope);

        if (!empty($foreignRepository) && method_exists($foreignRepository, 'updateMainImageRelationData')) {
            $foreignRepository->updateMainImageRelationData($relationName, $setData, $re1, $re1Id, $re2, $re2Id);
            parent::updateRelationData($relationName, $setData, $re1, $re1Id, $re2, $re2Id);
            return;
        }

        if (!empty($setData['isMainImage'])) {
            $table = $this->getEntityManager()->getQuery()->toDb($relationName);
            $column = $this->getEntityManager()->getQuery()->toDb($re1);
            $entityId = $this->getPDO()->quote($re1Id);
            $this->getPDO()->exec("UPDATE `$table` SET is_main_image=0 WHERE deleted=0 AND $column=$entityId");
        }

        parent::updateRelationData($relationName, $setData, $re1, $re1Id, $re2, $re2Id);
    }

    public function updateSortOrder(string $entityId, array $assetsIds, string $scope, string $link): bool
    {
        $relationName = $this->getMetadata()->get(['entityDefs', $scope, 'links', $link, 'relationName']);
        if (empty($relationName)) {
            throw new BadRequest("No 'relationName' for relation.");
        }

        $table = $this->getEntityManager()->getQuery()->toDb($relationName);
        $column = $this->getEntityManager()->getQuery()->toDb("{$scope}Id");
        $entityId = $this->getPDO()->quote($entityId);

        foreach ($assetsIds as $k => $assetId) {
            $assetId = $this->getPDO()->quote($assetId);
            $sorting = $k * 10;
            $this->getPDO()->exec("UPDATE `$table` SET sorting=$sorting WHERE asset_id=$assetId AND $column=$entityId AND deleted=0");
        }

        return true;
    }

    public function getPossibleTypes(Entity $attachment): array
    {
        $types = [];
        foreach ($this->getMetadata()->get(['entityDefs', 'Asset', 'fields', 'type', 'assignAutomatically'], []) as $assetType) {
            try {
                $this->getInjection(AssetValidator::class)->validateViaTypes([$assetType], $attachment);
                $types[] = $assetType;
            } catch (\Throwable $e) {
                // ignore validation error
            }
        }

        return $types;
    }

    public function clearAssetMetadata(Entity $asset): void
    {
        $this->getEntityManager()->getRepository('AssetMetadata')->where(['assetId' => $asset->get('id')])->removeCollection();
    }

    public function updateMetadata(Entity $asset): void
    {
        $attachment = $this->getEntityManager()->getEntity('Attachment', $asset->get('fileId'));
        if (empty($attachment)) {
            throw new BadRequest($this->getInjection('language')->translate('noAttachmentExist', 'exceptions', 'Asset'));
        }

        $filePath = $this->getEntityManager()->getRepository('Attachment')->getFilePath($attachment);

        /**
         * @todo develop metadata readers
         */
        if (stripos($attachment->get('type'), "image") !== false) {
            $imagick = new \Imagick();
            $imagick->readImage($filePath);
            $metadata = $imagick->getImageProperties();
        }

        $this->clearAssetMetadata($asset);

        if (empty($metadata) || !is_array($metadata)) {
            return;
        }

        foreach ($metadata as $name => $value) {
            $item = $this->getEntityManager()->getEntity('AssetMetadata');
            $item->set('name', $name);
            $item->set('value', $value);
            $item->set('assetId', $asset->get('id'));
            $this->getEntityManager()->saveEntity($item);
        }
    }

    /**
     * @inheritDoc
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        $file = $this->getEntityManager()->getEntity('Attachment', $entity->get('fileId'));
        if (empty($file)) {
            throw new BadRequest($this->getInjection('language')->translate('noAttachmentExist', 'exceptions', 'Asset'));
        }

        if (empty($entity->get('type'))) {
            $possibleTypes = $this->getPossibleTypes($file);
            if (empty($possibleTypes)) {
                throw new BadRequest($this->getInjection('language')->translate('noAssetTypeProvided', 'exceptions', 'Asset'));
            }

            $entity->set('type', $possibleTypes);
        }

        // validate asset if type changed
        if (!$entity->isNew() && $entity->isAttributeChanged('type')) {
            $typesToExclude = [];
            foreach ($entity->get('type') as $type) {
                $this->getInjection(AssetValidator::class)->validateViaType((string)$type, $file);
                $typesToExclude = array_merge($typesToExclude, $this->getMetadata()->get(['entityDefs', 'Asset', 'fields', 'type', 'typesToExclude', $type], []));
            }

            if (!empty($typesToExclude)) {
                $filteredTypes = [];
                foreach ($entity->get('type') as $type) {
                    if (!in_array($type, $typesToExclude)) {
                        $filteredTypes[] = $type;
                    }
                }
                if (empty($filteredTypes)) {
                    throw new BadRequest($this->getInjection('language')->translate('noAssetTypeProvided', 'exceptions', 'Asset'));
                }
                $entity->set('type', $filteredTypes);
            }
        }

        // prepare name
        if (empty($entity->get('name'))) {
            $entity->set('name', $file->get('name'));
        } elseif ($entity->isAttributeChanged('name')) {
            $assetParts = explode('.', (string)$entity->get('name'));
            if (count($assetParts) > 1) {
                $assetExt = array_pop($assetParts);
            }

            $attachmentParts = explode('.', (string)$file->get('name'));
            $attachmentExt = array_pop($attachmentParts);

            if (!empty($assetExt) && $assetExt !== $attachmentExt) {
                throw new BadRequest($this->getInjection('language')->translate('fileExtensionCannotBeChanged', 'exceptions', 'Asset'));
            }

            $entity->set('name', implode('.', $assetParts) . '.' . $attachmentExt);
        }

        // update file info
        if ($entity->isAttributeChanged('fileId')) {
            $this->getInjection('serviceFactory')->create('Asset')->getFileInfo($entity);
        }

        // rename file
        if (!$entity->isNew() && $entity->isAttributeChanged("name")) {
            $this->getInjection('serviceFactory')->create('Attachment')->changeName($file, $entity->get('name'));
        }

        parent::beforeSave($entity, $options);
    }

    protected function afterSave(Entity $entity, array $options = [])
    {
        if ($entity->isAttributeChanged('private')) {
            $file = $this->getEntityManager()->getEntity('Attachment', $entity->get('fileId'));
            if (!empty($file)) {
                $file->set('private', $entity->get('private'));
                $this->getEntityManager()->saveEntity($file);
            }
        }

        // update metadata
        if ($entity->isAttributeChanged('fileId')) {
            $this->updateMetadata($entity);
        }

        parent::afterSave($entity, $options);
    }

    protected function afterRemove(Entity $entity, array $options = [])
    {
        if (!empty($attachmentId = $entity->get('fileId'))) {
            $attachment = $this->getEntityManager()->getEntity('Attachment', $attachmentId);
            if (!empty($attachment)) {
                $this->getEntityManager()->removeEntity($attachment);
            }
        }

        $this->clearAssetMetadata($entity);

        parent::afterRemove($entity, $options);
    }

    protected function init()
    {
        parent::init();

        $this->addDependency(AssetValidator::class);
        $this->addDependency('serviceFactory');
        $this->addDependency('language');
    }
}
