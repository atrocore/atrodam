

Espo.define('dam:views/rendition/record/panels/side/preview/detail', ["view", "dam:config"],
    (Dep, Config) => Dep.extend({
        template : "dam:rendition/record/panels/side/preview/detail",
        damConfig: null,
        assetType: null,
        
        events: {
            'click a[data-action="showImagePreview"]': function (e) {
                e.stopPropagation();
                e.preventDefault();
                let id = $(e.currentTarget).data('id');
                this.createView('preview', 'dam:views/modals/image-preview', {
                    id   : id,
                    model: this.model
                }, function (view) {
                    view.render();
                });
            }
        },
        
        setup() {
            this.damConfig = Config.prototype.init.call(this);
            Dep.prototype.setup.call(this);
            
            this.listenTo(this.model, "change:fileId", () => {
                this.reRender();
            });
            
            if (this.model.get("assetId")) {
                this._setAssetType();
            }
            
            this.listenTo(this.model, "sync", () => {
                this._setAssetType(assetModel => {
                    this.reRender();
                });
            });
        },
        
        _setAssetType(callback) {
            this.getModelFactory().create("Asset", model => {
                model.id = this.model.get("assetId");
                model.fetch().then(() => {
                    this.assetType = this.damConfig.getType(model.get("type"));
                    
                    if (typeof callback === "function") {
                        callback(model);
                    }
                });
            });
        },
        
        data() {
            // debugger;
            return {
                show: this._showImage(),
                path: this.options.el
            };
        },
        
        _showImage() {
            return !!(
                this._isImage() && this._hasImage()
            );
        },
        
        _hasImage() {
            return this.model.has("fileId") && this.model.get("fileId");
        },
        
        _isImage() {
            if (this.model.get("type") && this.assetType) {
                return this.damConfig.getByType(`${this.assetType}.renditions.${this.model.get("type")}.preview`)
                    || this.damConfig.getByType(`${this.assetType}.renditions.${this.model.get("type")}.nature`) === "image"
                    || false;
                
            }
            return false;
        }
    })
);