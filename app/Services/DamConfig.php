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

declare(strict_types=1);

namespace Dam\Services;

use Espo\Core\Templates\Services\Base;

/**
 * Class Asset
 *
 * @package Dam\Services
 */
class DamConfig extends Base
{
    /**
     * Asset constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     */
    public function getYamlConfig()
    {
        if (!file_exists("data/dam/config.yaml")) {
            return "";
        }

        return file_get_contents("data/dam/config.yaml");
    }

    /**
     * @param string $yaml
     * @return bool
     */
    public function validateYaml(string $yaml): bool
    {
        $res = yaml_parse($yaml);

        return $res === false ? false : true;
    }

    /**
     * @param string $yaml
     * @return bool
     */
    public function saveYaml(string $yaml): bool
    {
        $res = file_put_contents("data/dam/config.yaml", $yaml);

        return $res === false ? false : true;
    }

    /**
     * @param string $yaml
     * @return bool
     */
    public function convertYamlToArray(array $yaml): bool
    {
        $config = $this->getFileManager()->varExport($yaml);

        $res = file_put_contents(
            "data/dam/config.php",
            "<?php " . PHP_EOL . "return " . $config . ";" . PHP_EOL
        );

        return $res === false ? false : true;
    }
}
