<?php

declare(strict_types=1);

namespace Dam\Controllers;

use Dam\Core\ConfigManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Utils\Json;

/**
 * Class DamConfig
 * @package Dam\Controllers
 */
class DamConfig extends AbstractController
{
    /**
     * @param $params
     * @param $data
     * @param $request
     * @return mixed
     */
    public function actionRead($params, $data, $request)
    {
        return $this->getConfigManager()->getConfig();
    }

    /**
     * @param $params
     * @param $data
     * @param $request
     * @return false|string
     * @throws NotFound
     */
    public function actionReadYaml($params, $data, $request)
    {
        if ($config = $this->getDamConfigService()->getYamlConfig()) {
            return Json::encode([
                "content" => $config,
            ]);
        }

        throw new NotFound();
    }

    /**
     * @param $params
     * @param $data
     * @param $request
     * @return mixed
     * @throws BadRequest
     */
    public function actionSaveYaml($params, $data, $request)
    {
        $service = $this->getDamConfigService();

        if ($service->validateYaml($data->content) && $service->saveYaml($data->content)) {

            $res = $this->getDamConfigService()->convertYamlToArray(yaml_parse($data->content));

            if ($res === false) {
                return Json::encode([
                    "result" => "error",
                ]);
            }

            $this->getConfig()->updateCacheTimestamp();
            $this->getConfig()->save();

            return Json::encode([
                "result" => "ok",
            ]);
        }

        throw new BadRequest();
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->getContainer()->get("ConfigManager");
    }

    protected function getDamConfigService(): \Dam\Services\DamConfig
    {
        return $this->getService("DamConfig");
    }
}