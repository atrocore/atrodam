

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

Espo.define('dam:views/rendition/detail', 'dam:views/detail',
    Dep => Dep.extend({
        assetModel: null,
        
        setupHeader: function () {
            this.waitForView("header");
            if (this.model.has("assetId")) {
                this.createBreadcrumbs();
            }

            this.listenTo(this.model, 'sync', function (model) {
                this.waitForView("header");
                if (model.hasChanged("assetId")) {
                    this.createBreadcrumbs();
                }
                if (model.hasChanged('name')) {
                    this.getView('header').reRender();
                    this.updatePageTitle();
                }
            }, this);
        },

        createBreadcrumbs() {
            this.getModelFactory().create("Asset", (model) => {
                model.id = this.model.get("assetId");
                model.fetch().then(() => {
                    this.assetModel = model;
                    this.createView('header', this.headerView, {
                        model: this.model,
                        el: '#main > .header',
                        scope: this.scope
                    });
                });
            });
        },

        getHeader() {
            let name = Handlebars.Utils.escapeExpression(this.model.get('name'));
            let assetName = Handlebars.Utils.escapeExpression(this.assetModel.get('name'));

            if (name === '') {
                name = this.model.id;
            }

            return this.buildHeaderHtml([
                '<a href="#Asset">' + this.getLanguage().translate("Asset", 'scopeNamesPlural') + '</a>',
                '<a href="#Asset/view/' + this.assetModel.get('id') + '">' + assetName + '</a>',
                this.getLanguage().translate(this.scope, 'scopeNamesPlural'),
                name
            ]);
        }
    })
);