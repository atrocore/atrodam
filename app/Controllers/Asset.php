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

namespace Dam\Controllers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Slim\Http\Request;

/**
 * Class Asset
 *
 * @package Dam\Controllers
 */
class Asset extends AbstractController
{
    /**
     * @param array   $params
     * @param array   $data
     * @param Request $request
     *
     * @return array
     * @throws BadRequest
     * @throws Forbidden
     */
    public function actionAssetsNatures($params, $data, Request $request): array
    {
        if (!$request->isGet() || empty($request->get('entity')) || empty($request->get('id'))) {
            throw new BadRequest();
        }

        if (!$this->getAcl()->check($this->name, 'read')) {
            throw new Forbidden();
        }

        return $this->getRecordService()->getAssetsNatures((string)$request->get('entity'), (string)$request->get('id'));
    }

    /**
     * @param array   $params
     * @param array   $data
     * @param Request $request
     *
     * @return array
     * @throws BadRequest
     * @throws Forbidden
     */
    public function actionAssetsForEntity($params, $data, Request $request)
    {
        if (!$request->isGet() || empty($request->get('entity')) || empty($request->get('id'))) {
            throw new BadRequest();
        }

        if (!$this->getAcl()->check($this->name, 'read')) {
            throw new Forbidden();
        }

        return $this->getRecordService()->getAssetsForEntity($request);
    }
}
