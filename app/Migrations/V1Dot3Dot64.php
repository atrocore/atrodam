<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
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
