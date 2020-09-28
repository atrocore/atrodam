<?php

declare(strict_types=1);

namespace Dam\Controllers;

use Espo\Core\Exceptions\NotFound;

/**
 * Class Rendition
 * @package Dam\Controllers
 */
class Rendition extends \Espo\Core\Templates\Controllers\Base
{
    /**
     * @param $params
     * @param $data
     * @param $request
     * @throws NotFound
     */
    public function actionList($params, $data, $request)
    {
        throw new NotFound();
    }
}
