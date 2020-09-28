

Espo.define('dam:views/asset_relation/record/panels/bottom-panel', 'treo-core:views/record/panels/relationship',
    Dep => Dep.extend({
        template: "dam:asset_relation/record/panels/asset-relations",
        blocks  : [],
        link    : null,
        sort    : false,
        scope   : null,
        
        selectRelatedFilters    : {},
        selectPrimaryFilterNames: {},
        selectBoolFilterLists   : [],
        
        data() {
            return {
                blocks: this.blocks
            };
        },
        
        setup() {
            this.link  = this._getAssetLink();
            this.scope = this.options.defs.entityName;
            
            this.getGroupsInfo();
            if (this.link) {
                this.actionButtonList();
            }
        },
        
        getGroupsInfo() {
            this.wait(true);
            let items     = this.getMetadata().get("entityDefs.Asset.fields.type.options");
            this.blocks   = [];
            let showFirst = true;
            
            this.getCollectionFactory().create("AssetRelation", (collection) => {
                collection.url = `AssetRelation/${this.model.name}/${this.model.id}/itemsInEntity?list=${items.join(",")}`;
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
                title   : this.translate('clickToRefresh', 'messages', 'Global'),
                action  : 'refresh',
                link    : this.link,
                acl     : 'read',
                aclScope: this.scope,
                html    : '<span class="fas fa-sync"></span>'
            });
            
            this.actionList.unshift({
                label   : 'Select',
                action  : this.defs.selectAction || 'selectRelation',
                acl     : 'edit',
                aclScope: this.model.name
            });
            
            this.buttonList.push({
                title   : 'Create',
                action  : this.defs.createAction || 'createRelation',
                link    : this.link,
                acl     : 'create',
                aclScope: this.scope,
                html    : '<span class="fas fa-plus"></span>',
                data    : {
                    link: this.link
                }
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
                                    entityName : this.defs.entityName,
                                    entityId   : this.model.id,
                                    entityModel: this.model
                                });
    
                                this.createView(model.get('name'), "dam:views/asset_relation/record/panels/asset-type-block", {
                                    model: model,
                                    el   : this.options.el + ' .group[data-name="' + model.get("name") + '"]',
                                    sort : this.sort,
                                    show : false
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
        
        actionCreateRelation() {
            this.createView("createAssetRelation", "dam:views/asset_relation/modals/create-assets", {
                relate: {
                    model: this.model,
                    link : this.model.defs['links'][this.link].foreign
                },
                scope : this.scope
            }, (view) => {
                view.render();
                
                view.listenTo(view, "after:save", () => {
                    this.actionRefresh();
                    this._refreshAssetPanel();
                });
            });
        },
        
        actionSelectRelation(data) {
            if (!this.model.defs['links'][this.link]) {
                throw new Error('Link ' + this.link + ' does not exist.');
            }
            
            var scope   = this.model.defs['links'][this.link].entity;
            var foreign = this.model.defs['links'][this.link].foreign;
            
            var massRelateEnabled = false;
            if (foreign) {
                var foreignType = this.getMetadata().get('entityDefs.' + scope + '.links.' + foreign + '.type');
                if (foreignType == 'hasMany') {
                    massRelateEnabled = true;
                }
            }
            
            var self       = this;
            var attributes = {};
            
            var filters = Espo.Utils.cloneDeep(this.selectRelatedFilters[this.link]) || {};
            for (var filterName in filters) {
                if (typeof filters[filterName] == 'function') {
                    var filtersData = filters[filterName].call(this);
                    if (filtersData) {
                        filters[filterName] = filtersData;
                    } else {
                        delete filters[filterName];
                    }
                }
            }
            
            var primaryFilterName = data.primaryFilterName || this.selectPrimaryFilterNames[this.link] || null;
            if (typeof primaryFilterName == 'function') {
                primaryFilterName = primaryFilterName.call(this);
            }
            
            var dataBoolFilterList = data.boolFilterList;
            if (typeof data.boolFilterList == 'string') {
                dataBoolFilterList = data.boolFilterList.split(',');
            }
            
            var boolFilterList = dataBoolFilterList || Espo.Utils.cloneDeep(this.selectBoolFilterLists[this.link] || []);
            
            if (typeof boolFilterList == 'function') {
                boolFilterList = boolFilterList.call(this);
            }
            
            var viewName = this.getMetadata().get('clientDefs.' + scope + '.modalViews.select') || 'views/modals/select-records';
            
            this.notify('Loading...');
            this.createView('dialog', viewName, {
                scope            : scope,
                multiple         : true,
                createButton     : false,
                filters          : filters,
                massRelateEnabled: massRelateEnabled,
                primaryFilterName: primaryFilterName,
                boolFilterList   : boolFilterList
            }, function (dialog) {
                dialog.render();
                this.notify(false);
                dialog.once('select', function (selectObj) {
                    var data = {};
                    if (Object.prototype.toString.call(selectObj) === '[object Array]') {
                        var ids        = [];
                        var assetTypes = {};
                        
                        selectObj.forEach(function (model) {
                            ids.push(model.id);
                            assetTypes[model.id] = model.get("type");
                        });
                        data.ids = ids;
                    } else {
                        if (selectObj.massRelate) {
                            data.massRelate = true;
                            data.where      = selectObj.where;
                        } else {
                            data.id = selectObj.id;
                        }
                    }
                    
                    $.ajax({
                        url    : self.scope + '/' + self.model.id + '/' + this.link,
                        type   : 'POST',
                        data   : JSON.stringify(data),
                        success: function () {
                            this._updateAssetRelations(data, assetTypes);
                        }.bind(this),
                        error  : function () {
                            this.notify('Error occurred', 'error');
                        }.bind(this)
                    });
                }.bind(this));
            }.bind(this));
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
        
        _updateAssetRelations(assetIds, assetTypes) {
            this.getCollectionFactory().create("AssetRelation", collection => {
                collection.url = `AssetRelation/byEntity/${this.scope}/${this.model.id}?assetIds=${assetIds.ids.join(',')}`;
                collection.fetch().then(() => {
                    this.createView("EntityAssetList", "dam:views/asset_relation/modals/entity-asset-list", {
                        collection: collection,
                        assetTypes: assetTypes
                    }, view => {
                        view.render();
                        
                        view.listenTo(view, "after:save", () => {
                            this.actionRefresh();
                        });
                    });
                }).fail();
            });
        },
        _refreshAssetPanel() {
            let parent    = this.getParentView();
            let panelName = this._getAssetPanelName();
            
            const panelView = parent.getView(panelName);
            if (panelView && panelView.getView("list")) {
                panelView.getView("list").collection.fetch();
            }
        },
        
        _getAssetPanelName() {
            let links = this.getMetadata().get(`entityDefs.${this.scope}.links`);
            for (let key in links) {
                if (links[key].entity === "Asset") {
                    return key;
                }
            }
            return false;
        },
        _createTypeBlock(model, show, callback) {
            model.set({
                entityName : this.defs.entityName,
                entityId   : this.model.id,
                entityModel: this.model
            });
            
            this.createView(model.get('name'), "dam:views/asset_relation/record/panels/asset-type-block", {
                model: model,
                el   : this.options.el + ' .group[data-name="' + model.get("name") + '"]',
                sort : this.sort,
                show : show
            }, view => {
                if (typeof callback === "function") {
                    callback(view);
                }
            });
        }
    })
);