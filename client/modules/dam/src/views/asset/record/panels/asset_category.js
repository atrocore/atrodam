

Espo.define('dam:views/asset/record/panels/asset_category', 'views/record/panels/relationship',
    Dep => Dep.extend({
        
        boolFilterData: {
            notSelectCategories: function () {
                return this.model.id;
            },
            byCollection       : function () {
                return this.model.attributes.collectionId;
            }
        },
        
        setup() {
            this.defs.createAction = "createAssetCategory";
            Dep.prototype.setup.call(this);
            
            let select = this.actionList.find(item => item.action === (
                this.defs.selectAction || 'selectRelated'
            ));
            
            if (select) {
                select.data = {
                    link                  : this.link,
                    scope                 : this.scope,
                    boolFilterListCallback: 'getSelectBoolFilterList',
                    boolFilterDataCallback: 'getSelectBoolFilterData',
                    primaryFilterName     : this.defs.selectPrimaryFilterName || null
                };
            }
        },
        
        getSelectBoolFilterData(boolFilterList) {
            let data = {};
            if (Array.isArray(boolFilterList)) {
                boolFilterList.forEach(item => {
                    if (this.boolFilterData && typeof this.boolFilterData[item] === 'function') {
                        data[item] = this.boolFilterData[item].call(this);
                    }
                });
            }
            return data;
        },
        
        getSelectBoolFilterList() {
            return this.defs.selectBoolFilterList || null;
        },
        
        actionCreateAssetCategory: function (data) {
            data = data || {};
        
            var link        = data.link;
            var scope       = this.model.defs['links'][link].entity;
            var foreignLink = this.model.defs['links'][link].foreign;
            
            var attributes = {};
            
            this.notify('Loading...');
            
            this.createView('quickCreate', "dam:views/asset/record/panels/relations/asset-category/modals/create", {
                scope           : scope,
                fullFormDisabled: true,
                relate          : {
                    model: this.model,
                    link : foreignLink
                },
                attributes      : attributes,
                assetModel      : this.model
            }, function (view) {
                view.render();
                view.notify(false);
                this.listenToOnce(view, 'after:save', function () {
                    this.collection.fetch();
                    this.model.trigger('after:relate');
                }, this);
            }.bind(this));
            
            return false;
        }
        
    })
);
