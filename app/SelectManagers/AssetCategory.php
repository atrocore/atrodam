<?php

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
