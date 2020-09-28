<?php

declare(strict_types=1);

namespace Dam\Repositories;

use Espo\Core\Utils\Util;

/**
 * Class AssetMetaData
 * @package Dam\Repositories
 */
class AssetMetaData extends \Espo\Core\Templates\Repositories\Base
{
    const TABLE_NAME = "asset_meta_data";

    /**
     * @param string $field
     * @param string $id
     * @return mixed
     */
    public function clearData(string $field, string $id)
    {
        return $this->getPDO()->exec("DELETE FROM " . static::TABLE_NAME . " WHERE {$field} = '{$id}'");
    }

    /**
     * @param $field
     * @param $id
     * @param $metaData
     * @return bool
     */
    public function insertMeta($field, $id, $metaData)
    {
        $res  = true;
        $stmt = $this->getPDO()->prepare("INSERT INTO " . static::TABLE_NAME . " (id, name, value, {$field}) VALUES (?,?,?,?)");

        foreach ($metaData as $k => $v) {
            $res &= $stmt->execute([Util::generateId(), $k, $v, $id]);
        }

        return $res;
    }
}
