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

Espo.define('dam:views/asset/modals/edit', 'views/modals/edit',
    Dep => Dep.extend({

        fullFormDisabled: true,

        setup() {
            Dep.prototype.setup.call(this);

            if (this.options.relate) {
                let button = {
                    name: '',
                    label: '',
                    style: 'link'
                };

                if (this.options.layoutName === 'massCreateDetailSmall') {
                    button.name = 'simpleUpload';
                    button.label = this.translate('simpleUpload', 'labels', 'Asset');
                } else {
                    button.name = 'massUpload';
                    button.label = this.translate('massUpload', 'labels', 'Asset');
                }

                this.addButton(button);
            }

            this.listenTo(this, 'after:save', model => {
                if (this.getParentModel()) {
                    this.getParentModel().trigger('asset:saved');
                }
            });
        },

        getParentModel() {
            if (this.getParentView() && this.getParentView().model) {
                return this.getParentView().model;
            }

            return this.getParentView().getParentView().model;
        },

        actionSimpleUpload() {
            this.actionClose();
            this.actionQuickCreate('detailSmall');
        },

        actionMassUpload() {
            this.actionClose();
            this.actionQuickCreate('massCreateDetailSmall');
        },

        actionQuickCreate(layout) {
            let options = _.extend({
                model: this.model,
                scope: this.scope,
                attributes: {}
            }, this.options || {});
            options.layoutName = layout;

            this.notify('Loading...');
            let viewName = this.getMetadata().get('clientDefs.' + this.scope + '.modalViews.edit') || 'views/modals/edit';

            this.createView('quickCreate', viewName, options, function (view) {
                view.render();
                view.notify(false);
                this.listenToOnce(view, 'after:save', function () {
                    $('button[data-action="refresh"][data-panel="assets"]').click();
                }, this);
            }.bind(this));
        },

        actionSave() {
            this.notify('Saving...');

            if (this.getView('edit').validate()) {
                this.notify('Not valid', 'error');
                this.trigger('cancel:save');
                return;
            }

            let filesIds = [];
            if (this.model.get('fileId')) {
                filesIds.push(this.model.get('fileId'));
            } else if (this.model.get('filesIds') && this.model.get('filesIds').length > 0) {
                filesIds = this.model.get('filesIds');
            }

            let count = filesIds.length;

            let editView = this.getView('edit');
            let formData = editView.fetch();

            let attrs = formData;
            if (!this.model.isNew()) {
                let initialAttributes = editView.attributes;
                for (let name in formData) {
                    if (_.isEqual(initialAttributes[name], formData[name])) {
                        continue;
                    }
                    (attrs || (attrs = {}))[name] = formData[name];
                }
            }
            attrs['name'] = this.model.get('name');
            attrs['_silentMode'] = true;

            if (this.options.relate && this.options.relate.model) {
                this.model.defs['_relationName'] = this.options.relate.model.defs['_relationName'];
            }

            let hashParts = window.location.hash.split('/view/');
            if (typeof hashParts[1] !== 'undefined' && this.model.defs._relationName) {
                attrs._relationName = this.model.defs._relationName;
                attrs._relationEntity = hashParts[0].replace('#', '');
                attrs._relationEntityId = hashParts[1];
            }

            if (attrs.type && attrs.type.length === 0) {
                delete attrs.type;
            }

            let self = this;
            if (count > 0) {
                this.model.save(attrs, {
                    patch: !this.model.isNew(),
                    success(response) {
                        new Promise(resolve => self.relateExistedAssets(resolve)).then(() => {
                            self.trigger('after:save', self.model);
                            self.dialog.close();
                            if (count > 20) {
                                Espo.Ui.notify(self.translate('assetsAdded', 'messages', 'Asset'), 'success', 1000 * 60, true);
                            } else {
                                self.notify('Saved', 'success');
                            }
                        });
                    },
                    error(e, xhr) {
                        if (xhr.status === 304) {
                            Espo.Ui.notify(self.translate('notModified', 'messages'), 'warning', 1000 * 60 * 60 * 2, true);
                        } else {
                            let statusReason = xhr.responseText || '';
                            Espo.Ui.notify(`${self.translate("Error")} ${xhr.status}: ${statusReason}`, "error", 1000 * 60 * 60 * 2, true);
                        }
                    }
                });
            } else if (this.options.relate) {
                new Promise(resolve => {
                    this.relateExistedAssets(resolve);
                }).then(() => {
                    this.trigger('after:save', this.model);
                    this.dialog.close();
                    this.notify('Linked', 'success');
                });
            } else {
                this.dialog.close();
                this.notify(false);
            }
        },

        relateExistedAssets(resolve) {
            if (this.options.relate && this.model.get('assetsForRelate')) {
                let ids = [];
                $.each(this.model.get('assetsForRelate'), (hash, id) => {
                    ids.push(id);
                });

                if (ids.length > 0) {
                    this.ajaxPostRequest(`${this.options.relate.model.urlRoot}/${this.options.relate.model.get('id')}/assets`, {"ids": ids}).then(success => {
                        resolve();
                    });
                } else {
                    resolve();
                }
            } else {
                resolve();
            }
        },

        afterRender() {
            Dep.prototype.afterRender.call(this);

            let buttonLink = $('.main-btn-group > .btn-link');

            if (buttonLink) {
                let prev = buttonLink.prev('.btn');

                if (prev && !prev.hasClass('last')) {
                    prev.addClass('last');
                }
            }
        }
    })
);