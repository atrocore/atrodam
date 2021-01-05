<?php

declare(strict_types=1);

namespace Dam\Migrations;

use Treo\Core\Migration\Base;

/**
 * Class V1Dot1Dot5
 *
 * @package Dam\Migrations
 */
class V1Dot1Dot5 extends Base
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("
            UPDATE asset, attachment 
            SET asset.name = attachment.name 
            WHERE asset.file_id = attachment.id;
        ");
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute("
            UPDATE asset 
            SET asset.name = SUBSTRING_INDEX(asset.name, '.', 1);
        ");
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
