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
