

Espo.define('dam:views/asset/record/panels/rendition',
    ['treo-core:views/record/panels/relationship', "dam:config"],
    (Dep, Config) => {
        return Dep.extend({
            damConfig : null,
            
            setup() {
                this.damConfig = Config.prototype.init.call(this);
                this.defs.create = this._create();
                Dep.prototype.setup.call(this);
            },
            
            _create() {
                let type       = this._getType();
                let renditions = this.damConfig.getByType(`${type}.renditions`);
                
                return !!renditions;
            },
            _getType() {
                if (this.model.get("type")) {
                    return this.damConfig.getType(this.model.get('type'));
                } else {
                    this.model.listenToOnce(this.model, "sync", () => {
                        this.defs.create = this._create();
                        this.reRender();
                    });
                }
            }
        });
    }
);