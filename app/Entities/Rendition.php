<?php

declare(strict_types=1);

namespace Dam\Entities;

use Dam\Core\FilePathBuilder;
use Dam\Core\FileStorage\DAMUploadDir;
use Dam\Core\PathInfo;
use Espo\Core\Templates\Entities\Base;

/**
 * Class Rendition
 * @package Dam\Entities
 */
class Rendition extends Base implements PathInfo
{
    /**
     * @var bool
     */
    public $isAutoCreated = false;
    /**
     * @var string
     */
    protected $entityType = "Rendition";

    /**
     * @return array
     */
    public function getPathInfo(): array
    {
        return [
            ($this->get('private') ? DAMUploadDir::PRIVATE_PATH : DAMUploadDir::PUBLIC_PATH) . "{$this->get('type')}/",
            $this->get('private') ? FilePathBuilder::PRIVATE : FilePathBuilder::PUBLIC,
        ];
    }

    /**
     * @return string
     */
    public function getMainFolder(): string
    {
        return $this->get("type");
    }
}
