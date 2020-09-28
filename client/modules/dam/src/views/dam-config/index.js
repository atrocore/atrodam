

Espo.define('dam:views/dam-config/index', 'view',
    Dep => Dep.extend({
        template: "dam:dam-config/index",
        
        events: {
            'click button[data-action="save"]': function (e) {
                this._saveConfig();
            },
            
            'click button[data-action="cancel"]': function (e) {
                this._rollbackConfig();
            }
        },
        
        _rollbackConfig() {
            this.getView("editor").rollback();
        },
        
        _saveConfig() {
            this.getView("editor").save();
        },
        
        setup() {
            Dep.prototype.setup.call(this);
            
            this.createEditorView();
        },
        
        createEditorView() {
            this.createView("editor", "dam:views/dam-config/editor", {
                "el": this.options.el + " > .import-container"
            });
        }
    })
);