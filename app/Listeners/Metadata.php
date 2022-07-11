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

use Dam\Repositories\AssetType;
use Espo\Listeners\AbstractListener;
use Espo\Core\EventManager\Event;

class Metadata extends AbstractListener
{
    public function modify(Event $event): void
    {
        $data = $event->getArgument('data');

        $this->updateRelationMetadata($data);

        if ($this->getConfig()->get('isInstalled', false)) {
            $typesData = $this->getAssetTypes();
            $data['fields']['asset']['types'] = array_column($typesData, 'name');
            $data['fields']['asset']['hasPreviewExtensions'][] = 'pdf';
            $data['entityDefs']['Asset']['fields']['type']['options'] = $data['fields']['asset']['types'];
            $data['entityDefs']['Asset']['fields']['type']['optionsIds'] = array_column($typesData, 'id');
            $data['entityDefs']['Asset']['fields']['type']['assignAutomatically'] = [];
            foreach ($typesData as $item) {
                if (!empty($item['isDefault'])) {
                    $data['entityDefs']['Asset']['fields']['type']['default'] = $item['name'];
                }
                if (!empty($item['assignAutomatically'])) {
                    $data['entityDefs']['Asset']['fields']['type']['assignAutomatically'][] = $item['name'];
                }
            }
        }

        $event->setArgument('data', $data);
    }

    protected function getAssetTypes(): array
    {
        /** @var AssetType $repository */
        $repository = $this->getEntityManager()->getRepository('AssetType');

        $types = $repository
            ->select(['id', 'name', 'isDefault', 'assignAutomatically'])
            ->order('sort_order', 'ASC')
            ->find()
            ->toArray();

        if (!in_array('File', array_column($types, 'name'))) {
            $fileTypeData = ['name' => 'File', 'isDefault' => false, 'assignAutomatically' => true];
            $fileType = $repository->get();
            $fileType->set($fileTypeData);
            $repository->save($fileType);
            $types[] = $fileTypeData;
        }

        return $types;
    }

    protected function updateRelationMetadata(array &$data): void
    {
        $scopes = [];

        foreach ($data['entityDefs'] as $scope => $defs) {
            if (empty($defs['links']) || !empty($data['scopes'][$scope]['skipAssetSorting'])) {
                continue 1;
            }
            foreach ($defs['links'] as $link => $linkData) {
                if (!empty($linkData['entity']) && $linkData['entity'] == 'Asset') {
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['entityName'] = $scope;
                    $data['clientDefs'][$scope]['relationshipPanels'][$link]['view'] = "dam:views/record/panels/assets";

                    if (!empty($linkData['relationName'])) {
                        $data['clientDefs'][$scope]['relationshipPanels'][$link]['dragDrop'] = [
                            'isActive'  => true,
                            'sortField' => 'sorting'
                        ];

                        $data['clientDefs'][$scope]['relationshipPanels'][$link]['sortBy'] = 'sorting';
                        $data['clientDefs'][$scope]['relationshipPanels'][$link]['asc'] = true;

                        $data['clientDefs'][$scope]['relationshipPanels'][$link]['rowActionsView'] = "dam:views/asset/record/row-actions/relationship";

                        foreach (['edit', 'detail', 'detailSmall'] as $mode) {
                            $data['clientDefs'][$scope]['sidePanels'][$mode][] = [
                                'name'    => 'mainImage',
                                'unshift' => true,
                                'label'   => 'mainImage',
                                'view'    => 'dam:views/asset/fields/main-image'
                            ];
                        }

                        $data['entityDefs'][$scope]['links'][$link]['additionalColumns']['sorting'] = [
                            'type' => 'int'
                        ];

                        $data['entityDefs'][$scope]['links'][$link]['additionalColumns']['isMainImage'] = [
                            'type' => 'bool'
                        ];

                        $data['entityDefs'][$scope]['fields']['mainImage'] = [
                            'type'        => 'image',
                            'notStorable' => true,
                            'previewSize' => 'medium',
                            'readOnly'    => true
                        ];

                        $data['entityDefs'][$scope]['links']['mainImage'] = [
                            'type'        => 'belongsTo',
                            'entity'      => 'Attachment',
                            'skipOrmDefs' => true
                        ];
                    }

                    $scopes[] = $scope;
                }
            }
        }

        $data['entityDefs']['Asset']['fields']['sorting']['relatingEntityField'] = $scopes;
        $data['entityDefs']['Asset']['fields']['isMainImage']['relatingEntityField'] = $scopes;

        $data['app']['nonInheritedFields'][] = 'mainImage';
    }
}
