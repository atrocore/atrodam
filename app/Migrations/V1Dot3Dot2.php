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

/**
 * Migration for version 1.3.2
 */
class V1Dot3Dot2 extends V1Dot2Dot14
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("ALTER TABLE `asset_type` ADD sort_order INT DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD is_default TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `asset_type` DROP INDEX IDX_NAME, ADD UNIQUE INDEX UNIQ_68BA92E15E237E06EB3B4E33 (name, deleted)");

        $ids = $this->getPDO()->query("SELECT id FROM `asset_type` WHERE deleted=0")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($ids as $k => $id) {
            $this->execute("UPDATE `asset_type` SET sort_order=$k WHERE id='$id'");
        }
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute("ALTER TABLE `asset_type` DROP sort_order, DROP is_default");
        $this->execute("ALTER TABLE `asset_type` DROP INDEX UNIQ_68BA92E15E237E06EB3B4E33, ADD INDEX IDX_NAME (name, deleted)");
    }
}
