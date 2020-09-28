<?php

declare(strict_types=1);

namespace Dam\Core\Loaders;

use Dam\Core\ConfigManager;
use Treo\Core\Loaders\Base;

/**
 * Class ConfigManagerLoader
 * @package Dam\Core\Loaders
 */
class ConfigManagerLoader extends Base
{
    /**
     * @return ConfigManager
     */
    public function load()
    {
        return new ConfigManager($this->getContainer());
    }
}