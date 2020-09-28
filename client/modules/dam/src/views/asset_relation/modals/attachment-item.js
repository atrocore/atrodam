

Espo.define('dam:views/asset_relation/modals/attachment-item',
    ['view', "dam:views/fields/code-from-name", "dam:config"],
    (Dep, Code, Config) => {
        return Dep.extend({
            template : "dam:asset_relation/modals/attachment-item",
            type     : null,
            damConfig: null,
            
            events: {
                'click span[data-action="collapsePanel"]'     : function (e) {
                    let obj = $(e.currentTarget);
                    obj.toggleClass("fa-chevron-up").toggleClass("fa-chevron-down");
                    obj.parents(".media-body").find('.edit-form').slideToggle();
                },
                'click span[data-action="collapseAssetPanel"]': function (e) {
                    let obj = $(e.currentTarget);
                    obj.toggleClass("fa-chevron-up").toggleClass("fa-chevron-down");
                    obj.parents(".asset-edit-form").find('.detail').slideToggle();
                },
                'click span[data-action="deleteAttachment"]'  : function (e) {
                    this.model.destroy({
                        wait   : true,
                        success: () => {
                            this.notify('Removed', 'success');
                            this.remove();
                            this.trigger("attachment:remove");
                        }
                    });
                }
            },
            data() {
                let data = {
                    'name': this.model.get("name"),
                    'size': (
                        parseInt(this.model.get("size")) / 1024
                    ).toFixed(2) + " kb"
                };
                
                if (this._showPreview()) {
                    data.preview = `?entryPoint=preview&size=small&id=${this.model.id}&type=attachment`;
                }
                
                return data;
            },
            setup() {
                this.damConfig = Config.prototype.init.call(this);
                
                let type   = this.options.type || null;
                let access = this.options.private;
                
                this.type = this.damConfig.getType(type);
                
                this.getModelFactory().create("Asset", assetModel => {
                    
                    assetModel.set("type", type);
                    assetModel.set("private", access);
                    assetModel.set(`fileId`, this.model.id);
                    assetModel.set(`fileName`, this.model.get("name"));
                    assetModel.set("name", this._getFileName(this.model.get("name")));
                    assetModel.set("nameOfFile", this._getFileName(this.model.get("name")));
                    assetModel.set("code", Code.prototype.transformToPattern.call(this, this._getFileName(this.model.get("name"))));
                    
                    assetModel.trigger("change:name");
                    
                    this.model.set("assetModel", assetModel);
                    this.createView("assetEdit", "dam:views/asset_relation/modals/asset-form", {
                        model: assetModel,
                        el   : this.options.el + " .asset-edit-form"
                    });
                    
                    this.getModelFactory().create("AssetRelation", entityAssetModel => {
                        assetModel.set("EntityAsset", entityAssetModel);
                        entityAssetModel.set({
                            name      : `${assetModel.get("name")} / ${(
                                (
                                    this.model.get('size') / 1024
                                ).toFixed(1)
                            )}`,
                            entityName: this.options.entityName
                        });
                        this.createView("entityAssetEdit", "dam:views/asset_relation/modals/entity-asset-form", {
                            model: entityAssetModel,
                            el   : this.options.el + " .edit-form"
                        });
                        
                    });
                });
            },
            
            _getFileName(name) {
                name = name.split('.');
                name.pop();
                return name.join('.');
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