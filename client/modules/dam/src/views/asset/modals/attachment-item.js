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

Espo.define('dam:views/asset/modals/attachment-item', ['view', "dam:config"],
    (Dep, Config) => {
        return Dep.extend({
            template: "dam:asset/modals/attachment-item",
            type: null,
            damConfig: null,

            events: {
                'click span[data-action="collapsePanel"]': function (e) {
                    let obj = $(e.currentTarget);
                    obj.toggleClass("fa-chevron-up").toggleClass("fa-chevron-down");
                    obj.parents(".media-body").find('.edit-form').slideToggle();
                },
                'click span[data-action="deleteAttachment"]': function (e) {
                    this.model.destroy({
                        wait: true,
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

                let type = this.options.type || null;

                this.type = this.damConfig.getType(type);

                this.getModelFactory().create("Asset", assetModel => {
                    assetModel.set("type", type);
                    assetModel.set("private", true);
                    assetModel.set(`fileId`, this.model.id);
                    assetModel.set(`fileName`, this.model.get("name"));
                    assetModel.set(`albumId`, this.getMetadata().get('entityDefs.Asset.fields.album.defaultAttributes.albumId'));
                    assetModel.set(`albumName`, this.getMetadata().get('entityDefs.Asset.fields.album.defaultAttributes.albumName'));
                    assetModel.set("name", this._getFileName(this.model.get("name")));

                    assetModel.trigger("change:name");

                    this.model.set("assetModel", assetModel);
                    this.createView("assetEdit", "dam:views/asset/modals/asset-form", {
                        model: assetModel,
                        el: this.options.el + " .asset-edit-form"
                    });
                });
            },

            _getFileName(name) {
                name = name.split('.');
                name.pop();
                return name.join('.');
            },

            validate() {
                let notValid = false;
                for (let key in this.nestedViews) {
                    const view = this.nestedViews[key];
                    if (view && typeof view.validate === 'function') {
                        notValid = view.validate() || notValid;
                    }
                }
                return notValid;
            },

            _showPreview() {
                let config = this.damConfig.getByType(this.type);
                return config.nature === "image" || config.preview;
            }
        });
    });