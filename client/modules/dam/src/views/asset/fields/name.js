/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
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
