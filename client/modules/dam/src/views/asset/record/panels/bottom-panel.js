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

Espo.define('dam:views/asset/record/panels/bottom-panel', 'treo-core:views/record/panels/relationship',
    Dep => Dep.extend({
        template: "dam:asset/record/panels/asset",
        blocks: [],
        link: null,
        sort: false,
        scope: null,

        data() {
            return {
                blocks: this.blocks
            };
        },

        setup() {
            this.link = this._getAssetLink();
            this.scope = this.options.defs.entityName;

            this.getGroupsInfo();
            if (this.link) {
                this.actionButtonList();
            }

            this.listenTo(this.model, 'after:relate after:unrelate', function () {
                this.actionRefresh();
            });
        },

        getGroupsInfo() {
            this.wait(true);
            this.blocks = [];
            let showFirst = true;

            this.getCollectionFactory().create("Asset", (collection) => {
                collection.url = `Asset/action/assetsNatures?entity=${this.model.name}&id=${this.model.id}`;
                collection.fetch().then(() => {
                    this.collection = collection;
                    this.collection.forEach((model) => {
                        if (model.get("hasItem")) {
                            this.blocks.push(model.get("name"));
                            this._createTypeBlock(model, showFirst);
                            showFirst = false;
                        }
                    });
                    this.wait(false);
                });
            });
        },

        actionButtonList() {

            this.buttonList.push({
                title: this.translate('clickToRefresh', 'messages', 'Global'),
                action: 'refresh',
                link: this.link,
                acl: 'read',
                aclScope: this.scope,
                html: '<span class="fas fa-sync"></span>'
            });

            this.actionList.unshift({
                label: 'Select',
                action: this.defs.selectAction || 'selectRelated',
                data: {
                    link: this.link
                },
                acl: 'edit',
                aclScope: this.model.name
            });

            this.actionList.unshift({
                label: this.translate('Mass Upload', 'labels', 'Asset'),
                action: 'massAssetCreate',
                data: {
                    link: this.link
                },
                acl: 'create',
                aclScope: this.model.name
            });

            this.buttonList.push({
                title: 'Create',
                action: this.defs.createAction || 'createRelated',
                link: this.link,
                acl: 'create',
                aclScope: this.scope,
                html: '<span class="fas fa-plus"></span>',
                data: {
                    link: this.link
                }
            });
        },

        actionMassAssetCreate: function (data) {
            const foreignLink = this.model.defs['links'][data.link].foreign;

            this.notify('Loading...');
            this.createView('massCreate', 'dam:views/asset/modals/edit', {
                scope: 'Asset',
                relate: {
                    model: this.model,
                    link: foreignLink,
                },
                attributes: {},
                fullFormDisabled: true,
                layoutName: 'massCreateDetailSmall'
            }, view => {
                view.render();
                view.notify(false);
                this.listenToOnce(view, 'after:save', function () {
                    this.actionRefresh();
                    this.model.trigger('after:relate');
                }, this);
            });
        },

        actionRefresh() {
            if (this.collection) {
                let Promises = [];

                this.collection.fetch().then(() => {
                    this.blocks = [];
                    this.collection.forEach(model => {
                        if (model.get("hasItem") && !this.hasView(model.get("name"))) {
                            Promises.push(new Promise(resolve => {
                                model.set({
                                    entityName: this.defs.entityName,
                                    entityId: this.model.id,
                                    entityModel: this.model
                                });

                                this.createView(model.get('name'), this._getInnerPanelView(), {
                                    model: model,
                                    el: this.options.el + ' .group[data-name="' + model.get("name") + '"]',
                                    sort: this.sort,
                                    show: false
                                }, view => {
                                    resolve();
                                });
                            }));
                        }
                        if (model.get("hasItem")) {
                            this.blocks.push(model.get("name"));
                        }
                    });

                    if (Promises.length > 0) {
                        Promise.all(Promises).then(r => {
                            this.reRender();
                        });
                    } else {
                        this.reRender();
                    }
                });
            }
        },

        actionUnlinkRelated: function (data) {
            const id = data.id;
            this.confirm({
                message: this.translate('unlinkRecordConfirmation', 'messages'),
                confirmText: this.translate('Unlink')
            }, function () {
                var model = this.collection.get(id);
                this.notify('Unlinking...');
                $.ajax({
                    url: `${this.model.name}/${this.model.id}/assets`,
                    type: 'DELETE',
                    data: JSON.stringify({
                        id: id
                    }),
                    contentType: 'application/json',
                    success: function () {
                        this.notify('Unlinked', 'success');
                        this.collection.fetch();
                        this.model.trigger('after:unrelate');
                    }.bind(this),
                    error: function () {
                        this.notify('Error occurred', 'error');
                    }.bind(this),
                });
            }, this);
        },

        _getAssetLink() {
            let links = this.model.defs.links;
            for (let key in links) {
                if (links[key].type === "hasMany" && links[key].entity === "Asset") {
                    this.sort = true;
                    return key;
                }
            }

            return false;
        },

        _getInnerPanelView() {
            return this.getMetadata().get(`clientDefs.${this.defs.entityName}.relationshipPanels.assets.innerPanelView`, 'dam:views/asset/record/panels/asset-type-block');
        },

        _createTypeBlock(model, show, callback) {
            model.set({
                entityName: this.defs.entityName,
                entityId: this.model.id,
                entityModel: this.model
            });

            this.createView(model.get('name'), this._getInnerPanelView(), {
                model: model,
                el: this.options.el + ' .group[data-name="' + model.get("name") + '"]',
                sort: this.sort,
                show: show
            }, view => {
                if (typeof callback === "function") {
                    callback(view);
                }
            });
        }
    })
);