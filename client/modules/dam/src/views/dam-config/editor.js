

Espo.define('dam:views/dam-config/editor', ['view', "lib!codemirror"],
    (Dep) => Dep.extend({
        template   : "dam:dam-config/editor",
        editor     : null,
        configModel: null,
        
        setup() {
            Dep.prototype.setup.call(this);
        },
        
        afterRender() {
            this._initCodemirror();
        },
        
        _setConfig() {
            this.editor.getDoc().setValue(this.configModel.get("content"));
        },
        
        rollback() {
            this.configModel.fetch();
        },
        
        save() {
            this.configModel.save();
        },
        
        _initCodemirror() {
            
            Promise.all([
                new Promise((r) => {
                    Espo.loader.load('lib!codemirror.addons.search', function () {
                        r();
                    }.bind(this));
                }),
                new Promise((r) => {
                    Espo.loader.load('lib!codemirros.plugins.fold.indent-fold', function () {
                        r();
                    }.bind(this));
                }),
                new Promise((r) => {
                    Espo.loader.load('lib!coddemiror.plugins.yaml', function () {
                        r();
                    }.bind(this));
                }),
                new Promise((r) => {
                    Espo.loader.load('lib!codemirros.plugins.fold.foldcode', function () {
                        r();
                    }.bind(this));
                }),
                new Promise((r) => {
                    Espo.loader.load('lib!codemirros.plugins.fold.foldgutter', function () {
                        r();
                    }.bind(this));
                })
            ]).then(r => {
                
                this.editor = CodeMirror.fromTextArea(document.getElementById("text"), {
                    lineNumbers   : !0,
                    autofocus     : !0,
                    foldGutter    : {
                        rangeFinder: CodeMirror.fold.indent
                    },
                    gutters       : ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                    extraKeys     : {
                        "Ctrl-Q": function (e) {
                            e.foldCode(e.getCursor(), {
                                rangeFinder: CodeMirror.fold.indent,
                                minFoldSize: 3
                            });
                        },
                        Tab     : function (e) {
                            e.somethingSelected() ? e.indentSelection("add") : e.replaceSelection(e.getOption("indentWithTabs") ? "\t" : Array(e.getOption("indentUnit") + 1).join(" "), "end", "+input");
                        },
                        "Ctrl-H": "replaceAll"
                    },
                    tabSize       : 4,
                    indentUnit    : 4,
                    indentWithTabs: !1
                });
                
                this.getModelFactory().create("Config", (configModel) => {
                    this.configModel = configModel;
                    configModel.url  = "DamConfig/yaml";
                    configModel.id   = "notNull";
                });
                
                this.listenTo(this.configModel, "sync", () => {
                    this._setConfig();
                });
                
                this.configModel.fetch();
                
                this.editor.on("change", () => {
                    this.configModel.set("content", this.editor.getDoc().getValue());
                });
            });
        }
    })
);