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

Espo.define('dam:views/asset_relation/modals/create-assets', 'dam:views/modals/multi-create',
    Dep => Dep.extend({
        entityAssetModels: {},
        
        _renderAttachmentList() {
            this.createView("attachmentList", "dam:views/asset_relation/modals/attachment-list", {
                el        : this.options.el + " .attachment-list",
                collection: this.collection,
                model     : this.model,
                entityName: this.scope
            }, view => {
                view.render();
            });
        },
        
        actionSave() {
            if (this.validate()) {
                this.notify('Not valid', 'error');
                return;
            }
            let Promises = [];
            this.collection.forEach(model => {
                let assetModel       = model.get("assetModel");
                let entityAssetModel = assetModel.get("EntityAsset");
                assetModel.unset("EntityAsset");
                
                assetModel.setRelate(this.options.relate);
                
                Promises.push(new Promise((resolve, rejected) => {
                    assetModel.save().then(() => {
                        let entityId         = this.getParentView().model.id;
                        entityAssetModel.url = `AssetRelation/update/by?entityName=${this.scope}&entityId=${entityId}&assetId=${assetModel.id}`;
                        entityAssetModel.save().then(() => {
                            resolve();
                        }).fail((data) => {
                            rejected();
                        });
                    }).fail(() => {
                        assetModel.set("EntityAsset", entityAssetModel);
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

        validate() {
            let notValid = false;
            for (let key in this.nestedViews) {
                const view = this.nestedViews[key];
                if (view && typeof view.validate === 'function') {
                    notValid = view.validate() || notValid;
                }
            }
            return notValid
        }
    })
);