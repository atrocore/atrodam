<?php

declare(strict_types=1);

namespace Dam;

use Espo\Core\Utils\DataUtil;
use Treo\Core\ModuleManager\AbstractModule;
use Treo\Core\Utils\Metadata;

/**
 * Class Module
 *
 * @author r.ratsun <r.ratsun@gmail.com>
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

    /**
     * Get Metadata
     *
     * @return Metadata
     */
    protected function getMetadata(): Metadata
    {
        return $this->container->get('metadata');
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
        if ($this->getMetadata()->isModuleInstalled('PIM')) {
            unset($metadata->themes);
        }

        $data = DataUtil::merge($data, $metadata);

    }

    /**
     * @return string|\Treo\Core\ModuleManager\string
     */
    public function getPath()
    {
        return $this->path;
    }
}
