<?php

declare(strict_types=1);

namespace Dam\Core\Loaders;

use Dam\Core\FileManager;
use Treo\Core\Loaders\Base;

/**
 * Class FileManagerLoader
 * @package Dam\Core\Loaders
 */
class FileManagerLoader extends Base
{

    /**
     * @return FileManager
     */
    public function load()
    {
        return new FileManager($this->getContainer()->get('config'));
    }
}