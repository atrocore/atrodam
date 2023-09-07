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

class V1Dot3Dot66 extends Base
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `asset_type` ADD types_to_exclude MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE `asset_type` DROP types_to_exclude");
    }

    protected function execute(string $query): void
    {
        try {
            $this->getPDO()->exec($query);
        } catch (\Throwable $e) {
        }
    }
}
