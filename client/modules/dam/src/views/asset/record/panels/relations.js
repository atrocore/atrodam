

Espo.define('dam:views/asset/record/panels/relations', 'treo-core:views/record/panels/relationship',
    Dep => {
        return Dep.extend({
            template: "dam:asset/record/panels/relations",
            blocks  : [],
            
            data() {
                return {
                    blocks: this.blocks
                };
            },
            
            setup() {
                this.getGroupsInfo();
            },
            
            getGroupsInfo() {
                this.wait(true);
                let url       = `AssetRelation/EntityList/${this.model.id}`;
                this.blocks   = [];
                let showFirst = true;
                
                this.getCollectionFactory().create("AssetRelation", (collection) => {
                    collection.url = url;
                    collection.fetch().then(() => {
                        this.collection = collection;
                        this.collection.forEach((model) => {
                            model.set({
                                entityName: "Asset",
                                entityId  : this.model.id
                            });
                            
                            let params = {
                                model: model,
                                el   : this.options.el + ' .group[data-name="' + model.get("name") + '"]'
                            };
                            
                            this.blocks.push(model.get("name"));
                            this.createView(model.get('name'), "dam:views/asset/record/panels/entity-block", params);
                        });
                        this.wait(false);
                    });
                });
            }
        });
    }
);