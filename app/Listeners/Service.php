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

namespace Dam\Listeners;

use Espo\Core\Utils\Util;
use Espo\ORM\Entity;
use Treo\Listeners\AbstractListener;
use Treo\Core\EventManager\Event;

class Service extends AbstractListener
{
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

        $ids = array_column($collection->toArray(), 'id');

        foreach ($this->getMetadata()->get(['entityDefs', $entityType, 'links'], []) as $link => $linkData) {
            if (empty($linkData['type']) || $linkData['type'] !== 'hasMany' || empty($linkData['entity']) || $linkData['entity'] !== 'Asset' || empty($linkData['relationName'])) {
                continue 1;
            }

            $tableName = Util::toUnderScore($linkData['relationName']);
            $field = Util::toUnderScore(lcfirst($entityType));

            $query = "SELECT a.file_id as attachmentId, r.{$field}_id as entityId
                      FROM `$tableName` r 
                      LEFT JOIN `asset` a ON a.id=r.asset_id
                      WHERE r.is_main_image=1 
                        AND r.{$field}_id IN ('" . implode("','", $ids) . "')";

            $records = $this
                ->getEntityManager()
                ->getPDO()
                ->query($query)
                ->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($records)) {
                continue 1;
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
                        $entity->set('mainImageName', $record['attachmentId']);
                        break 1;
                    }
                }
            }
        }
    }

    public function prepareEntityForOutput(Event $event): void
    {
        /** @var Entity $entity */
        $entity = $event->getArgument('entity');

        if (empty($this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'fields', 'mainImage']))) {
            return;
        }

        if ($entity->has('mainImageId')) {
            return;
        }

        $entity->set('mainImageId', null);
        $entity->set('mainImageName', null);
        $entity->set('mainImagePathsData', null);

        foreach ($this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'links'], []) as $link => $linkData) {
            if (empty($linkData['type']) || $linkData['type'] !== 'hasMany' || empty($linkData['entity']) || $linkData['entity'] !== 'Asset' || empty($linkData['relationName'])) {
                continue 1;
            }

            $tableName = Util::toUnderScore($linkData['relationName']);
            $field = Util::toUnderScore(lcfirst($entity->getEntityType()));

            $query = "SELECT a.file_id 
                      FROM `$tableName` r 
                      LEFT JOIN `asset` a ON a.id=r.asset_id
                      WHERE r.is_main_image=1 
                        AND r.{$field}_id='{$entity->get('id')}'";

            $attachmentId = $this
                ->getEntityManager()
                ->getPDO()
                ->query($query)
                ->fetch(\PDO::FETCH_COLUMN);

            if (empty($attachmentId)) {
                continue 1;
            }

            $entity->set('mainImageId', $attachmentId);
            $entity->set('mainImageName', $attachmentId);
            $entity->set('mainImagePathsData', $this->getEntityManager()->getRepository('Attachment')->getAttachmentPathsData($attachmentId));
        }
    }
}
