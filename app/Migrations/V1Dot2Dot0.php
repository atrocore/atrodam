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
 * Class V1Dot2Dot0
 */
class V1Dot2Dot0 extends Base
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("UPDATE attachment SET storage='UploadDir' WHERE storage='DAMUploadDir'");
        exec('mv data/dam/public/master/* upload/files/');
        exec('mv data/dam/private/master/* upload/files/');
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
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
