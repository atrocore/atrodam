<?php
/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

declare(strict_types=1);

namespace Dam\Controllers;

use Espo\Core\Controllers\Base;

class AssetMetadata extends Base
{
    const MAX_SIZE_LIMIT = 200;

    public function actionListLinked($params, $data, $request)
    {
        $id = $params['id'];
        $link = $params['link'];

        $where = $request->get('where');
        $offset = $request->get('offset');
        $maxSize = $request->get('maxSize');
        $asc = $request->get('asc', 'true') === 'true';
        $sortBy = $request->get('sortBy');
        $q = $request->get('q');
        $textFilter = $request->get('textFilter');

        if (empty($maxSize)) {
            $maxSize = self::MAX_SIZE_LIMIT;
        }

        $params = [
            'where'      => $where,
            'offset'     => $offset,
            'maxSize'    => $maxSize,
            'asc'        => $asc,
            'sortBy'     => $sortBy,
            'q'          => $q,
            'textFilter' => $textFilter
        ];

        $result = $this->getServiceFactory()->create('AssetMetadata')->findLinkedEntities($id, $link, $params);

        return [
            'total' => $result['total'],
            'list'  => isset($result['collection']) ? $result['collection']->getValueMapList() : $result['list']
        ];
    }
}
