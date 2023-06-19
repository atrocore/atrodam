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

Espo.define('dam:views/record/panels/assets', 'views/record/panels/relationship',
    Dep => Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.actionList.unshift({
                label: this.translate('massUpload', 'labels', 'Asset'),
                action: 'massAssetCreate',
                data: {
                    link: this.link
                },
                acl: 'create',
                aclScope: 'Asset'
            });
        },

        actionMassAssetCreate(data) {
            const link = data.link;
            const foreignLink = this.model.defs['links'][link].foreign;

            this.model.defs['_relationName'] = link;

            this.notify('Loading...');
            this.createView('massCreate', 'dam:views/asset/modals/edit', {
                name: 'massCreate',
                scope: 'Asset',
                relate: {
                    model: this.model,
                    link: foreignLink,
                },
                attributes: {massCreate: true},
                fullFormDisabled: true,
                layoutName: 'detailSmall'
            }, view => {
                view.render();
                view.notify(false);
                this.listenToOnce(view, 'after:save', () => {
                    this.actionRefresh();
                    this.model.trigger('after:relate', this.link, this.defs);
                });
            });
        },

    })
);

