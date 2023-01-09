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

Espo.define('dam:views/asset/fields/name', 'views/fields/varchar',
    Dep => Dep.extend({

        detailTemplate: "dam:fields/name/detail",

        editTemplate: 'dam:asset/fields/name/edit',

        validations: ['name'],

        data() {
            let data = _.extend({attachmentId: this.model.get("fileId")}, Dep.prototype.data.call(this));

            const parts = (data.value || '').split('.');

            data['fileExt'] = parts.length >= 2 ? parts.pop() : '';
            data['valueWithoutExt'] = parts.length > 1 ? parts.join('.') : parts[0];

            return data;
        },

        validateName() {
            let name = this.model.get(this.name);
            let fileNameRegexPatternString = this.getConfig().get('fileNameRegexPattern');
            let fileNameRegexPattern = this.convertStrToRegex(fileNameRegexPatternString);

            if (fileNameRegexPattern && !fileNameRegexPattern.test(name)) {
                let msg = this.translate('fileNameNotValidByUserRegex', 'exceptions', 'Asset').replace('%s', fileNameRegexPattern);
                this.showValidationMessage(msg, '[name="' + this.name + '"]');
                return true;
            }

            return false;
        },
    })
);
