

Espo.define('dam:views/asset_relation/modals/create-assets', 'dam:views/modals/multi-create',
    Dep => Dep.extend({
        entityAssetModels: {},
        
        _renderAttachmentList() {
            this.createView("attachmentList", "dam:views/asset_relation/modals/attachment-list", {
                el        : this.options.el + " .attachment-list",
                collection: this.collection,
                model     : this.model,
                entityName: this.scope
            }, view => {
                view.render();
            });
        },
        
        actionSave() {
            if (this.validate()) {
                this.notify('Not valid', 'error');
                return;
            }
            let Promises = [];
            this.collection.forEach(model => {
                let assetModel       = model.get("assetModel");
                let entityAssetModel = assetModel.get("EntityAsset");
                assetModel.unset("EntityAsset");
                
                assetModel.setRelate(this.options.relate);
                
                Promises.push(new Promise((resolve, rejected) => {
                    assetModel.save().then(() => {
                        let entityId         = this.getParentView().model.id;
                        entityAssetModel.url = `AssetRelation/update/by?entityName=${this.scope}&entityId=${entityId}&assetId=${assetModel.id}`;
                        entityAssetModel.save().then(() => {
                            resolve();
                        }).fail((data) => {
                            rejected();
                        });
                    }).fail(() => {
                        assetModel.set("EntityAsset", entityAssetModel);
                        rejected();
                    });
                }));
            });
            Promise.all(Promises).then(r => {
                this._afterSave();
                this.saved = true;
                this.dialog.close();
            }).catch(r => {
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
            return notValid
        }
    })
);