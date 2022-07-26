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

/**
 * Class V1Dot2Dot13
 */
class V1Dot2Dot13 extends V1Dot2Dot1
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("INSERT INTO `asset_type` (`id`, `name`, `deleted`, `created_at`, `modified_at`, `created_by_id`, `modified_by_id`) VALUES ('603d0ed4662639434', 'Video', 0, '2021-03-01 00:00:00', '2021-03-01 00:00:00', '1', NULL)");
        $this->execute("INSERT INTO `validation_rule` (`id`, `name`, `deleted`, `created_at`, `modified_at`, `is_active`, `type`, `ratio`, `validate_by`, `pattern`, `min`, `max`, `color_depth`, `color_space`, `min_width`, `min_height`, `extension`, `mime_list`, `created_by_id`, `modified_by_id`, `asset_type_id`) VALUES ('603d0f0435374022f', 'Extension', 0, '2021-03-01 15:57:56', '2021-03-01 15:57:56', 1, 'Extension', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, '[\"mp4\",\"webm\",\"ogv\"]', NULL, '1', NULL, '603d0ed4662639434'),('603d0f4e8bd0a907e', 'Mime', 0, '2021-03-01 15:59:10', '2021-03-01 15:59:10', 1, 'Mime', NULL, 'List', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '[\"video\\/mp4\",\"video\\/webm\",\"video\\/ogg\"]', '1', NULL, '603d0ed4662639434')");
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
    }
}
