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

use Treo\Core\Migration\Base;

class V1Dot3Dot19 extends Base
{
    public function up(): void
    {
        $data = $this->getPDO()->query("SELECT id, name FROM `attachment` WHERE deleted=0")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($data as $row) {
            $this->execute("UPDATE `asset` SET name='{$row['name']}' WHERE file_id='{$row['id']}'");
        }
    }

    public function down(): void
    {
    }

    protected function execute(string $sql): void
    {
        try {
            $this->getPDO()->exec($sql);
        } catch (\Throwable $e) {
            // ignore all
        }
    }
}
