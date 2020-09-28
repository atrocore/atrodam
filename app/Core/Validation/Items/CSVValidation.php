<?php

declare(strict_types=1);

namespace Dam\Core\Validation\Items;

use Dam\Core\Validation\Base;

/**
 * Class CSVValidation
 * @package Dam\Core\Validation\Items
 */
class CSVValidation extends Base
{
    /**
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->skip()) {
            return true;
        }

        return true;
    }

    /**
     * @throws BadRequest
     */
    public function onValidateFail()
    {
        // TODO: Implement onValidateFail() method.
    }
}