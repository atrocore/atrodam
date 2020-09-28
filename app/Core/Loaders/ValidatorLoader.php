<?php
declare(strict_types=1);

namespace Dam\Core\Loaders;

use Dam\Core\Validation\Validator;
use Treo\Core\Loaders\Base;

/**
 * Class ValidatorLoader
 * @package Dam\Core\Loaders
 */
class ValidatorLoader extends Base
{
    /**
     * @return Validator
     */
    public function load()
    {
        return new Validator($this->getContainer());
    }
}