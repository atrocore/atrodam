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

Espo.define('dam:views/asset/list', ['dam:views/list', 'search-manager'],
    (Dep, SearchManager) => Dep.extend({

        template: 'dam:asset/list',

        createButton: false,

        setup() {
            Dep.prototype.setup.call(this);

            if (this.getAcl().check('Catalog', 'read') && this.getAcl().check('Category', 'read')) {
                this.setupCatalogTreePanel();
            }

            this.menu.buttons.push({
                link: '#' + this.scope + '/create',
                action: 'create',
                label: 'Create ' +  this.scope,
                style: 'primary',
                acl: 'create',
                cssStyle: "margin-left: 15px",
                aclScope: this.entityType || this.scope
            });

            (this.menu.dropdown || []).unshift({
                acl: 'create',
                aclScope: 'Asset',
                action: 'massAssetCreate',
                label: this.translate('massUpload', 'labels', 'Asset'),
                iconHtml: ''
            });

            this.getStorage().set('list-view', 'Asset', 'list');
        },

        actionMassAssetCreate() {
            this.notify('Loading...');
            this.createView('massCreate', 'dam:views/asset/modals/edit', {
                scope: 'Asset',
                attributes: this.getCreateAttributes() || {},
                fullFormDisabled: true,
                layoutName: 'massCreateDetailSmall'
            }, view => {
                view.notify(false);
                this.listenToOnce(view, 'after:save', () => {
                    this.collection.fetch();
                    view.close();
                });
                view.listenTo(view.model, 'updating-started', () => view.disableButton('save'));
                view.listenTo(view.model, 'updating-ended', () => view.enableButton('save'));
                view.render();
            });
        },

        setupCatalogTreePanel() {
            this.createView('catalogTreePanel', 'dam:views/asset/record/catalog-tree-panel', {
                el: '#main > .catalog-tree-panel',
                scope: this.scope
            }, view => {
                view.render();
                view.listenTo(view, 'select-category', data => {
                    this.updateCollectionWithCatalogTree(data);
                    this.collection.fetch();
                });
            });
        },

        updateCollectionWithCatalogTree(data) {
            let defaultFilters = this.searchManager.get();
            let advanced = _.extend(Espo.Utils.cloneDeep(defaultFilters.advanced), data.advanced);
            let bool = _.extend(Espo.Utils.cloneDeep(defaultFilters.bool), data.bool);
            this.searchManager.set(_.extend(Espo.Utils.cloneDeep(defaultFilters), {advanced: advanced, bool: bool}));
            this.collection.where = this.searchManager.getWhere();
            let boolPart = this.collection.where.find(item => item.type === 'bool');
            if (boolPart) {
                let boolPartData = {};
                boolPart.value.forEach(elem => {
                    if (elem in data.boolData) {
                        boolPartData[elem] = data.boolData[elem];
                    }
                });
                boolPart.data = boolPartData;
            }
            this.searchManager.set(defaultFilters);
        },

        data() {
            return {
                isCatalogTreePanel: this.getAcl().check('Catalog', 'read') && this.getAcl().check('Category', 'read')
            }
        },

        setupSearchManager() {
            let collection = this.collection;

            var searchManager = new SearchManager(collection, 'list', this.getStorage(), this.getDateTime(), this.getSearchDefaultData());
            searchManager.scope = this.scope;

            if (this.options.params.showFullListFilter) {
                searchManager.set(_.extend(searchManager.get(), {advanced: Espo.Utils.cloneDeep(this.options.params.advanced)}));
            }

            if ((this.options.params || {}).boolFilterList) {
                searchManager.set({
                    textFilter: '',
                    advanced: {},
                    primary: null,
                    bool: (this.options.params || {}).boolFilterList.reduce((acc, curr) => {
                        acc[curr] = true;
                        return acc;
                    }, {})
                });
            } else {
                searchManager.loadStored();
            }

            collection.where = searchManager.getWhere();
            this.searchManager = searchManager;
        },

        resetSorting() {
            Dep.prototype.resetSorting.call(this);

            let catalogTreePanel = this.getView('catalogTreePanel');
            if (catalogTreePanel) {
                catalogTreePanel.trigger('resetFilters');
            }
        }

    })
);

