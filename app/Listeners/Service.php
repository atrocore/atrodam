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

namespace Dam\Listeners;

use Espo\Core\Utils\Util;
use Espo\ORM\Entity;
use Espo\Core\EventManager\Event;

class Service extends AbstractListener
{
    public function beforeUpdateEntity(Event $event): void
    {
        $id = $event->getArgument('id');
        $entityType = substr_replace($event->getArgument('target'), "", -7);
        $data = $event->getArgument('data');

        if (property_exists($data, 'mainImageAddOnly')) {
            if (property_exists($data, 'mainImageId') && !empty($data->mainImageAddOnly)) {
                $entity = $this->getService($entityType)->getEntity($id);
                if (!empty($entity->get('mainImageId'))) {
                    $data->mainImageId = $entity->get('mainImageId');
                }
            }
        }
    }

    public function afterLinkEntity(Event $event): void
    {
        /** @var Entity $entity */
        $entity = $event->getArgument('entity');

        /** @var Entity $foreignEntity */
        $foreignEntity = $event->getArgument('foreignEntity');

        if ($foreignEntity->getEntityType() !== 'Asset' || $entity->getEntityType() === 'AssetCategory') {
            return;
        }

        $data = new \stdClass();
        $data->_relationEntity = $entity->getEntityType();
        $data->_relationEntityId = $entity->get('id');
        $data->_relationName = $event->getArgument('link');

        try {
            $data->sorting = $this->getEntityManager()->getRepository('Asset')->getNextSorting($data->_relationEntity, $data->_relationName, $data->_relationEntityId);
            $this->getService('Asset')->updateEntity($foreignEntity->get('id'), $data);
        } catch (\Throwable $e) {
            $GLOBALS['log']->error('UPDATE SORTING FAILED: ' . $e->getMessage());
        }
    }

    public function loadPreviewForCollection(Event $event): void
    {
        $collection = $event->getArgument('collection');

        if (count($collection) === 0) {
            return;
        }

        $entityType = $collection[0]->getEntityType();

        if (empty($this->getMetadata()->get(['entityDefs', $entityType, 'fields', 'mainImage']))) {
            return;
        }

        if (empty($linkData = $this->getAssetRelationWithMainImage($entityType))) {
            return;
        }

        $ids = array_column($collection->toArray(), 'id');

        $tableName = Util::toUnderScore($linkData['relationName']);
        $field = Util::toUnderScore(lcfirst($entityType));

        $query = "SELECT a1.id as attachmentId, a1.name as attachmentName, r.{$field}_id as entityId
                  FROM `$tableName` r 
                  LEFT JOIN `asset` a ON a.id=r.asset_id
                  LEFT JOIN `attachment` a1 ON a1.id=a.file_id
                  WHERE r.is_main_image=1 
                    AND r.{$field}_id IN ('" . implode("','", $ids) . "')
                    AND r.deleted=0
                    AND a1.deleted=0";

        if (empty($records = $this->getEntityManager()->getPDO()->query($query)->fetchAll(\PDO::FETCH_ASSOC))) {
            return;
        }

        foreach ($collection as $entity) {
            if (!empty($entity->get('mainImageId'))) {
                continue 1;
            }

            $entity->set('mainImageId', null);
            $entity->set('mainImageName', null);

            foreach ($records as $record) {
                if ($entity->get('id') === $record['entityId']) {
                    $entity->set('mainImageId', $record['attachmentId']);
                    $entity->set('mainImageName', $record['attachmentName']);
                    break 1;
                }
            }
        }
    }

    public function prepareEntityForOutput(Event $event): void
    {
        $this->setMainImage($event->getArgument('entity'));
    }

    public function beforeCheckingIsEntityUpdated(Event $event): void
    {
        $this->setMainImage($event->getArgument('entity'));
    }

    protected function setMainImage(Entity $entity): void
    {
        if (empty($this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'fields', 'mainImage']))) {
            return;
        }

        if (empty($linkData = $this->getAssetRelationWithMainImage($entity->getEntityType()))) {
            return;
        }

        if ($entity->has('mainImageId')) {
            return;
        }

        $entity->set('mainImageId', null);
        $entity->set('mainImageName', null);
        $entity->set('mainImagePathsData', null);

        $tableName = Util::toUnderScore($linkData['relationName']);
        $field = Util::toUnderScore(lcfirst($entity->getEntityType()));

        $query = "SELECT a1.id, a1.name 
                      FROM `$tableName` r 
                      LEFT JOIN `asset` a ON a.id=r.asset_id
                      LEFT JOIN `attachment` a1 ON a1.id=a.file_id
                      WHERE r.is_main_image=1 
                        AND r.{$field}_id='{$entity->get('id')}'
                        AND r.deleted=0
                        AND a.deleted=0
                        AND a1.deleted=0";

        if (empty($record = $this->getEntityManager()->getPDO()->query($query)->fetch(\PDO::FETCH_ASSOC))) {
            return;
        }

        $entity->set('mainImageId', $record['id']);
        $entity->set('mainImageName', $record['name']);
        $entity->set('mainImagePathsData', $this->getEntityManager()->getRepository('Attachment')->getAttachmentPathsData($record['id']));
    }
}
