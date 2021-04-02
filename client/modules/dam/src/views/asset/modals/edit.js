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
            const isNew = typeof this.model.id === 'undefined';

            if (this.model.get('filesIds') && this.model.get('filesIds').length > 0) {
                this.model.save().then(response => {
                    new Promise(resolve => {
                        this.relateExistedAssets(resolve);
                    }).then(() => {
                        this.trigger('after:save', this.model);
                        this.dialog.close();

                        if (response.afterSaveMessage) {
                            Espo.Ui.notify(response.afterSaveMessage, 'success', 1000 * 60 * 60, true);
                        } else if (isNew) {
                            this.notify('Created', 'success');
                        } else {
                            this.notify('Saved', 'success');
                        }
                    });
                });
            } else {
                new Promise(resolve => {
                    this.relateExistedAssets(resolve);
                }).then(() => {
                    this.trigger('after:save', this.model);
                    this.dialog.close();
                    this.notify('Saved', 'success');
                });
            }
        },

        relateExistedAssets(resolve) {
            if (this.model.get('assetsForRelate')) {
                let ids = [];
                $.each(this.model.get('assetsForRelate'), (hash, id) => {
                    ids.push(id);
                });

                this.ajaxPostRequest(`${this.options.relate.scope}/${this.options.relate.model.get('id')}/assets`, {"ids": ids}).then(success => {
                    resolve();
                });
            } else {
                resolve();
            }
        },
    })
);