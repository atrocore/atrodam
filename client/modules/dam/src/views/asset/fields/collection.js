

Espo.define('dam:views/asset/fields/collection', 'views/fields/link',
    Dep => Dep.extend({
        setup() {
            Dep.prototype.setup.call(this);
            
            if (this.model.isNew()) {
                this._setDefaultCollection();
            }
        },
        
        _setDefaultCollection() {
            this.wait(true);
            this.getModelFactory().create("Collection", collectionEntity => {
                let url   = "Collection?select=name";
                let where = [];
                
                where.push({
                    type : 'bool',
                    value: ['default']
                });
                collectionEntity.url = (
                    url + '&' + $.param({'where': where})
                );
                
                collectionEntity.fetch().then(() => {
                    if (collectionEntity.get("total") > 0) {
                        let model = collectionEntity.get("list")[0];
                        this.model.set("collectionName", model.name);
                        this.model.set("collectionId", model.id);
                    }
                    this.wait(false);
                });
            });
        }
    })
);