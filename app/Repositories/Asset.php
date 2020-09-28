<?php

declare(strict_types=1);

namespace Dam\Repositories;

use Dam\Core\DAMAttachment;
use Dam\Core\FilePathBuilder;
use Dam\Core\FileStorage\DAMUploadDir;
use Espo\Core\Templates\Repositories\Base;
use Espo\ORM\Entity;

/**
 * Class Asset
 *
 * @package Dam\Repositories
 */
class Asset extends Base implements DAMAttachment
{
    /**
     *
     * @param Entity $entity
     * @return array
     */
    public function buildPath(Entity $entity): array
    {
        return [
            ($entity->get('private') ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "master/",
            $entity->get('private') ? FilePathBuilder::PRIVATE : FilePathBuilder::PUBLIC,
        ];
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     * @return bool
     */
    public function linkAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getMapper()->relate($foreign, "relatedAssets", $main) &&
            $this->getMapper()->relate($main, "relatedAssets", $foreign);
    }

    /**
     * @param \Dam\Entities\Asset $main
     * @param \Dam\Entities\Asset $foreign
     * @return mixed
     */
    public function unlinkAsset(\Dam\Entities\Asset $main, \Dam\Entities\Asset $foreign)
    {
        return $this->getMapper()->unrelate($foreign, "relatedAssets", $main);
    }

}
