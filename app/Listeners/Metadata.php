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

use Espo\Core\Utils\Json;
use Espo\Core\Utils\Util;
use Treo\Listeners\AbstractListener;
use Treo\Core\EventManager\Event;

/**
 * Class Metadata
 */
class Metadata extends AbstractListener
{
    /**
     * @param Event $event
     */
    public function modify(Event $event)
    {
        $data = $event->getArgument('data');

        $this->updateRelationMetadata($data);

        if ($this->getConfig()->get('isInstalled', false)) {
            $typesData = $this->getAssetTypes();
            $data['fields']['asset']['types'] = array_column($typesData, 'name');
            $data['fields']['asset']['hasPreviewExtensions'][] = 'pdf';
            $data['entityDefs']['Asset']['fields']['type']['options'] = $data['fields']['asset']['types'];
            $data['entityDefs']['Asset']['fields']['type']['optionsIds'] = array_column($typesData, 'id');
            $data['entityDefs']['Asset']['fields']['type']['default'] = 'File';
            foreach ($typesData as $k => $item) {
                if (!empty($item['is_default'])) {
                    $data['entityDefs']['Asset']['fields']['type']['default'] = $item['name'];
                }
            }
        }

        $event->setArgument('data', $data);
    }

    protected function getAssetTypes(): array
    {
        /** @var \PDO $pdo */
        $pdo = $this->getContainer()->get('pdo');

        try {
            $types = $pdo
                ->query("SELECT id, name, is_default FROM asset_type WHERE deleted=0 ORDER BY sort_order ASC")
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $types = [];
        }

        if (!in_array('File', array_column($types, 'name'))) {
            $types[] = ['id' => 'file', 'name' => 'File', 'is_default' => false];
            $pdo->exec("DELETE FROM asset_type WHERE id='file';INSERT INTO asset_type (id, name, sort_order) VALUE ('file', 'File', 999)");
        }

        return $types;
    }

    /**
     * @param array $data
     */
    protected function updateRelationMetadata(array &$data)
    {
        foreach ($data['entityDefs'] as $scope => $defs) {
            if (empty($defs['links']) || !empty($data['scopes'][$scope]['skipAssetSorting'])) {
                continue 1;
            }
            foreach ($defs['links'] as $link => $linkData) {
                if (!empty($linkData['entity']) && $linkData['entity'] == 'Asset') {
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['entityName'] = $scope;
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['label'] = 'Assets';

                    if (empty($data['clientDefs'][$scope]['relationshipPanels'][$link]['view'])) {
                        $data['clientDefs'][$scope]['relationshipPanels'][$link]['view'] = "dam:views/asset/record/panels/bottom-panel";
                    }

                    $data['entityDefs'][$scope]['links'][$link]['additionalColumns']['sorting'] = [
                        'type'    => 'int',
                        'default' => 100000
                    ];
                    $data['entityDefs'][$scope]['fields'][$link]['columns']['sorting'] = 'assetSorting';
                    $data['entityDefs'][$scope]['fields']['assetSorting'] = [
                        'type'                      => 'int',
                        'notStorable'               => true,
                        'layoutListDisabled'        => true,
                        'layoutListSmallDisabled'   => true,
                        'layoutDetailDisabled'      => true,
                        'layoutDetailSmallDisabled' => true,
                        'layoutMassUpdateDisabled'  => true,
                        'layoutFiltersDisabled'     => true,
                        'importDisabled'            => true,
                        'exportDisabled'            => true,
                    ];
                }
            }
        }
    }
}
