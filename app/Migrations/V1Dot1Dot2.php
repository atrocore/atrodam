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

use Treo\Core\Migration\Base;

/**
 * Class V1Dot1Dot2
 */
class V1Dot1Dot2 extends Base
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("ALTER TABLE `asset_type` DROP nature");
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute("ALTER TABLE `asset_type` ADD nature VARCHAR(255) DEFAULT 'File' COLLATE utf8mb4_unicode_ci");
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
