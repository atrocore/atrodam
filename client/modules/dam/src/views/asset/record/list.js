

Espo.define('dam:views/asset/record/list', 'views/record/list',
    Dep => Dep.extend({
        massActionList              : ['remove', 'massUpdate'],
        checkAllResultMassActionList: ['remove', 'massUpdate'],
        
        actionQuickEdit: function (data) {
            
            data   = data || {};
            var id = data.id;
            if (!id) return;
            
            var model = null;
            if (this.collection) {
                model = this.collection.get(id);
            }
            if (!data.scope && !model) {
                return;
            }
            
            var scope = data.scope || model.name || this.scope;
            
            var viewName = this.getMetadata().get('clientDefs.' + scope + '.modalViews.quickEdit') || this.getMetadata().get('clientDefs.' + scope + '.modalViews.edit') || 'views/modals/edit';
            
            if (!this.quickEditDisabled) {
                Espo.Ui.notify(this.translate('loading', 'messages'));
                var options = {
                    scope               : scope,
                    id                  : id,
                    model               : model,
                    fullFormDisabled    : data.noFullForm,
                    returnUrl           : this.getRouter().getCurrentUrl(),
                    returnDispatchParams: {
                        controller: scope,
                        action    : null,
                        options   : {
                            isReturn: true
                        }
                    }
                };
                if (this.options.keepCurrentRootUrl) {
                    options.rootUrl = this.getRouter().getCurrentUrl();
                }
                this.createView('modal', viewName, options, function (view) {
                    view.once('after:render', function () {
                        Espo.Ui.notify(false);
                    });
                    
                    view.render();
                    
                    this.listenToOnce(view, 'remove', function () {
                        this.clearView('modal');
                    }, this);
                    
                    this.listenToOnce(view, 'after:save', function (m) {
                        var model = this.collection.get(m.id);
                        if (model) {
                            model.set(m.getClonedAttributes());
                        }
                        
                        this.trigger('after:save', m);
                    }, this);
                }, this);
            } else {
                var options = {
                    id                  : id,
                    model               : this.collection.get(id),
                    returnUrl           : this.getRouter().getCurrentUrl(),
                    returnDispatchParams: {
                        controller: scope,
                        action    : null,
                        options   : {
                            isReturn: true
                        }
                    }
                };
                if (this.options.keepCurrentRootUrl) {
                    options.rootUrl = this.getRouter().getCurrentUrl();
                }
                this.getRouter().navigate('#' + scope + '/edit/' + id, {trigger: false});
                this.getRouter().dispatch(scope, 'edit', options);
            }
        }
    })
);