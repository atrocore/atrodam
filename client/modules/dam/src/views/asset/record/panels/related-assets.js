

Espo.define('dam:views/asset/record/panels/related-assets', 'views/record/panels/relationship',
    Dep => Dep.extend({
        
        boolFilterData: {
            notEntity  : function () {
                return this.model.id;
            },
            notSelected: function () {
                let listView = this.getView("list");
                let ids      = [];
                
                listView.collection.forEach((item) => {
                    ids.push(item.get("id"));
                });
                
                return ids;
            }
        },
        
        setup() {
            this.defs.createAction = "createMulti";
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
        
        actionCreateMulti: function (data) {
            data = data || {};
            
            var link        = data.link;
            var scope       = this.model.defs['links'][link].entity;
            var foreignLink = this.model.defs['links'][link].foreign;
            
            var attributes = {};
            
            this.notify('Loading...');
            
            var viewName = this.getMetadata().get('clientDefs.' + scope + '.modalViews.edit') || "dam:views/asset/modals/multi-create";
            this.createView('quickCreate', viewName, {
                scope     : scope,
                relate    : {
                    model: this.model,
                    link : foreignLink
                },
                attributes: attributes
            }, function (view) {
                view.render();
                view.notify(false);
                this.listenToOnce(view, 'after:save', function () {
                    this.updateRelationshipPanel();
                }, this);
            }.bind(this));
        },
        
        updateRelationshipPanel: function () {
            this.notify('Success');
            this.collection.fetch();
        }
    })
);
