

Espo.define('dam:views/asset/list', ['dam:views/list', 'search-manager'],
    (Dep, SearchManager) => Dep.extend({

        template: 'dam:asset/list',

        setup() {
            Dep.prototype.setup.call(this);

            if (this.getAcl().check('Catalog', 'read') && this.getAcl().check('Category', 'read')) {
                this.setupCatalogTreePanel();
            }
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

