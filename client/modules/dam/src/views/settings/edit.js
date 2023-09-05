/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

Espo.define('dam:views/settings/edit', 'views/settings/edit', function (Dep) {

    return Dep.extend({

        scope: 'Settings',

        recordView: 'dam:views/admin/settings',

        getHeader() {
            return this.buildHeaderHtml([
                this.getLanguage().translate('Dam', 'labels', 'Admin')
            ], true)
        }
    });

});