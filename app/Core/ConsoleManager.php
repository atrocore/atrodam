<?php

declare(strict_types=1);

namespace Dam\Core;

use Treo\Core\ConsoleManager as Base;
use Treo\Core\Container;

/**
 * Class ConsoleManager
 * @package Dam\Core
 */
class ConsoleManager extends Base
{
    /**
     * ConsoleManager constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }

    /**
     * Load routes
     *
     * @return array
     */
    protected function loadRoutes(): array
    {
        $routes = include CORE_PATH . '/Treo/Configs/Console.php';

        return array_merge($routes, [
            "config <module> rebuild" => \Dam\Console\Config::class,
        ]);
    }
}