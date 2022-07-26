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
 *  This software is not allowed to be used in Russia and Belarus.
 */

Espo.define('dam:views/modals/multi-create', 'views/modal', function (Dep) {
    return Dep.extend({
        saved   : false,
        template: "dam:modals/multi-create",
        scope   : null,
        
        setup() {
            this.scope  = this.options.scope || null;
            this.header = this.getLanguage().translate("Create Assets", 'labels', this.scope);
            
            this.getCollectionFactory().create("Attachments", collection => {
                this.collection = collection;
                this.collection.listenTo(this.collection, "upload:done", () => {
                    if (this.collection.length > 0) {
                        this._afterUploadDone();
                        this._renderAttachmentList();
                    }
                });
            });
            
            this._renderInfoPanel();
            this._renderUpload();
            
            this.listenTo(this, "close", () => {
                if (this.saved) {
                    return true;
                }
                let count = this.collection.length;
                while (count > 0) {
                    this.collection.models[0].destroy();
                    count--;
                }
            });
            
            this.once("after:save", () => {
                this.trigger("after:save");
            });
        },
        
        _renderAttachmentList() {
            this.createView("attachmentList", "dam:views/asset/modals/attachment-list", {
                el        : this.options.el + " .attachment-list",
                collection: this.collection,
                model     : this.model
            }, view => {
                view.render();
            });
        },
        
        _renderUpload() {
            this.createView("upload", "dam:views/asset/multi-upload", {
                model     : this.model,
                collection: this.collection,
                el        : this.options.el + ' div[data-name="upload"]'
            });
        },
        
        _renderInfoPanel() {
            this.getModelFactory().create("CreateAssets", model => {
                this.model = model;
                this.createView("assetInfoPanel", "dam:views/asset/modals/info-panel", {
                    model: this.model,
                    el   : this.options.el + " .info-panel"
                });
            });
        },
        
        _afterUploadDone() {
            this.addButton({
                name : "save",
                label: "Save",
                style: 'primary'
            });
            
            this.addButton({
                name : "cancel",
                label: "Cancel"
            });
            this.getView("assetInfoPanel").setReadOnly();
        },
        
        actionSave() {
            let Promises = [];
            this.collection.forEach(model => {
                model.get("assetModel").setRelate(this.options.relate);
                
                Promises.push(new Promise((resolve, rejected) => {
                    model.get("assetModel").save().then(() => {
                        resolve();
                    }).fail(() => {
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
        
        _afterSave() {
            this.trigger("after:save");
        }
        
    });
});