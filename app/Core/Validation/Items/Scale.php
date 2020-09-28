<?php

declare(strict_types=1);

namespace Dam\Core\Validation\Items;

use Dam\Core\Validation\Base;
use Espo\Core\Exceptions\BadRequest;

/**
 * Class Scale
 * @package Dam\Core\Validation\Items
 */
class Scale extends Base
{
    /**
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->skip()) {
            return true;
        }

        list ($width, $height) = getimagesize($this->attachment->get("tmpPath"));

        return $width > $this->params['min']['width'] && $height > $this->params['min']['height'];
    }

    /**
     * @throws BadRequest
     */
    public function onValidateFail()
    {
        throw new BadRequest("Image must have width more than {$this->params['min']['width']} and height more than {$this->params['min']['height']}");
    }
}