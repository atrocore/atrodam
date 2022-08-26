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

namespace Dam\Migrations;

use Treo\Core\Migration\Base;

class V1Dot4Dot0 extends Base
{
    public function up(): void
    {
        $this->execute(
            "CREATE TABLE asset_category_hierarchy (id INT AUTO_INCREMENT NOT NULL UNIQUE COLLATE `utf8mb4_unicode_ci`, entity_id VARCHAR(24) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, parent_id VARCHAR(24) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, hierarchy_sort_order INT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, deleted TINYINT(1) DEFAULT '0' COLLATE `utf8mb4_unicode_ci`, INDEX IDX_70F97C4681257D5D (entity_id), INDEX IDX_70F97C46727ACA70 (parent_id), UNIQUE INDEX UNIQ_70F97C4681257D5D727ACA70 (entity_id, parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB"
        );
        $this->execute("DROP INDEX id ON asset_category_hierarchy");

        try {
            $records = $this->getPDO()->query("SELECT * FROM `asset_category` WHERE deleted=0")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $records = [];
        }

        foreach ($records as $record) {
            if (!empty($record['category_parent_id'])) {
                $this->execute(
                    "INSERT INTO `asset_category_hierarchy` (entity_id, parent_id, hierarchy_sort_order) VALUES ('{$record['id']}','{$record['category_parent_id']}', 0)"
                );
            }
        }

        $this->execute("DROP INDEX IDX_CATEGORY_PARENT_ID ON asset_category");
        $this->execute("ALTER TABLE asset_category ADD sort_order INT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`");
        $this->execute("ALTER TABLE asset_category DROP category_route");
        $this->execute("ALTER TABLE asset_category DROP category_route_name");
        $this->execute("ALTER TABLE asset_category DROP has_child");
        $this->execute("ALTER TABLE asset_category DROP category_parent_id");

        $this->execute("DROP INDEX IDX_ASSIGNED_USER ON asset_meta_data");
        $this->execute("DROP INDEX IDX_ASSIGNED_USER_ID ON asset_meta_data");
        $this->execute("DROP INDEX IDX_CREATED_BY_ID ON asset_meta_data");
        $this->execute("DROP INDEX IDX_MODIFIED_BY_ID ON asset_meta_data");
        $this->execute("DROP INDEX IDX_OWNER_USER ON asset_meta_data");
        $this->execute("DROP INDEX IDX_OWNER_USER_ID ON asset_meta_data");
        $this->execute("ALTER TABLE asset_meta_data DROP `description`");
        $this->execute("ALTER TABLE asset_meta_data DROP created_at");
        $this->execute("ALTER TABLE asset_meta_data DROP modified_at");
        $this->execute("ALTER TABLE asset_meta_data DROP created_by_id");
        $this->execute("ALTER TABLE asset_meta_data DROP modified_by_id");
        $this->execute("ALTER TABLE asset_meta_data DROP assigned_user_id");
        $this->execute("ALTER TABLE asset_meta_data DROP owner_user_id");

        $this->execute("RENAME TABLE `asset_meta_data` TO `asset_metadata`");

        foreach (['Asset', 'AssetCategory'] as $v) {
            try {
                \Espo\Core\Utils\Util::removeDir('custom/Espo/Custom/Resources/layouts/' . $v);
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        throw new \Error('Downgrade is prohibited!');
    }

    protected function execute(string $query): void
    {
        try {
            $this->getPDO()->exec($query);
        } catch (\Throwable $e) {
        }
    }
}
