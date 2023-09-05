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

class V1Dot4Dot4 extends Base
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        if ($this->getConfig()->get('isMultilangActive', false) && !empty($locales = $this->getConfig()->get('inputLanguageList', []))) {
            $parts = [];

            foreach ($locales as $locale) {
                $field = 'name_' . strtolower($locale);

                $parts[] = " ADD $field VARCHAR(255) DEFAULT NULL UNIQUE COLLATE `utf8mb4_unicode_ci`";
            }

            $this->execute("ALTER TABLE asset_type" . implode(',', $parts));
        }
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        if ($this->getConfig()->get('isMultilangActive', false) && !empty($locales = $this->getConfig()->get('inputLanguageList', []))) {
            $parts = [];

            foreach ($locales as $locale) {
                $field = 'name_' . strtolower($locale);
                $parts[] = " DROP $field";
            }

            $this->execute("ALTER TABLE asset_type" . implode(',', $parts));
        }
    }

    protected function execute(string $query): void
    {
        try {
            $this->getPDO()->exec($query);
        } catch (\Throwable $e) {
        }
    }
}
