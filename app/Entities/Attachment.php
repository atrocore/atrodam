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

/**
 * Class Attachment
 *
 * @package Dam\Entities
 */
class Attachment extends \Espo\Entities\Attachment
{
    /**
     * @var string
     */
    protected $entityType = "Attachment";

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $baseFileInfo = pathinfo($this->get("name"));
        $this->set("name", $name . "." . $baseFileInfo['extension']);

        return $this;
    }

    /**
     * @return Asset|null
     */
    public function getAsset(): ?Asset
    {
        return $this->entityManager->getRepository($this->getEntityType())->getAsset($this);
    }
}
