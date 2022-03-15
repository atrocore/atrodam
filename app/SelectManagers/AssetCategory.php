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

namespace Dam\SelectManagers;

/**
 * Class AssetCategory
 *
 * @package Dam\SelectManagers
 */
class AssetCategory extends AbstractSelectManager
{
    /**
     * NotEntity filter
     *
     * @param array $result
     */
    protected function boolFilterNotEntity(&$result)
    {
        if ($value = $this->getBoolData('notEntity')) {
            $value = (array)$value;

            foreach ($value as $id) {
                $result['whereClause'][] = [
                    'id!=' => (string)$id,
                ];
            }
        }
    }

    /**
     * @param $result
     */
    protected function boolFilterNotAttachment(&$result)
    {
        $result['whereClause'][] = [
            "id!=s" => [
                "selectParams" => [
                    "select" => ['asset_category_asset.asset_category_id'],
                    "customJoin" => "JOIN asset_category_asset ON asset_category_asset.asset_category_id = asset_category.id",
                    "whereClause" => [
                        'asset_category_asset.deleted' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $result
     */
    protected function boolFilterNotSelectCategories(&$result)
    {
        if ($value = $this->getBoolData('notSelectCategories')) {
            $result['whereClause'][] = [
                "id!=s" => [
                    "selectParams" => [
                        "select" => ['asset_category_asset.asset_category_id'],
                        "customJoin" => "JOIN asset_category_asset ON asset_category_asset.asset_category_id = asset_category.id",
                        "whereClause" => [
                            'asset_category_asset.asset_id' => (string)$value,
                            'asset_category_asset.deleted' => 0,
                        ],
                    ],
                ],
            ];
        }
    }

    /**
     * @param $result
     */
    protected function boolFilterNotChildCategory(&$result)
    {
        if ($value = $this->getBoolData('notChildCategory')) {
            $result['whereClause'][] = [
                'categoryRoute!*' => "%|$value|%",
            ];
        }
    }

    /**
     * @param $result
     */
    protected function boolFilterOnlyChildCategory(&$result)
    {
        $result['whereClause'][] = [
            'hasChild' => 0,
        ];
    }

    /**
     * @param $result
     */
    protected function boolFilterOnlyRoot(&$result)
    {
        $result['whereClause'][] = [
            'categoryParentId' => null
        ];
    }

    /**
     * @param $result
     */
    protected function boolFilterByCollection(&$result)
    {
        if ($value = $this->getBoolData('byCollection')) {
            // get catalog
            $collection = $this
                ->getEntityManager()
                ->getEntity('Collection', (string)$value);

            if (!empty($collection) && !empty($categories = $collection->get('assetCategories')->toArray())) {
                // prepare where
                $where[] = ['id' => array_column($categories, 'id')];
                foreach ($categories as $category) {
                    $where[] = ['categoryRoute*' => "%|" . $category['id'] . "|%"];
                }

                $result['whereClause'][] = ['OR' => $where];
            } else {
                $result['whereClause'][] = ['id' => -1];
            }
        }
    }

}
