<?php

declare(strict_types=1);

namespace Dam\Controllers;

use Espo\Core\Exceptions;
use Espo\Core\Templates\Controllers\Base;
use Slim\Http\Request;

/**
 * Class AbstractController
 * @package Dam\Controllers
 */
class AbstractController extends Base
{
    /**
     * Validate Get action
     *
     * @param Request $request
     *
     * @return bool
     * @throws Exceptions\BadRequest
     * @throws Exceptions\Forbidden
     */
    public function isReadAction(Request $request): bool
    {
        if (!$request->isGet()) {
            throw new Exceptions\BadRequest();
        }

        if (!$this->getAcl()->check($this->name, 'read')) {
            throw new Exceptions\Forbidden();
        }

        return true;
    }

    /**
     * Validate Put action
     *
     * @param Request $request
     *
     * @return bool
     * @throws Exceptions\BadRequest
     * @throws Exceptions\Forbidden
     */
    public function isPutAction($request)
    {
        if (!$request->isPut()) {
            throw new Exceptions\BadRequest();
        }

        if (!$this->getAcl()->check($this->name, 'edit')) {
            throw new Exceptions\Forbidden();
        }

        return true;
    }

    /**
     * @param $request
     * @return bool
     */
    public function isPostAction($request)
    {
        if (!$request->isPost()) {
            throw new Exceptions\BadRequest();
        }

        if (!$this->getAcl()->check($this->name, 'edit')) {
            throw new Exceptions\Forbidden();
        }

        return true;
    }
}