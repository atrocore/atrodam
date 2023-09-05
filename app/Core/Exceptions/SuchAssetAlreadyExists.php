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

namespace Dam\Core\Exceptions;

use Dam\Entities\Asset;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\WithStatusReasonData;

/**
 * Class SuchAssetAlreadyExists
 */
class SuchAssetAlreadyExists extends BadRequest implements WithStatusReasonData
{
    /**
     * @var Asset|null
     */
    protected $asset = null;

    /**
     * @inheritDoc
     */
    public function getStatusReasonData(): string
    {
        if (empty($this->asset)) {
            return '';
        }

        return $this->asset->get('id');
    }

    /**
     * @param Asset $asset
     *
     * @return SuchAssetAlreadyExists
     */
    public function setAsset(Asset $asset): SuchAssetAlreadyExists
    {
        $this->asset = $asset;

        return $this;
    }
}
