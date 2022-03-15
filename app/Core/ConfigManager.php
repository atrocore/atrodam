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

namespace Dam\Core;

use Treo\Core\Container;

/**
 * Class ConfigManager
 *
 * @package Dam\Core
 */
class ConfigManager
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var
     */
    protected $config;

    const PATH_TO_DAM = "data/dam";

    /**
     * ConfigManager constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $type
     *
     * @return string
     */
    public static function getType($type)
    {
        return strtolower(str_replace(" ", '-', $type));
    }

    /**
     * @param array $path
     * @param array $config
     *
     * @return array|mixed|null|string
     */
    public function get(array $path, array $config = [])
    {
        if (!$config) {
            $config = $this->getConfig();
        }

        foreach ($path as $pathItem) {
            if (isset($config[$pathItem])) {
                $config = $config[$pathItem];
            } else {
                return null;
            }
        }

        return $config;
    }

    /**
     * @param array $path
     *
     * @return array|mixed|string|null
     */
    public function getByType(array $path)
    {
        $config = $this->getConfig();

        if (!isset($config['type']['custom'][$path[0]])) {
            return $config['default'];
        }

        return $this->get($path, $config['type']['custom']);
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        if (!$this->config) {
            $result = [
                'type'             => [
                    'custom'  => [
                        'file' => [
                            'validations' => [],
                            'renditions'  => [],
                        ]
                    ],
                    'default' => [
                        'validations' => [],
                        'renditions'  => [],
                    ]
                ],
                'attributeMapping' => [
                    'size'        => [
                        'field' => 'size',
                    ],
                    'orientation' => [
                        'field' => 'orientation',
                    ],
                    'width'       => [
                        'field' => 'width',
                    ],
                    'height'      => [
                        'field' => 'height',
                    ],
                    'color-depth' => [
                        'field' => 'colorDepth',
                    ],
                    'color-space' => [
                        'field' => 'colorSpace',
                    ],
                ]
            ];

            $types = $this
                ->container
                ->get('entityManager')
                ->getRepository('AssetType')
                ->find();

            if ($types->count() > 0) {
                foreach ($types as $type) {
                    $name = strtolower(str_replace(" ", "-", $type->get('name')));
                    $result['type']['custom'][$name] = [
                        'validations' => $type->getValidations(),
                        'renditions'  => $type->getRenditions(),
                    ];
                }
            }

            $this->config = $result;
        }

        return $this->config;
    }
}