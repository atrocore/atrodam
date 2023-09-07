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

namespace Dam\Entities;

use Espo\Core\Templates\Entities\Hierarchy;

/**
 * Class AssetCategory
 *
 * @package Dam\Entities
 */
class AssetCategory extends Hierarchy
{
    /**
     * @var string
     */
    protected $entityType = "AssetCategory";
}
