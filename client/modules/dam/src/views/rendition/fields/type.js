

Espo.define('dam:views/rendition/fields/type', ['views/fields/enum', 'dam:config'],
    (Dep, Config) => Dep.extend({
        damConfig: null,
        
        setup() {
            this.damConfig = Config.prototype.init.call(this);
            Dep.prototype.setup.call(this);
        },
        
        setupOptions() {
            if (!this.model.get("assetId")) {
                return;
            }
            this.wait(true);
            this.getModelFactory().create("Asset", model => {
                model.id = this.model.get("assetId");
                model.fetch().then(() => {
                    let type        = this.damConfig.getType(model.get("type"));
                    let enableTypes = this.damConfig.getByType(`${type}.renditions`);
                    
                    let params = [];
                    
                    for (let i in enableTypes) {
                        let item = enableTypes[i];
                        if (!item.auto) {
                            params.push(i);
                        }
                    }
                    
                    this.params.options = params;
                    if (!this.model.has("id")) {
                        this.model.set("type", params[0]);
                    }
                    this.wait(false);
                });
            });
        }
    })
);