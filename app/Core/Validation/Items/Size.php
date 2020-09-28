<?php

declare(strict_types=1);

namespace Dam\Core\Validation\Items;

use Dam\Core\Validation\Base;
use Espo\Core\Exceptions\Error;

/**
 * Class Size
 * @package Dam\Core\Validation\Items
 */
class Size extends Base
{
    /**
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->skip()) {
            return true;
        }

        $imageSize = (filesize($this->attachment->get('tmpPath')) / 1024);

        if ($imageSize >= $this->params['min'] && $imageSize <= $this->params['max']) {
            return true;
        }

        return false;
    }

    /**
     * @throws Error
     */
    public function onValidateFail()
    {
        throw new Error("File size should not exceed from {$this->params['min']}kb to {$this->params['max']}kb");
    }
}