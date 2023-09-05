<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

declare(strict_types=1);

namespace Dam\Migrations;

use Treo\Core\Migration\Base;

class V1Dot5Dot9 extends Base
{
    public function up(): void
    {
        $this->getPDO()->exec("DELETE FROM asset_category WHERE deleted=1");
        while (!empty($id = $this->getDuplicate('asset_category'))) {
            $this->getPDO()->exec("UPDATE asset_category SET code=NULL WHERE id='$id'");
        }
        $this->getPDO()->exec("CREATE UNIQUE INDEX UNIQ_8427034177153098EB3B4E33 ON asset_category (code, deleted)");

        $this->getPDO()->exec("DELETE FROM library WHERE deleted=1");
        while (!empty($id = $this->getDuplicate('library'))) {
            $this->getPDO()->exec("UPDATE library SET code=NULL WHERE id='$id'");
        }
        $this->getPDO()->exec("CREATE UNIQUE INDEX UNIQ_A18098BC77153098EB3B4E33 ON library (code, deleted)");
    }

    public function down(): void
    {
        throw new \Error("Downgrade is prohibited!");
    }

    protected function exec(string $query): void
    {
        try {
            $this->getPDO()->exec($query);
        } catch (\Throwable $e) {
        }
    }

    protected function getDuplicate(string $table)
    {
        $query = "SELECT c1.id
                  FROM $table c1
                  JOIN $table c2 ON c1.code=c2.code
                  WHERE c1.deleted=0
                    AND c2.deleted=0
                    AND c1.id!=c2.id
                  ORDER BY c1.id
                  LIMIT 0,1";

        return $this->getPDO()->query($query)->fetch(\PDO::FETCH_COLUMN);
    }
}
