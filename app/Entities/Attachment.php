<?php

declare(strict_types=1);

namespace Dam\Entities;

/**
 * Class Attachment
 *
 * @package Dam\Entities
 */
class Attachment extends \Treo\Entities\Attachment
{
    /**
     * @var string
     */
    protected $entityType = "Attachment";

    /**
     * @return string
     */
    public function _getStorage()
    {
        return $this->valuesContainer['storage'] ? $this->valuesContainer['storage'] : "DAMUploadDir";
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $baseFileInfo = pathinfo($this->get("name"));
        $this->set("name", $name . "." . $baseFileInfo['extension']);

        return $this;
    }
}
