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

namespace Dam\Listeners;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\EventManager\Event;

class SettingsController extends \Espo\Listeners\AbstractListener
{
    public function beforeActionUpdate(Event $event): void
    {
        $data = $event->getArgument('data');

        if (!empty($data->fileNameRegexPattern) && !preg_match('/^\/((?:(?:[^?+*{}()[\]\\\\|]+|\\\\.|\[(?:\^?\\\\.|\^[^\\\\]|[^\\\\^])(?:[^\]\\\\]+|\\\\.)*\]|\((?:\?[:=!]|\?<[=!]|\?>)?(?1)??\)|\(\?(?:R|[+-]?\d+)\))(?:(?:[?+*]|\{\d+(?:,\d*)?\})[?+]?)?|\|)*)\/[gmixsuAJD]*$/', $data->fileNameRegexPattern)) {
            throw new BadRequest($this->getLanguage()->translate('regexNotValid', 'exceptions', 'FieldManager'));
        }
    }
}
