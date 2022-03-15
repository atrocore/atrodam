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
 *
 * This software is not allowed to be used in Russia and Belarus.
 */

Espo.define('dam:views/asset/record/panels/side/download/custom', 'view',
    Dep => {
        return Dep.extend({
            template       : "dam:asset/record/panels/side/download/custom",
            downloadModel  : {},
            active         : false,
            attachmentModel: null,
            
            events: {
                'change input': function (e) {
                    let $el  = $(e.currentTarget);
                    let name = $el.prop("name");
                    this.downloadModel.set(name, $el.val());
                }
            },
            
            data() {
                return {
                    downloadModel: this.downloadModel
                };
            },
            
            setup() {
                Dep.prototype.setup.call(this);
                
                this.wait(true);
                
                new Promise((resolve, rejected) => {
                    this.getModelFactory().create("Attachment", model => {
                        model.id = this.model.get("fileId");
                        model.fetch().then(() => {
                            this.attachmentModel = model;
                            resolve();
                        });
                    });
                }).then(r => {
                    this._createModel();
                    this._createForm();
                    
                    this.listenToOnce(this, "after:render", () => {
                        this._changeMode();
                        this._changeFormat();
                    });
                    
                    this.wait(false);
                });
            },
            
            _createForm() {
                this.createView("width", "dam:views/fields/varchar", {
                    el    : `${this.options.el} .field[data-name="width"]`,
                    model : this.downloadModel,
                    name  : 'width',
                    mode  : 'edit',
                    params: {
                        trim    : true,
                        readOnly: false
                    }
                });
                
                this.createView("height", "dam:views/fields/varchar", {
                    el    : `${this.options.el} .field[data-name="height"]`,
                    model : this.downloadModel,
                    name  : 'height',
                    mode  : 'edit',
                    params: {
                        trim    : true,
                        readOnly: false
                    }
                });
                
                this.createView("mode", "views/fields/enum", {
                    model               : this.downloadModel,
                    el                  : `${this.options.el} .field[data-name="mode"]`,
                    defs                : {
                        name  : 'mode',
                        params: {
                            options          : ["byWidth", "byHeight", "resize"],
                            translatedOptions: {
                                "resize"  : "Resize",
                                "byWidth" : "Scale by width",
                                "byHeight": "Scale by height"
                            }
                        }
                    },
                    mode                : 'edit',
                    prohibitedEmptyValue: true
                });
                
                this.createView("format", "views/fields/enum", {
                    model: this.downloadModel,
                    el   : `${this.options.el} .field[data-name="format"]`,
                    defs : {
                        name  : "format",
                        params: {
                            options          : ["jpeg", "png"],
                            translatedOptions: {
                                "jpeg": "JPEG",
                                "png" : "PNG"
                            }
                        }
                    },
                    mode : "edit",
                    prohibitedEmptyValue : true
                });
                
                this.createView("quality", "dam:views/fields/varchar", {
                    el    : `${this.options.el} .field[data-name="quality"]`,
                    model : this.downloadModel,
                    name  : 'quality',
                    mode  : 'edit',
                    params: {
                        trim    : true,
                        readOnly: false
                    }
                });
                
            },
            
            hide() {
                this.active = false;
                this.$el.find(".additional-panel").hide();
            },
            
            show() {
                this.active = true;
                this.$el.find(".additional-panel").show();
            },
            
            buildUrl() {
                let attachmentId = this.model.get("fileId") || this.model.get("imageId");
                return `?entryPoint=download&id=${attachmentId}` + "&" +
                    `width=${this.downloadModel.get("width")}` + "&" +
                    `height=${this.downloadModel.get("height")}` + "&" +
                    `quality=${this.downloadModel.get("quality")}` + "&" +
                    `scale=${this.downloadModel.get("mode")}` + "&" +
                    `format=${this.downloadModel.get("format")}` + "&" +
                    `type=custom`;
                
            },
            
            _getFormat() {
                return this.attachmentModel.get("type") === "image/png" ? "png" : "jpeg";
            },
            
            _createModel() {
                this.getModelFactory().create("downloadModel", model => {
                    model.set("width", this.model.get("width"));
                    model.set("height", this.model.get("height"));
                    model.set("quality", 100);
                    model.set("mode", "byWidth");
                    model.set("format", this._getFormat());
                    
                    this.downloadModel = model;
                    model.listenTo(model, "change:quality", () => {
                        if (parseInt(model.get('quality')) > 100) {
                            model.set("quality", 100);
                        }
                        if (parseInt(model.get('quality')) <= 0) {
                            model.set("quality", 1);
                        }
                    });
                    
                    model.listenTo(model, "change:width", () => {
                        if (parseInt(model.get('width')) < 1) {
                            model.set("width", 1);
                        }
                    });
                    
                    model.listenTo(model, "change:height", () => {
                        if (parseInt(model.get('height')) < 1) {
                            model.set("height", 1);
                        }
                    });
                    
                    model.listenTo(model, "change:mode", () => {
                        this._changeMode();
                    });
                    
                    model.listenTo(model, "change:format", () => {
                        this._changeFormat();
                    });
                });
            },
            
            _changeMode() {
                let heightView = this.getView("height");
                let widthView  = this.getView("width");
                
                switch (this.downloadModel.get("mode")) {
                    case "byWidth" :
                        this._setScaleWidth(heightView, widthView);
                        break;
                    
                    case "byHeight" :
                        this._setScaleHeight(heightView, widthView);
                        break;
                    
                    case "resize" :
                        this._setScaleResize(heightView, widthView);
                        break;
                }
                
                heightView.reRender();
                widthView.reRender();
            },
            
            _setScaleWidth(heightView, widthView) {
                heightView.readOnly = true;
                widthView.readOnly  = false;
                
                this.downloadModel.set("height", "");
                if (!this.downloadModel.get("width")) {
                    this.downloadModel.set("width", this.model.get("width"));
                }
            },
            
            _setScaleHeight(heightView, widthView) {
                heightView.readOnly = false;
                widthView.readOnly  = true;
                
                this.downloadModel.set("width", "");
                if (!this.downloadModel.get("height")) {
                    this.downloadModel.set("height", this.model.get("height"));
                }
            },
            
            _setScaleResize(heightView, widthView) {
                heightView.readOnly = false;
                widthView.readOnly  = false;
                
                if (!this.downloadModel.get("width")) {
                    this.downloadModel.set("width", this.model.get("width"));
                }
                if (!this.downloadModel.get("height")) {
                    this.downloadModel.set("height", this.model.get("height"));
                }
            },
            
            _changeFormat() {
                let qualityView = this.getView("quality");
                if (this.downloadModel.get("format") === "png") {
                    this.downloadModel.set("quality", 100);
                    qualityView.setReadOnly();
                } else {
                    qualityView.setNotReadOnly();
                }
                
                qualityView.reRender();
            }
        });
    }
);