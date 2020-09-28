

Espo.define('dam:views/asset/modals/info-panel', 'view', function (Dep) {
    return Dep.extend({
        template: "dam:asset/modals/info-panel",
        setup() {
            this._createTypeDropBox();
            this._createPrivateCheckBox();
        },
        events  : {
            'change .field[data-name="type"] > select'  : function (e) {
                this.model.set("type", $(e.currentTarget).val());
            },
            'change .field[data-name="private"] > input': function (e) {
                this.model.set("private", $(e.currentTarget).prop("checked") ? true : false);
            }
        },
        
        _createTypeDropBox() {
            let data = this.getMetadata().get("entityDefs.Asset.fields.type.options");
            this.model.set("type", data[0]);
            
            this.createView("type", "views/fields/enum", {
                model: this.model,
                el   : this.options.el + ' .field[data-name="type"]',
                defs : {
                    name  : 'type',
                    params: {
                        options: this.getMetadata().get("entityDefs.Asset.fields.type.options")
                    }
                },
                mode : 'edit'
            });
        },
        _createPrivateCheckBox() {
            this.model.set("private", this.getMetadata().get("entityDefs.Asset.fields.private.default"));
            this.createView("private", "views/fields/bool", {
                model: this.model,
                el   : this.options.el + ' .field[data-name="private"]',
                defs : {
                    name: 'private'
                },
                mode : 'edit'
            });
        },
    
        setReadOnly () {
            this.getView("type").setReadOnly();
            this.getView("private").setReadOnly();
        }
    });
});