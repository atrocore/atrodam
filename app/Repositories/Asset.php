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
 * This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\Repositories;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Utils\Util;
use Espo\ORM\Entity;

/**
 * Class Asset
 *
 * @package Dam\Repositories
 */
class Asset extends AbstractRepository
{
    public function getNextSorting(string $entityType, string $link, string $entityId): int
    {
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
        /**
         * For main image
         */
        if (method_exists($this->getEntityManager()->getRepository($re1), 'updateMainImageRelationData')) {
            $this->getEntityManager()->getRepository($re1)->updateMainImageRelationData($relationName, $setData, $re1, $re1Id, $re2, $re2Id);
        } else {
            if (!empty($setData['isMainImage'])) {
                $query = "UPDATE `" . Util::toUnderScore($relationName) . "` SET is_main_image=0 WHERE deleted=0";
                $query .= " AND " . Util::toUnderScore(lcfirst($re1)) . "=" . $this->getPDO()->quote($re1Id);
                $this->getPDO()->exec($query);
            }
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

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     *
     * @return bool
     */
    public function linkAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getMapper()->relate($foreign, "relatedAssets", $main) && $this->getMapper()->relate($main, "relatedAssets", $foreign);
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     *
     * @return mixed
     */
    public function unlinkAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getMapper()->unrelate($foreign, "relatedAssets", $main);
    }

    /**
     * @inheritDoc
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        if (empty($file = $entity->get('file'))) {
            throw new BadRequest($this->translate('noAttachmentExist', 'exceptions', 'Asset'));
        }

        // set defaults
        if (empty($entity->get('libraryId'))) {
            $entity->set('libraryId', '1');
        }
        if (empty($entity->get('type'))) {
            $entity->set('type', 'File');
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
                throw new BadRequest($this->translate('fileExtensionCannotBeChanged', 'exceptions', 'Asset'));
            }

            $entity->set('name', implode('.', $assetParts) . '.' . $attachmentExt);
        }

        parent::beforeSave($entity, $options);
    }

    protected function afterSave(Entity $entity, array $options = [])
    {
        if ($entity->isAttributeChanged('private')) {
            $file = $entity->get('file');
            if (!empty($file)) {
                $file->set('private', $entity->get('private'));
                $this->getEntityManager()->saveEntity($file);
            }
        }

        parent::afterSave($entity, $options);
    }
}
