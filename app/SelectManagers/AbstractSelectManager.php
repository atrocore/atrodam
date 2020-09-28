<?php

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
