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

/**
 * Class V1Dot2Dot38
 */
class V1Dot2Dot38 extends V1Dot2Dot14
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("ALTER TABLE `asset` DROP path");
        $this->execute("ALTER TABLE `asset` DROP meta_data");

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute("ALTER TABLE `asset` ADD path VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci");
        $this->execute("ALTER TABLE `asset` ADD meta_data MEDIUMTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci");
    }
}
