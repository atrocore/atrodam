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
 * This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\Migrations;

use Espo\Core\Exceptions\Error;
use Treo\Core\Migration\Base;

class V1Dot3Dot64 extends Base
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `asset_type` ADD assign_automatically TINYINT(1) DEFAULT '0' NOT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("DROP INDEX IDX_TYPE ON `asset`");
        $this->execute("ALTER TABLE `asset` CHANGE `type` type MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `asset_type` DROP is_default");

        try {
            $assets = $this->getPDO()->query("SELECT id, `type` FROM `asset` WHERE deleted=0")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $assets = [];
        }

        foreach ($assets as $asset) {
            $this->execute("UPDATE `asset` SET `type`='[\"{$asset['type']}\"]' WHERE id='{$asset['id']}'");
        }

        try {
            $container = (new \Espo\Core\Application())->getContainer();
            $container->get('metadata')->delete('entityDefs', 'Asset', ['fields.type']);
            $container->get('metadata')->save();
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        throw new Error('Downgrade is prohibited!');
    }

    protected function execute(string $query): void
    {
        try {
            $this->getPDO()->exec($query);
        } catch (\Throwable $e) {
        }
    }
}
