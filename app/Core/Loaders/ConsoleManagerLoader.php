<?php

declare(strict_types=1);

namespace Dam\Core\Loaders;

use Dam\Core\ConsoleManager;
use Treo\Core\Loaders\Base;

/**
 * Class ConsoleManagerLoader
 * @package Dam\Core\Loaders
 */
class ConsoleManagerLoader extends Base
{
    /**
     * @return ConsoleManager
     */
    public function load()
    {
        return new ConsoleManager($this->getContainer());
    }
}