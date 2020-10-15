

/*
 *  This file is part of AtroDAM.
 *
 *  AtroDAM - Open Source DAM application.
 *  Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 *  Website: https://atrodam.com
 *
 *  AtroDAM is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  AtroDAM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with AtroDAM. If not, see http://www.gnu.org/licenses/.
 *
 *  The interactive user interfaces in modified source and object code versions
 *  of this program must display Appropriate Legal Notices, as required under
 *  Section 5 of the GNU General Public License version 3.
 *
 *  In accordance with Section 7(b) of the GNU General Public License version 3,
 *  these Appropriate Legal Notices must retain the display of the "AtroDAM" word.
 */

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