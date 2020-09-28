

Espo.define("dam:views/asset_relation/fields/related-entity-name", "dam:views/fields/varchar", Dep =>
    Dep.extend({
        listTemplate: "dam:asset_relation/fields/related-entity-name/list",
        
        data() {
            let data       = {};
            let mainEntity = this.getParentView().getParentView().getParentView().model.get("entityName");
            
            if (mainEntity === "Asset") {
                data = {
                    entity: this.model.get("entityName"),
                    id    : this.model.get("entityId"),
                    name  : this.model.get("relatedEntityName")
                };
            } else {
                data = {
                    entity: "Asset",
                    id    : this.model.get("assetId"),
                    name  : this.model.get("relatedEntityName")
                };
            }
            
            return data;
        }
    })
);