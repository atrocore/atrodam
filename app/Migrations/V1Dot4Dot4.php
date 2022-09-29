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
 *  This software is not allowed to be used in Russia and Belarus.
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
