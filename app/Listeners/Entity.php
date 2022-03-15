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

namespace Dam\Listeners;

use Treo\Core\EventManager\Event;

class Entity extends AbstractListener
{
    public function afterSave(Event $event): void
    {
        /** @var \Espo\ORM\Entity $entity */
        $entity = $event->getArgument('entity');

        if (empty($this->getMetadata()->get(['entityDefs', $entity->getEntityType(), 'fields', 'mainImage']))) {
            return;
        }

        if (empty($linkData = $this->getAssetRelationWithMainImage($entity->getEntityType()))) {
            return;
        }

        if (!$entity->isAttributeChanged('mainImageId')) {
            return;
        }

        $postData = new \stdClass();

        if (empty($entity->get('mainImageId'))) {
            $postData->isMainImage = false;
            $relatedAssets = $this->getService($entity->getEntityType())->findLinkedEntities($entity->get('id'), $linkData['linkName'], []);
            if (!empty($relatedAssets['total'])) {
                foreach ($relatedAssets['collection'] as $asset) {
                    if (!empty($asset->get('isMainImage'))) {
                        $mainImageAssetId = $asset->get('id');
                        break;
                    }
                }
            }
        } else {
            $asset = $this->getEntityManager()->getRepository('Asset')->where(['fileId' => $entity->get('mainImageId')])->findOne();
            if (!empty($asset)) {
                $this->getEntityManager()->getRepository($entity->getEntityType())->relate($entity, $linkData['linkName'], $asset);
                $postData->isMainImage = true;
                $mainImageAssetId = $asset->get('id');
            }
        }

        $postData->_relationEntity = $entity->getEntityType();
        $postData->_relationEntityId = $entity->get('id');
        $postData->_relationName = $linkData['linkName'];

        if (!empty($mainImageAssetId)) {
            $this->getService('Asset')->updateEntity($mainImageAssetId, $postData);
        }
    }
}
