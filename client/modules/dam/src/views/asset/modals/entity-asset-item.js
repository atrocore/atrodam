/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

Espo.define('dam:views/asset/modals/entity-asset-item', ['view', "dam:config"], (Dep, Config) => {
    return Dep.extend({
        template : "dam:asset/modals/entity-asset-item",
        type     : null,
        damConfig: null,
        
        data() {
            let data = {};

            data.preview = this.model.get('filePathsData').thumbs.small;
            
            return data;
        },
        
        setup() {
            this.damConfig = Config.prototype.init.call(this);
            
            this.type = this.damConfig.getType(this.options.assetType);
            
            this.createView("entityAssetEdit", "dam:views/asset/modals/entity-asset-form", {
                model: this.model,
                el   : this.options.el + " .edit-form"
            });
        },
        
        validate() {
            let notValid = false;
            for (let key in this.nestedViews) {
                const view = this.nestedViews[key];
                if (view && typeof view.validate === 'function') {
                    notValid = view.validate() || notValid;
                }
            }
            return notValid;
        },

    });
});