<?php

namespace Dam\Console;

use Dam\Services\DamConfig;
use Treo\Console\AbstractConsole;

/**
 * Class Config
 * @package Dam\Console
 */
class Config extends AbstractConsole
{
    /**
     * Run action
     *
     * @param array $data
     */
    public function run(array $data): void
    {
        if (!file_exists("data/" . $data['module'] . "/config.yaml")) {
            self::show("Config not found in data/dam", self::ERROR, true);
        }

        $res = $this->getDamConfigService()->convertYamlToArray(yaml_parse_file("data/dam/config.yaml"));

        if ($res === false) {
            self::show("Error on save config", self::ERROR, true);
        } else {
            $this->getConfig()->updateCacheTimestamp();
            $this->getConfig()->save();
            self::show("Config update successful", self::SUCCESS, true);
        }
    }

    /**
     * Get console command description
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return "Method for build yaml configs to php arrays";
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getService(string $name)
    {
        return $this->container->get("ServiceFactory")->create($name);
    }

    /**
     * @return DamConfig
     */
    protected function getDamConfigService(): DamConfig
    {
        return $this->getService("DamConfig");
    }

}