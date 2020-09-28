<?php

declare(strict_types=1);

namespace Dam\Entities;

use Dam\Core\FilePathBuilder;
use Dam\Core\FileStorage\DAMUploadDir;
use Dam\Core\PathInfo;
use Espo\Core\Templates\Entities\Base;

/**
 * Class Asset
 *
 * @package Dam\Entities
 */
class Asset extends Base implements PathInfo
{
    /**
     * @var string
     */
    protected $entityType = "Asset";

    /**
     * @return array
     */
    public function getPathInfo(): array
    {
        return [
            ($this->get('private') ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "master/",
            $this->get('private') ? FilePathBuilder::PRIVATE : FilePathBuilder::PUBLIC,
        ];
    }

    /**
     * @return string
     */
    public function getMainFolder(): string
    {
        return "master";
    }

    /**
     * @return array
     */
    public static function staticRelations()
    {
        return [
            'renditions',
            'collection',
            'assetMetaDatas',
            'assetVersions',
            'createdBy',
            'modifiedBy',
            'assignedUser',
            'teams',
            'file',
            'assetCategories',
            'ownerUser',
        ];
    }
}
