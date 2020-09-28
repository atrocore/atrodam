

Espo.define('dam:views/asset/modals/attachment-item', ['view', "dam:views/fields/code-from-name", "dam:config"],
    function (Dep, Code, Config) {
        return Dep.extend({
            template : "dam:asset/modals/attachment-item",
            type    : null,
            damConfig: null,
            
            events: {
                'click span[data-action="collapsePanel"]'   : function (e) {
                    let obj = $(e.currentTarget);
                    obj.toggleClass("fa-chevron-up").toggleClass("fa-chevron-down");
                    obj.parents(".media-body").find('.edit-form').slideToggle();
                },
                'click span[data-action="deleteAttachment"]': function (e) {
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
                
                this.type   = this.damConfig.getType(type);
                
                this.getModelFactory().create("Asset", model => {
                    
                    model.set("type", type);
                    model.set("private", access);
                    model.set(`fileId`, this.model.id);
                    model.set(`fileName`, this.model.get("name"));
                    model.set("name", this._getFileName(this.model.get("name")));
                    model.set("nameOfFile", this._getFileName(this.model.get("name")));
                    model.set("code", Code.prototype.transformToPattern.call(this, this._getFileName(this.model.get("name"))));
                    
                    model.trigger("change:name");
                    
                    this.model.set("assetModel", model);
                    this.createView("edit", "dam:views/asset/modals/asset-form", {
                        model: model,
                        el   : this.options.el + " .edit-form"
                    });
                });
            },
            
            _getFileName(name) {
                name = name.split('.');
                name.pop();
                return name.join('.');
            },
            
            _showPreview() {
                let config = this.damConfig.getByType(this.type);
                return config.nature === "image" || config.preview;
            }
        });
    });