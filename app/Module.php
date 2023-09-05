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

namespace Dam;

use Espo\Core\Utils\DataUtil;
use Treo\Core\ModuleManager\AbstractModule;

/**
 * Class Module
 */
class Module extends AbstractModule
{
    /**
     * @inheritdoc
     */
    public static function getLoadOrder(): int
    {
        return 5119;
    }

    public function onLoad()
    {
        parent::onLoad();

        $this->container->setClassAlias('configManager', \Dam\Core\ConfigManager::class);
        $this->container->setClassAlias('thumbnail', \Dam\Core\Thumbnail\Image::class);
        $this->container->setClassAlias('validator', \Dam\Core\Validation\Validator::class);
    }

    /**
     * @inheritdoc
     */
    public function loadMetadata(\stdClass &$data)
    {
        $metadata = $this
            ->getObjUnifier()
            ->unify('metadata', $this->path . 'app/Resources/metadata', true);

        // checking if module PIM installed
        if ($this->container->get('metadata')->isModuleInstalled('PIM')) {
            unset($metadata->themes);
        }

        $data = DataUtil::merge($data, $metadata);

    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
