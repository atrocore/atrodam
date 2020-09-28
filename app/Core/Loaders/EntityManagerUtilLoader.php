<?php

declare(strict_types=1);

namespace Dam\Core\Loaders;

use Dam\Core\Utils\EntityManager;
use Treo\Core\Loaders\Base;

/**
 * Class EntityManagerUtilLoader
 * @package Dam\Core\Loaders
 */
class EntityManagerUtilLoader extends Base
{
    /**
     * @return EntityManager
     */
    public function load()
    {
        return new EntityManager($this->getContainer());
    }
}