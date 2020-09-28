

Espo.define('dam:views/asset/record/panels/side/preview/main', ['view', "dam:config"],
    (Dep, Config) => {
        return Dep.extend({
            template : "dam:asset/record/panels/side/preview/main",
            damConfig: null,
            
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
            },
            data() {
                return {
                    showImage: this._showImage(),
                    path     : this.options.el
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
                if (this.model.get("type")) {
                    let type = this.damConfig.getType(this.model.get("type"));
                    return this.damConfig.getByType(`${type}.preview`)
                        || this.damConfig.getByType(`${type}.nature`) === "image";
                    
                }
                return false;
            }
        });
    }
);