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
 */

declare(strict_types=1);

namespace Dam\Core\Validation;

use Espo\Core\Utils\Util;
use Treo\Core\Container;
use Treo\Core\ORM\EntityManager;

/**
 * Class Base
 *
 * @package Dam\Core\Validation
 */
abstract class Base
{
    /**
     * @var
     */
    protected $params;
    /**
     * @var Container
     */
    protected $container;
    /**
     * @var
     */
    protected $attachment;

    /**
     * Base constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $attachment
     *
     * @return $this
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * @param $params
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return bool
     */
    abstract public function validate(): bool;

    /**
     * @return mixed
     */
    abstract public function onValidateFail();

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->container->get('entityManager');
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getRepository(string $name)
    {
        return $this->getEntityManager()->getRepository($name);
    }

    /**
     * @return mixed
     */
    protected function getUser()
    {
        return $this->container->get('user');
    }

    /**
     * @param string $label
     * @param string $category
     * @param string $scope
     *
     * @return string
     */
    protected function translate(string $label, string $category, string $scope): string
    {
        return $this->container->get("language")->translate($label, $category, $scope);
    }

    /**
     * @param string $label
     * @param string $category
     * @param string $scope
     *
     * @return string
     */
    protected function exception(string $label): string
    {
        return $this->translate($label, 'exceptions', 'Global');
    }

    /**
     * @return string
     */
    protected function getFilePath(): string
    {
        $path = $this->getEntityManager()->getRepository('Attachment')->getFilePath($this->attachment);

        if (!file_exists($path)) {
            $path = '/tmp/' . Util::generateId() . $this->attachment->get('name');
            if (!file_exists($path)) {
                file_put_contents($path, $this->attachment->get('contents'));
            }
        }

        return $path;
    }
}