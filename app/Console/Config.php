<?php
/*
 *  This file is part of AtroDAM.
 *
 *  AtroDAM - Open Source DAM application.
 *  Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 *  Website: https://atrodam.com
 *
 *  AtroDAM is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  AtroDAM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with AtroDAM. If not, see http://www.gnu.org/licenses/.
 *
 *  The interactive user interfaces in modified source and object code versions
 *  of this program must display Appropriate Legal Notices, as required under
 *  Section 5 of the GNU General Public License version 3.
 *
 *  In accordance with Section 7(b) of the GNU General Public License version 3,
 *  these Appropriate Legal Notices must retain the display of the "AtroDAM" word.
 */

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