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

/**
 * Class V1Dot1Dot0
 */
class V1Dot1Dot0 extends Base
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("CREATE TABLE `library` (`id` VARCHAR(24) NOT NULL COLLATE utf8mb4_unicode_ci, `name` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, `description` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `created_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `code` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `is_active` TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci, `created_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `assigned_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `owner_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX `IDX_CREATED_BY_ID` (created_by_id), INDEX `IDX_MODIFIED_BY_ID` (modified_by_id), INDEX `IDX_ASSIGNED_USER_ID` (assigned_user_id), INDEX `IDX_OWNER_USER_ID` (owner_user_id), INDEX `IDX_NAME` (name, deleted), INDEX `IDX_ASSIGNED_USER` (assigned_user_id, deleted), INDEX `IDX_OWNER_USER` (owner_user_id, deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("CREATE TABLE `library_asset_category` (`id` INT AUTO_INCREMENT NOT NULL UNIQUE COLLATE utf8mb4_unicode_ci, `library_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `asset_category_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, INDEX `IDX_370C9561137ABCF` (library_id), INDEX `IDX_370C956993EC4EB` (asset_category_id), UNIQUE INDEX `UNIQ_370C9561137ABCF993EC4EB` (library_id, asset_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;");
        $this->execute("DROP TABLE collection");
        $this->execute("DROP TABLE collection_asset_category");
        $this->execute("DROP INDEX IDX_COLLECTION_ID ON `asset`");
        $this->execute("ALTER TABLE `asset` ADD library_id VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("CREATE INDEX IDX_LIBRARY_ID ON `asset` (library_id)");
        $this->execute("INSERT INTO `library` (`id`, `name`, `code`, `is_active`) VALUES ('1', 'Default Library', 'default_library', 1)");
        $this->execute("UPDATE asset SET library_id='1' WHERE library_id IS NULL");
        $this->execute("ALTER TABLE `asset` DROP name_of_file, DROP code, DROP collection_id, CHANGE `private` private TINYINT(1) DEFAULT '1' NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE `library_id` library_id VARCHAR(24) DEFAULT '1' COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `category_asset` ADD sorting INT DEFAULT '100000' COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `product_asset` ADD sorting INT DEFAULT '100000' COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `product_asset` ADD scope MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci");
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute("CREATE TABLE `collection` (`id` VARCHAR(24) NOT NULL COLLATE utf8mb4_unicode_ci, `name` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, `description` MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, `created_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_at` DATETIME DEFAULT NULL COLLATE utf8mb4_unicode_ci, `code` VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `is_active` TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci, `created_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `modified_by_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `assigned_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `owner_user_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX `IDX_CREATED_BY_ID` (created_by_id), INDEX `IDX_MODIFIED_BY_ID` (modified_by_id), INDEX `IDX_ASSIGNED_USER_ID` (assigned_user_id), INDEX `IDX_OWNER_USER_ID` (owner_user_id), INDEX `IDX_NAME` (name, deleted), INDEX `IDX_ASSIGNED_USER` (assigned_user_id, deleted), INDEX `IDX_OWNER_USER` (owner_user_id, deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("CREATE TABLE `collection_asset_category` (`id` INT AUTO_INCREMENT NOT NULL UNIQUE COLLATE utf8mb4_unicode_ci, `asset_category_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `collection_id` VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci, `deleted` TINYINT(1) DEFAULT '0' COLLATE utf8mb4_unicode_ci, INDEX `IDX_8F27FD8A993EC4EB` (asset_category_id), INDEX `IDX_8F27FD8A514956FD` (collection_id), UNIQUE INDEX `UNIQ_8F27FD8A993EC4EB514956FD` (asset_category_id, collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
        $this->execute("DROP TABLE library");
        $this->execute("DROP TABLElibrary_asset_category");
        $this->execute("DROP INDEX IDX_LIBRARY_ID ON `asset`");
        $this->execute("ALTER TABLE `asset` DROP library_id, CHANGE `private` private TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci, ADD name_of_file VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD code VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD collection_id VARCHAR(24) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("CREATE INDEX IDX_COLLECTION_ID ON `asset` (collection_id)");
        $this->execute("ALTER TABLE `category_asset` DROP sorting");
        $this->execute("ALTER TABLE `product_asset` DROP sorting");
    }

    /**
     * @param string $sql
     */
    protected function execute(string $sql)
    {
        try {
            $this->getPDO()->exec($sql);
        } catch (\Throwable $e) {
            // ignore all
        }
    }
}
