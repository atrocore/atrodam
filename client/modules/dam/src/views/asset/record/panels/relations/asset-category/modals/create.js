

Espo.define('dam:views/asset/record/panels/relations/asset-category/modals/create', 'views/modals/edit',
    Dep => Dep.extend({
        actionSave: function () {
            let editModel  = this.getView("edit").model;
            let assetModel = this.options.assetModel;
            
            this._setCollection(editModel, assetModel);
            Dep.prototype.actionSave.call(this);
        },
        
        _setCollection(editModel, assetModel) {
            let collectionId   = assetModel.get("collectionId");
            let collectionName = {};
            
            collectionName[collectionId] = assetModel.get("collectionName");
            editModel.set("collectionsIds", [collectionId]);
            editModel.set("collectionsNames", collectionName);
        }
    })
);