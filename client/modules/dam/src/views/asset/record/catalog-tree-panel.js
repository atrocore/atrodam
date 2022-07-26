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
 *  This software is not allowed to be used in Russia and Belarus.
 */

Espo.define('dam:views/asset/record/catalog-tree-panel', 'views/record/panels/tree-panel',
    Dep => Dep.extend({

        template: 'dam:asset/record/catalog-tree-panel',

        catalogs: [],

        categories: [],

        rootCategoriesIds: [],

        events: {
            'click .category-buttons button[data-action="selectAll"]': function (e) {
                this.selectCategoryButtonApplyFilter($(e.currentTarget), {type: 'anyOf'});
            },
            'click .category-buttons button[data-action="selectWithoutCategory"]': function (e) {
                this.selectCategoryButtonApplyFilter($(e.currentTarget), {type: 'isEmpty'});
            },
            'click button[data-action="collapsePanel"]': function () {
                this.actionCollapsePanel();
            }
        },

        data() {
            return {
                scope: this.scope,
                catalogDataList: this.getCatalogDataList()
            }
        },

        setup() {
            this.scope = this.options.scope || this.scope;
            this.currentWidth = this.getStorage().get('panelWidth', this.scope) || this.minWidth;

            this.wait(true);

            this.getFullEntity('Library', {select: 'name,assetCategoriesIds,assetCategoriesNames'}, catalogs => {
                this.catalogs = catalogs;
                this.rootCategoriesIds = this.getRootCategoriesIds();
                this.getFullEntity('AssetCategory', {
                    select: 'name,categoryParentId',
                    where: [
                        {
                            type: 'or',
                            value: [
                                {
                                    type: 'in',
                                    attribute: 'id',
                                    value: this.rootCategoriesIds
                                },
                                {
                                    type: 'in',
                                    attribute: 'categoryParentId',
                                    value: this.rootCategoriesIds
                                }
                            ]
                        }
                    ]
                }, categories => {
                    this.categories = categories;
                    this.setupPanels();
                    this.wait(false);
                });
            });

            this.listenTo(this, 'resetFilters', () => {
                this.selectCategoryButtonApplyFilter(this.$el.find('button[data-action="selectAll"]'), false);
            });
        },

        getFullEntity(url, params, callback, container) {
            if (url) {
                container = container || [];

                let options = params || {};
                options.maxSize = options.maxSize || 200;
                options.offset = options.offset || 0;

                this.ajaxGetRequest(url, options).then(response => {
                    container = container.concat(response.list || []);
                    options.offset = container.length;
                    if (response.total > container.length || response.total === -1) {
                        this.getFullEntity(url, options, callback, container);
                    } else {
                        callback(container);
                    }
                });
            }
        },

        getRootCategoriesIds() {
            let categories = [];
            this.catalogs.forEach(catalog => {
                if (catalog.assetCategoriesIds) {
                    catalog.assetCategoriesIds.forEach(id => {
                        if (!categories.includes(id)) {
                            categories.push(id);
                        }
                    });
                }
            });

            return categories;
        },

        afterRender() {
            Dep.prototype.afterRender.call(this);

            if ($(window).width() <= 767 || !!this.getStorage().get('catalog-tree-panel', this.scope)) {
                this.actionCollapsePanel();
            }

            if (!this.getStorage().get('catalog-tree-panel', this.scope) && !this.model) {
                let pageHeader = this.$el.parents('#main').find('.page-header');
                pageHeader.addClass('collapsed');

                let listContainer = this.$el.parents('#main').find('.list-container');
                listContainer.addClass('collapsed');
            }
        },

        buildTree() {},

        selectCategoryButtonApplyFilter(button, filterParams) {
            this.selectCategoryButton(button);
            if ($(window).width() <= 767) {
                this.actionCollapsePanel(true);
            }
            if (filterParams) {
                this.applyCategoryFilter(filterParams.type);
            }
        },

        setupPanels() {
            this.createView('categorySearch', 'dam:views/asset/record/catalog-tree-panel/category-search', {
                el: '.catalog-tree-panel > .category-panel > .category-search',
                scope: this.scope,
                catalogs: this.catalogs
            }, view => {
                view.render();
                this.listenTo(view, 'category-search-select', category => {
                    this.selectCategory(category, true);
                });
            });

            this.catalogs.forEach(catalog => {
                if (catalog.assetCategoriesIds && catalog.assetCategoriesIds.length) {
                    this.createView(`category-tree-${catalog.id}`, 'dam:views/asset/record/catalog-tree-panel/category-tree', {
                        name: catalog.id,
                        el: `${this.options.el} > .category-panel > .category-tree > .panel[data-name="${catalog.id}"]`,
                        scope: this.scope,
                        catalog: catalog,
                        categories: this.categories
                    }, view => {
                        view.render();
                        view.listenTo(view, 'category-tree-select', category => {
                            this.selectCategory(category);
                        });
                    });
                }
            });
        },

        selectCategory(category, notSkipCollapse) {
            if (category && category.id && category.catalogId) {
                this.setCategoryActive(category.id, category.catalogId);
                if ($(window).width() <= 767) {
                    this.actionCollapsePanel();
                }
                if (notSkipCollapse) {
                    this.collapseCategory(category.id, category.catalogId);
                }
                this.applyCategoryFilter('anyOf', category);
            }
        },

        applyCategoryFilter(type, category) {
            let data = {};
            if (type === 'isEmpty') {
                data.advanced = {
                    assetCategories: {
                        type: 'isNotLinked',
                        data: {
                            type: type
                        }
                    }
                };
            } else if (type === 'anyOf' && category) {
                data.bool = {
                    linkedWithAssetCategory: true
                };
                data.boolData = {
                    linkedWithAssetCategory: category.id
                };
                data.advanced = {
                    collection: {
                        type: 'equals',
                        field: 'libraryId',
                        value: category.catalogId,
                        data: {
                            type: 'is',
                            idValue: category.catalogId,
                            nameValue: (this.catalogs.find(catalog => catalog.id === category.catalogId) || {}).name
                        }
                    }
                };
            }
            this.trigger('select-category', data);
        },

        collapseCategory(id, catalogId) {
            let activeCategory = this.$el.find(`.panel[data-name="${catalogId}"] li.child[data-id="${id}"]:eq()`);
            activeCategory.parents('.panel-collapse.collapse').collapse('show');
        },

        setCategoryActive(id, catalogId) {
            this.$el.find('.category-buttons > button').removeClass('active');
            this.$el.find('ul.list-group-tree li.child').removeClass('active');
            if (catalogId) {
                this.$el.find(`.panel[data-name="${catalogId}"] li.child[data-id="${id}"]:eq()`).addClass('active');
            } else {
                this.$el.find(`li.child[data-id="${id}"]:eq()`).addClass('active');
            }
        },

        selectCategoryButton(button) {
            this.$el.find('.panel-collapse.collapse[class^="catalog-"].in').collapse('hide');
            this.$el.find('ul.list-group-tree li.child').removeClass('active');
            this.$el.find('.category-buttons > button').removeClass('active');
            button.addClass('active');
        },

        actionCollapsePanel(forceHide) {
            let categoryPanel = this.$el.find('.category-panel');
            let button = this.$el.find('button[data-action="collapsePanel"]');
            let listContainer = this.$el.parent('#main').find('.list-container');
            let pageHeader = this.$el.parents('#main').find('.page-header');

            if (categoryPanel.hasClass('hidden') && !forceHide) {
                categoryPanel.removeClass('hidden');
                button.removeClass('collapsed');
                button.find('span.toggle-icon-left').removeClass('hidden');
                button.find('span.toggle-icon-right').addClass('hidden');
                this.$el.removeClass('catalog-tree-panel-hidden');
                this.$el.addClass('col-xs-12 col-lg-3');
                pageHeader.removeClass('not-collapsed');
                pageHeader.addClass('collapsed');
                listContainer.removeClass('hidden-catalog-tree-panel');
                listContainer.addClass('col-xs-12 col-lg-9 collapsed');
                this.getStorage().set('catalog-tree-panel', this.scope, '');
            } else {
                categoryPanel.addClass('hidden');
                button.addClass('collapsed');
                button.find('span.toggle-icon-left').addClass('hidden');
                button.find('span.toggle-icon-right').removeClass('hidden');
                this.$el.removeClass('col-xs-12 col-lg-3');
                this.$el.addClass('catalog-tree-panel-hidden');
                pageHeader.addClass('not-collapsed');
                pageHeader.removeClass('collapsed');
                listContainer.removeClass('col-xs-12 col-lg-9 collapsed');
                listContainer.addClass('hidden-catalog-tree-panel');
                this.getStorage().set('catalog-tree-panel', this.scope, 'collapsed');
            }
            $(window).trigger('resize');
        },

        getCatalogDataList: function () {
            let arr = [];
            this.catalogs.forEach(catalog => {
                if (catalog.assetCategoriesIds && catalog.assetCategoriesIds.length) {
                    arr.push({
                        key: `category-tree-${catalog.id}`,
                        name: catalog.id
                    });
                }
            });
            return arr;
        },
    })
);