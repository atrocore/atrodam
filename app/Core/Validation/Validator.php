<?php
/*
 *  This file is part of AtroDAM.
 *
 *  AtroDAM - Open Source DAM application.
 *  Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 *  Website: https://atrodam.com
 *
 *  AtroDAM is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  AtroDAM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with AtroDAM. If not, see http://www.gnu.org/licenses/.
 *
 *  The interactive user interfaces in modified source and object code versions
 *  of this program must display Appropriate Legal Notices, as required under
 *  Section 5 of the GNU General Public License version 3.
 *
 *  In accordance with Section 7(b) of the GNU General Public License version 3,
 *  these Appropriate Legal Notices must retain the display of the "AtroDAM" word.
 *
 * This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\Core\Validation;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Injectable;

class Validator extends Injectable
{
    private $instances = [];

    public function __construct()
    {
        $this->addDependency('container');
    }

    public function validate(string $validatorName, $attachment, $params)
    {
        $className = $this->getMetadata()->get(['app', 'config', 'validations', 'classMap', $validatorName]);

        if (!$className || !class_exists($className)) {
            $className = __NAMESPACE__ . "\\Items\\" . ucfirst($validatorName);
        }

        if (!class_exists($className)) {
            throw new BadRequest("Validator with name '{$validatorName}' not found");
        }

        if (!is_a($className, Base::class, true)) {
            throw new Error("Class must implements 'Base' validator");
        }

        if (!isset($this->instances[$className])) {
            $this->instances[$className] = new $className($this->getInjection('container'));
        }

        if (!$this->instances[$className]->setAttachment($attachment)->setParams($params)->validate()) {
            $this->instances[$className]->onValidateFail();
        }
    }

    protected function getMetadata()
    {
        return $this->getInjection('container')->get('metadata');
    }
}