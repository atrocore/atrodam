<?php

declare(strict_types=1);

namespace Dam\Core\Loaders;

use Dam\Core\FilePathBuilder;
use Treo\Core\Loaders\Base;

/**
 * Class FilePathBuilderLoader
 * @package Dam\Core\Loaders
 */
class FilePathBuilderLoader extends Base
{
    /**
     * @return FilePathBuilder
     */
    public function load()
    {
        return new FilePathBuilder($this->getContainer());
    }
}