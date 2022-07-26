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
 *  This software is not allowed to be used in Russia and Belarus.
 */

declare(strict_types=1);

namespace Dam\Entities;

use Espo\Core\Templates\Entities\Base;
use Espo\Core\Utils\Util;

/**
 * Class AssetType
 */
class AssetType extends Base
{
    /**
     * @var string
     */
    protected $entityType = "AssetType";

    /**
     * @return array
     */
    public function getValidations(): array
    {
        $result = [];

        $validations = $this->get('validationRules');
        if ($validations->count() > 0) {
            foreach ($validations as $validation) {
                if (empty($validation->get('isActive'))) {
                    continue 1;
                }

                $type = self::prepareType($validation->get('type'));

                $data = [];
                switch ($type) {
                    case 'mime':
                        if ($validation->get('validateBy') == 'List') {
                            $data['list'] = $validation->get('mimeList');
                        } elseif ($validation->get('validateBy') == 'Pattern') {
                            $data['pattern'] = $validation->get('pattern');
                        }
                        break;
                    case 'size':
                        $data['private'] = [
                            'min' => $validation->get('min'),
                            'max' => $validation->get('max'),
                        ];
                        $data['public'] = [
                            'min' => $validation->get('min'),
                            'max' => $validation->get('max'),
                        ];
                        break;
                    case 'quality':
                        $data['min'] = $validation->get('min');
                        $data['max'] = $validation->get('max');
                        break;
                    case 'colorDepth':
                        $data = $validation->get('colorDepth');
                        break;
                    case 'colorSpace':
                        $data = $validation->get('colorSpace');
                        break;
                    case 'extension':
                        $data = $validation->get('extension');
                        break;
                    case 'ratio':
                        $data = $validation->get('ratio');
                        break;
                    case 'scale':
                        $data['min'] = [
                            'width'  => $validation->get('minWidth'),
                            'height' => $validation->get('minHeight'),
                        ];
                        break;
                }

                $result[$type] = $data;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getRenditions(): array
    {
        return [];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected static function prepareType(string $type): string
    {
        return Util::toCamelCase(strtolower(str_replace(' ', '_', $type)));
    }
}
