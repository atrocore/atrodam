<?php

declare(strict_types=1);

namespace Dam\Controllers;

use Espo\Core\Exceptions\NotFound;

/**
 * Class AssetMetaData
 * @package Dam\Controllers
 */
class AssetMetaData extends \Espo\Core\Templates\Controllers\Base
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
