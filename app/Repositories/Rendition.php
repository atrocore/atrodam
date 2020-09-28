<?php

declare(strict_types=1);

namespace Dam\Repositories;

use Dam\Core\DAMAttachment;
use Dam\Core\FilePathBuilder;
use Dam\Core\FileStorage\DAMUploadDir;
use Espo\ORM\Entity;

/**
 * Class Rendition
 * @package Dam\Repositories
 */
class Rendition extends \Espo\Core\Templates\Repositories\Base implements DAMAttachment
{
    /**
     * @param Entity $entity
     * @return array
     */
    public function buildPath(Entity $entity): array
    {
        return [
            ($entity->get('private') ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "{$entity->get('type')}/",
            $entity->get('private') ? FilePathBuilder::PRIVATE : FilePathBuilder::PUBLIC,
        ];
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function checkExist(Entity $entity): bool
    {
        return $this->where([
            'id='  => $entity->id,
            "type" => $entity->get("type"),
        ])->findOne() ? true : false;
    }
}
