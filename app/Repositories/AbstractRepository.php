<?php
declare(strict_types=1);

namespace Dam\Repositories;

use Espo\Core\Templates\Repositories\Base;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository extends Base
{
    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $this->addDependency('language');
    }

    /**
     * @param string $key
     * @param string $category
     * @param string $scope
     *
     * @return string
     */
    protected function translate(string $key, string $category, string $scope): string
    {
        return $this->getInjection('language')->translate($key, $category, $scope);
    }
}
