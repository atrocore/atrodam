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

use Espo\Listeners\AbstractListener;
use Espo\Core\EventManager\Event;

class Metadata extends AbstractListener
{
    public function modify(Event $event): void
    {
        $data = $event->getArgument('data');

        $this->updateRelationshipPanels($data);

        if ($this->getConfig()->get('isInstalled', false)) {
            $typesData = $this->getAssetTypes();
            $data['fields']['asset']['types'] = array_column($typesData, 'name');
            $data['fields']['asset']['hasPreviewExtensions'][] = 'pdf';
            $data['entityDefs']['Asset']['fields']['type']['options'] = $data['fields']['asset']['types'];
            $data['entityDefs']['Asset']['fields']['type']['optionsIds'] = array_column($typesData, 'id');
            $data['entityDefs']['Asset']['fields']['type']['assignAutomatically'] = [];
            foreach ($typesData as $item) {
                if (!empty($item['assignAutomatically'])) {
                    $data['entityDefs']['Asset']['fields']['type']['assignAutomatically'][] = $item['name'];
                }
                if (!empty($item['typesToExclude'])) {
                    $data['entityDefs']['Asset']['fields']['type']['typesToExclude'][$item['name']] = $item['typesToExclude'];
                }
            }
        }

        $event->setArgument('data', $data);
    }

    protected function getAssetTypes(): array
    {
        $assetTypes = $this->getContainer()->get('dataManager')->getCacheData('assetTypes');
        if (empty($assetTypes)) {
            try {
                $assetTypes = $this
                    ->getEntityManager()
                    ->getRepository('AssetType')
                    ->select(['id', 'name', 'assignAutomatically', 'typesToExclude'])
                    ->order('sortOrder', 'ASC')
                    ->find()
                    ->toArray();
                $this->getContainer()->get('dataManager')->setCacheData('assetTypes', $assetTypes);
            } catch (\Throwable $e) {
                $assetTypes = [];
            }
        }

        return $assetTypes;
    }

    protected function updateRelationshipPanels(array &$data): void
    {
        foreach ($data['entityDefs'] as $scope => $defs) {
            if (empty($defs['links'])) {
                continue 1;
            }
            foreach ($defs['links'] as $link => $linkData) {
                if (!empty($linkData['entity']) && $linkData['entity'] == 'Asset') {
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['view'] = "dam:views/record/panels/assets";
                }
            }
        }
    }
}
