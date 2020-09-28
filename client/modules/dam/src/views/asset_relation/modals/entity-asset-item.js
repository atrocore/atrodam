

Espo.define('dam:views/asset_relation/modals/entity-asset-item', ['view', "dam:config"], (Dep, Config) => {
    return Dep.extend({
        template : "dam:asset_relation/modals/entity-asset-item",
        type     : null,
        damConfig: null,
        
        data() {
            let data = {};
            
            data.preview = `?entryPoint=preview&size=small&id=${this.model.get("assetId")}`;
            
            return data;
        },
        
        setup() {
            this.damConfig = Config.prototype.init.call(this);
            
            this.type = this.damConfig.getType(this.options.assetType);
            
            this.createView("entityAssetEdit", "dam:views/asset_relation/modals/entity-asset-form", {
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
        
        _showPreview() {
            let config = this.damConfig.getByType(this.type);
            return config.nature === "image" || config.preview;
        }
    });
});