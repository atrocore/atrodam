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

Espo.define('dam:views/asset/multi-upload', ["view", "dam:config", "lib!crypto"], function (Dep, Config) {
    return Dep.extend({
        template      : "dam:asset/multi-upload",
        size          : {},
        damConfig     : null,
        attachmentHash: [],
        
        events: _.extend({
            'change input[data-name="upload"]': function (e) {
                this._uploadFiles(e.currentTarget.files);
            }
        }, Dep.prototype.events),
        
        setup() {
            this.attachmentHash = [];
            
            this.damConfig = Config.prototype.init.call(this);
            Dep.prototype.setup.call(this);
        },
        
        _uploadFiles(files) {
            let maxUploadCount = this.getMetadata().get("app.fileStorage.maxUploadFiles");
            if (files.length > maxUploadCount) {
                this.notify(this.translate("File limit", "exceptions", "Asset"), "error");
                return false;
            }
            
            let pList = [];
            for (let i = 0; i < files.length; i++) {
                let result = this._createFile(files[i]);
                if (result !== false) {
                    pList.push(result);
                }
            }
            
            Promise.all(pList).then(r => {
                this.collection.trigger("upload:done", r);
            }).catch(r => {
                this.collection.trigger("upload:done", r);
            });
        },
        
        _sizeValidate(size) {
            let type       = this.damConfig.getType(this.model.get("type"));
            let private    = this.model.get('private') ? "private" : "public";
            let sizeParams = this.damConfig.getByType(`${type}.validations.size.${private}`);
            
            if (sizeParams && (
                size > sizeParams.max || size < sizeParams.min
            )) {
                return false;
            }
            
            return true;
        },
        
        _createFile(file) {
            let sizeValidate = this._sizeValidate((
                file.size / 1024
            ));
            
            if (!sizeValidate) {
                this.notify("Size limit", "error");
                return false;
            }
            
            return new Promise((resolve, reject) => {
                this.getModelFactory().create('Attachment', function (model) {
                    let fileReader    = new FileReader();
                    fileReader.onload = function (e) {
                        if (this._isDuplicate(e)) {
                            this.notify("Is Duplicate", "error");
                            resolve();
                        } else {
                            model.set('name', file.name);
                            model.set('type', file.type || 'text/plain');
                            model.set('role', 'Attachment');
                            model.set('size', file.size);
                            model.set('relatedType', "Asset");
                            model.set('file', e.target.result);
                            model.set('field', 'file');
                            model.set('modelAttributes', this.model);
                            model.save({}, {timeout: 0}).then(function () {
                                this.collection.push(model);
                                resolve();
                            }.bind(this)).fail(function () {
                                resolve();
                            }.bind(this));
                        }
                    }.bind(this);
                    fileReader.readAsDataURL(file);
                    
                }.bind(this));
            });
        },
        
        _isDuplicate(e) {
            let type = this.damConfig.getType(this.model.get("type"));
            
            if (this.damConfig.getByType(`${type}.validations.unique`) === null) {
                return false;
            }
            
            let hash = CryptoJS.MD5(e.currentTarget.result).toString();
            
            if (this.attachmentHash.find(i => hash === i)) {
                return true;
            }
            
            this.attachmentHash.push(hash);
            
            return false;
        }
    });
});