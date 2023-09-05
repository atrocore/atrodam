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

/**
 * Class V1Dot2Dot14
 */
class V1Dot2Dot14 extends V1Dot2Dot13
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute("UPDATE `validation_rule` SET extension='" . json_encode(self::getVideoExtensions()) . "' WHERE id='603d0f0435374022f'");
        $this->execute("DELETE FROM `validation_rule` WHERE id='603d0f4e8bd0a907e'");
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
    }

    public static function getVideoExtensions(): array
    {
        return [
            "3g2",
            "3gp",
            "aaf",
            "asf",
            "avchd",
            "avi",
            "drc",
            "flv",
            "m2v",
            "m4p",
            "m4v",
            "mkv",
            "mng",
            "mov",
            "mp2",
            "mp4",
            "mpe",
            "mpeg",
            "mpg",
            "mpv",
            "mxf",
            "nsv",
            "ogg",
            "ogv",
            "qt",
            "rm",
            "rmvb",
            "roq",
            "svi",
            "vob",
            "webm",
            "wmv",
            "yuv"
        ];
    }
}
