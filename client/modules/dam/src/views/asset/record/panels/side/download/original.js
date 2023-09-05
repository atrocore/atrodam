/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

Espo.define('dam:views/asset/record/panels/side/download/original', 'view',
    Dep => {
        return Dep.extend({
            template: "dam:asset/record/panels/side/download/original",
            active: true,

            setup() {

            },

            hide() {
                this.active = false;
            },

            show() {
                this.active = true;
            },

            buildUrl() {
                return (this.model.get('filePathsData')) ? this.model.get('filePathsData').download : ''
            }
        });
    }
);