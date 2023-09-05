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
