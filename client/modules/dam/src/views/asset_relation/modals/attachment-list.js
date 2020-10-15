

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

Espo.define('dam:views/asset_relation/modals/attachment-list', 'view',
    Dep => Dep.extend({
        template: "dam:asset_relation/modals/attachment-list",
        items   : [],

        data() {
            return {
                items: this.items
            };
        },

        setup() {
            this.items = [];
            for (let i = 0; i < this.collection.length; i++) {
                let attachment = this.collection.models[i];

                let name = `attachment-${attachment.get('id')}`;
                this.items.push(name);
                this.createView(name, "dam:views/asset_relation/modals/attachment-item", {
                    el     : this.options.el + ` tr[data-name="${name}"]`,
                    model  : attachment,
                    type   : this.model.get('type'),
                    private: this.model.get('private'),
                    entityName: this.options.entityName
                }, view => {
                    view.listenTo(view, "attachment:remove", () => {
                        this.reRender();
                    });
                });
            }
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