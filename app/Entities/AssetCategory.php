<?php

declare(strict_types=1);

namespace Dam\Entities;

use Espo\Core\Exceptions\Error;
use Espo\Core\Templates\Entities\Base;
use Espo\ORM\EntityCollection;

/**
 * Class AssetCategory
 *
 * @package Dam\Entities
 */
class AssetCategory extends Base
{
    /**
     * @var string
     */
    protected $entityType = "AssetCategory";

    /**
     * @return EntityCollection
     * @throws Error
     */
    public function getTreeAssets(): EntityCollection
    {
        // validation
        $this->isEntity();

        // prepare where
        $where = [
            'assetCategories.id' => [$this->get('id')],
        ];

        $categoryChildren = $this->getChildren();

        if (count($categoryChildren) > 0) {
            $where['assetCategories.id'] = array_merge($where['assetCategories.id'], array_column($categoryChildren->toArray(), 'id'));
        }

        return $this
            ->getEntityManager()
            ->getRepository('Asset')
            ->distinct()
            ->join('assetCategories')
            ->where($where)
            ->find();
    }

    /**
     * @return EntityCollection
     * @throws Error
     */
    public function getChildren(): EntityCollection
    {
        // validation
        $this->isEntity();

        return $this
            ->getEntityManager()
            ->getRepository('AssetCategory')
            ->where(['categoryRoute*' => "%|" . $this->get('id') . "|%"])
            ->find();
    }

    /**
     * @return bool
     * @throws Error
     */
    protected function isEntity(): bool
    {
        if (empty($id = $this->get('id'))) {
            throw new Error('AssetCategory is not exist');
        }

        return true;
    }
}
