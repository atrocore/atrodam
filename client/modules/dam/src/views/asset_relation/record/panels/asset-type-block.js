

Espo.define('dam:views/asset_relation/record/panels/asset-type-block', 'view',
    Dep => Dep.extend({
        template: "dam:asset_relation/record/panels/asset-type-block",
        sort    : false,
        show    : true,
        
        setup() {
            Dep.prototype.setup.call(this);
            this.sort = this.options.sort || false;
            this.createHeaderBlock();
            
            this.listenTo(this, "after:render", () => {
                this.showInfo();
            });
        },
        
        createHeaderBlock() {
            this.createView("headerBlock", "dam:views/asset_relation/record/panels/header", {
                model: this.model,
                el   : this.options.el + " .group-name",
                show : this.show
            });
        },
        
        showInfo() {
            this.show = true;
            if (this.collection) {
                this.collection.fetch().then(() => {
                    this.getView("list").reRender();
                });
            } else {
                this.getCollectionFactory().create("AssetRelation", (collection) => {
                    collection.url = `AssetRelation/byEntity/${this.model.get('entityName')}/${this.model.get('entityId')}?type=${this.model.get('name')}`;
                    collection.sortBy = "";
                    this.collection = collection;
                    this.waitForView("list");
                    this.createView('list', "dam:views/asset_relation/record/list", {
                        collection          : this.collection,
                        model               : this.model,
                        buttonsDisabled     : true,
                        checkboxes          : false,
                        el                  : this.options.el + ' .list-container',
                        layoutName          : "listSmall",
                        dragableListRows    : this.sort,
                        listRowsOrderSaveUrl: `AssetRelation/${this.model.get('entityName')}/${this.model.get('entityId')}/sortOrder`,
                        listLayout          : null,
                        skipBuildRows       : true,
                        rowActionsView      : this.model.get('rowActionsView') ? this.model.get('rowActionsView') : this.rowActionsView,
                    }, function (view) {
                        view.listenTo(collection, "sync", () => {
                            $(view.el).find('.list').slideDown("fast");
                            this.model.get('entityModel').fetch();
                        });
                        view.listenTo(view, "after:model-remove", () => {
                            let parent = this.getParentView();
                            parent.actionRefresh();
                            parent._refreshAssetPanel();
                            this.model.get('entityModel').fetch();
                        });
                        if (this.getMetadata().get(['scopes', this.model.get('entityName'), 'advancedFilters'])) {
                            view.listenTo(view, 'after:render', () => {
                                this.model.trigger("advanced-filters");
                            });
                        }
                        collection.fetch();
                    });
                });
            }
        },
        
        hideInfo() {
            this.show = false;
            if (this.hasView("list")) {
                let view = this.getView("list");
                $(view.el).find(".list").slideUp("fast");
            }
        }
    })
);