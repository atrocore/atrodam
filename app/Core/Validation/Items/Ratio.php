<?php
declare(strict_types=1);

namespace Dam\Core\Validation\Items;

use Dam\Core\Validation\Base;
use Espo\Core\Exceptions\BadRequest;

/**
 * Class Ratio
 * @package Dam\Core\Validation\Items
 */
class Ratio extends Base
{
    /**
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->skip()) {
            return true;
        }

        $imageParams = getimagesize($this->attachment->get('tmpPath'));

        return ($imageParams[0] / $imageParams[1]) == $this->params;
    }

    /**
     * @throws BadRequest
     */
    public function onValidateFail()
    {
        throw new BadRequest("Image must have ratio {$this->params}");
    }
}