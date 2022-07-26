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
 *
 *  This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\SelectManagers;

use \Treo\Core\SelectManagers\Base;

/**
 * Class AbstractSelectManager
 *
 * @package Dam\SelectManagers
 */
abstract class AbstractSelectManager extends Base
{

    /**
     * @var array
     */
    protected $selectData = [];
    /**
     * @var array
     */
    protected $boolData = [];

    /**
     * Get select params
     *
     * @param array $params
     * @param bool  $withAcl
     * @param bool  $checkWherePermission
     *
     * @return array
     */
    public function getSelectParams(array $params, $withAcl = false, $checkWherePermission = false)
    {
        // set select data
        $this->selectData = $params;

        return parent::getSelectParams($params, $withAcl, $checkWherePermission);
    }

    /**
     * Get select data
     *
     * @param string $key
     *
     * @return array
     */
    protected function getSelectData($key = '')
    {
        $result = [];

        if (empty($key)) {
            $result = $this->selectData;
        } elseif (isset($this->selectData[$key])) {
            $result = $this->selectData[$key];
        }

        return $result;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    protected function getBoolData(string $name)
    {
        if (!isset($this->boolData[$name])) {
            $this->boolData = $this->setBoolData();
        }

        return $this->boolData[$name] ?? null;
    }

    /**
     * @return array
     */
    protected function setBoolData()
    {
        $res = [];

        foreach ($this->getSelectData('where') as $row) {
            if ($row['type'] != 'bool') {
                continue;
            }
            foreach (($row['data'] ?? []) as $key => $data) {
                $res[$key] = $data;
            }
        }

        return $res;
    }
}
