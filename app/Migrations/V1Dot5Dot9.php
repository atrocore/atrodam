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
