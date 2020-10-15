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

namespace Dam\Core\Utils;

use Dam\Entities\Asset;
use Espo\Core\Exceptions\BadRequest;
use Treo\Core\Container;

/**
 * Class EntityManager
 * @package Dam\Core\Utils
 */
class EntityManager extends \Espo\Core\Utils\EntityManager
{
    /**
     * @var array
     */
    protected $asset_relation = [];
    /**
     * @var array
     */
    protected $relation       = [];

    const ASSET_RELATION_NAME = "asset_relations";
    const RELATION_NAME       = 'relations';

    /**
     * @param string $entityName
     * @return array
     */
    protected static function getAssetRelationTemplate(string $entityName)
    {
        return [
            [
                "name"       => self::ASSET_RELATION_NAME,
                "label"      => "Asset Relations",
                "view"       => "dam:views/asset_relation/record/panels/bottom-panel",
                "entityName" => $entityName,
            ],
        ];
    }

    /**
     * EntityManager constructor.
     * @param Container|null $container
     */
    public function __construct(Container $container = null)
    {
        $this->relation = [
            [
                "name"  => self::RELATION_NAME,
                "label" => "Relations",
                "view"  => "dam:views/asset/record/panels/relations",
            ],
        ];

        parent::__construct(
            $container->get('metadata'),
            $container->get('language'),
            $container->get('fileManager'),
            $container->get('config'),
            $container
        );
    }

    /**
     * @param array $params
     * @return bool
     * @throws \Espo\Core\Exceptions\BadRequest
     * @throws \Espo\Core\Exceptions\Conflict
     * @throws \Espo\Core\Exceptions\Error
     */
    public function createLink(array $params)
    {
        $relationEntityName = $params['entity'] === "Asset" ? $params['entityForeign'] : $params['entity'];

        if (
            $this->isAsset($params['entityForeign'], $params['entity']) &&
            $this->hasLinkTo($relationEntityName, "Asset")
        ) {
            throw new BadRequest("You can't create link to 'Asset' entity");
        }

        $res = parent::createLink($params);

        if ($params['entity'] === "Asset" || $params['entityForeign'] === "Asset") {
            $this->createAssetRelations($params);
            $this->createRelation();
            $this->createMassUploadAsset($params);
        }

        return $res;
    }

    /**
     * @param array $params
     * @return bool
     * @throws \Espo\Core\Exceptions\Error
     */
    public function deleteLink(array $params)
    {
        $res = parent::deleteLink($params);

        if ($params['entity'] === "Asset" || $params['link'] === "assets") {
            $this->deleteAssetRelations($params);
            $this->deleteRelation();
        }

        return $res;
    }

    /**
     * @param $params
     * @return mixed
     */
    protected function createAssetRelations($params)
    {
        $relationEntityName = $params['entity'] === "Asset" ? $params['entityForeign'] : $params['entity'];
        $currentDetailPanel = $this->getMetadata()->get([
            "clientDefs",
            $relationEntityName,
            "bottomPanels",
            "detail",
        ]);

        $panels = array_filter($currentDetailPanel ?? [],
            function ($item) {
                return ($item['name'] !== self::ASSET_RELATION_NAME);
            }
        );
        $this->getMetadata()->set("clientDefs", $relationEntityName, [
            "bottomPanels" => [
                "detail" => array_merge(
                    $this->getAssetRelationTemplate($relationEntityName),
                    $panels ?? []
                ),
            ],
        ]);

        return $this->getMetadata()->save();
    }

    /**
     * @return mixed
     */
    protected function createRelation()
    {
        $panels = array_filter($this->getMetadata()->get(["clientDefs", "Asset", "bottomPanels", "detail"]),
            function ($item) {
                return ($item['name'] !== self::RELATION_NAME);
            }
        );

        $this->getMetadata()->set("clientDefs", "Asset", [
            "bottomPanels" => [
                "detail" => array_merge(
                    $this->relation,
                    $panels ?? []
                ),
            ],
        ]);

        return $this->getMetadata()->save();
    }

    /**
     * @param array $params
     */
    protected function createMassUploadAsset(array $params)
    {
        $link   = $params['entityForeign'] === "Asset" ? $params['link'] : $params['linkForeign'];
        $entity = $params['entity'] === "Asset" ? $params['entityForeign'] : $params['entity'];

        $this->getMetadata()->set("clientDefs", $entity, [
            "relationshipPanels" => [
                $link => [
                    "view" => "dam:views/asset/record/panels/bottom-panel",
                ],
            ],
        ]);

        $this->getMetadata()->save();
    }

    /**
     * @param $params
     * @return bool
     */
    protected function deleteAssetRelations($params)
    {
        $relationEntityName = $params['entity'];

        if ($params['entity'] === "Asset") {
            $relationLink       = $params['link'];
            $relationEntityName = $this->getMetadata()->get(["entityDefs", "Asset", "links", $relationLink, "entity"]);
        }

        if ($this->hasLinkTo($relationEntityName, "Asset")) {
            return true;
        }

        $panels = array_filter($this->getMetadata()->get(["clientDefs", $relationEntityName, "bottomPanels", "detail"]),
            function ($item) {
                return ($item['name'] !== self::ASSET_RELATION_NAME);
            }
        );

        $this->getMetadata()->set("clientDefs", $relationEntityName, [
            "bottomPanels" => [
                "detail" => $panels ?? [],
            ],
        ]);

        return $this->getMetadata()->save();
    }

    /**
     * @return bool
     */
    protected function deleteRelation()
    {
        $links = array_keys($this->getMetadata()->get(["entityDefs", "Asset", "links"]));
        $diff  = array_diff($links, Asset::staticRelations());

        if (!$diff) {
            $panels = array_filter($this->getMetadata()->get(["clientDefs", "Asset", "bottomPanels", "detail"]),
                function ($item) {
                    return ($item['name'] !== self::RELATION_NAME);
                }
            );

            $this->getMetadata()->set("clientDefs", "Asset", [
                "bottomPanels" => [
                    "detail" => $panels ?? [],
                ],
            ]);

            return $this->getMetadata()->save();
        }

        return true;
    }

    /**
     * @param string|null $entityName
     * @param string|null $to
     * @return bool
     */
    protected function hasLinkTo(?string $entityName, ?string $to)
    {
        $links = $this->getMetadata()->get(['entityDefs', $entityName, "links"]);

        foreach ($links as $link) {
            if ($link['entity'] === $to) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $entityName1
     * @param string $entityName2
     * @return bool
     */
    protected function isAsset(string $entityName1, string $entityName2)
    {
        return $entityName1 === "Asset" || $entityName2 === "Asset";
    }

}