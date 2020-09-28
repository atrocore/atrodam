<?php

declare(strict_types=1);

namespace Dam\Controllers;

use Espo\Core\Exceptions;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Treo\Core\Slim\Http\Request;

/**
 * Class AssetRelation
 * @package Dam\Controllers
 */
class AssetRelation extends AbstractController
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

    /**
     * @param         $params
     * @param         $data
     * @param Request $request
     * @return mixed
     * @throws \Espo\Core\Exceptions\BadRequest
     * @throws \Espo\Core\Exceptions\Forbidden
     */
    public function actionItemsInEntity($params, $data, Request $request)
    {
        $typesList = $this->getMetadata()->get(["entityDefs", "Asset", "fields", "type", "options"]);
        $list      = array_intersect($typesList, explode(',', $request->get("list")));

        if (!$this->isReadAction($request) || !$list) {
            throw new Exceptions\BadRequest("List can't be empty");
        }

        $list = $this->getRecordService()->getItemsInList($list, $params['entity_name'], $params['entity_id']);

        return [
            "list"  => $list,
            "count" => count($list),
        ];
    }

    /**
     * @param         $params
     * @param         $data
     * @param Request $request
     * @return array
     */
    public function actionByEntity($params, $data, Request $request)
    {
        if (!$this->isReadAction($request)) {
            throw new Exceptions\Error();
        }

        $list = $this->getRecordService()->getItems($params['entity_id'], $params['entity_name'], $request);

        return [
            'list'  => $list,
            'total' => count($list),
        ];
    }

    /**
     * @param         $params
     * @param         $data
     * @param Request $request
     * @return mixed
     */
    public function actionSortOrder($params, $data, Request $request)
    {
        if (!$this->isPutAction($request)) {
            throw new Exceptions\Error();
        }

        return $this
            ->getRecordService()
            ->updateSortOrder($params["entity_name"], $params['entity_id'], $data->ids);
    }

    /**
     * @param         $params
     * @param         $data
     * @param Request $request
     * @return array
     */
    public function actionEntityList($params, $data, Request $request)
    {
        if (!$this->isReadAction($request)) {
            throw new Exceptions\Error();
        }

        $list = $this->getRecordService()->getAvailableEntities($params['asset_id']);

        return [
            'list'  => $list,
            'count' => count($list),
        ];
    }

    /**
     * @param         $params
     * @param         $data
     * @param Request $request
     * @return array
     */
    public function actionByAsset($params, $data, Request $request)
    {
        if (!$this->isReadAction($request)) {
            throw new Exceptions\Error();
        }

        $list = $this->getRecordService()->getItems($params['asset_id'], "Asset", $request->get("entity"));

        return [
            'list'  => $list,
            'total' => count($list),
        ];
    }

    /**
     * @param         $params
     * @param         $data
     * @param Request $request
     * @return mixed
     */
    public function actionUpdateBy($params, $data, Request $request)
    {
        if (!$request->isPost() && !$request->isPatch()) {
            throw new BadRequest();
        }

        if (!$this->getAcl()->check($this->name, 'edit')) {
            throw new Forbidden();
        }

        $model = $this->getRecordService()->getItem([
            "entityName" => (string)$request->get("entityName"),
            "entityId"   => (string)$request->get("entityId"),
            "assetId"    => (string)$request->get("assetId"),
        ]);

        if ($entity = $this->getRecordService()->updateEntity($model->id, $data)) {
            return $entity->getValueMap();
        }

        throw new BadRequest();
    }
}
