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

namespace Dam\Controllers;

/**
 * Class DamConfig
 */
class DamConfig extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function actionRead($params, $data, $request)
    {
        return $this->getContainer()->get("configManager")->getConfig();
    }
}