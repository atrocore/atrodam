

Espo.define('dam:views/asset/record/panels/bottom-panel', 'views/record/panels/relationship', Dep => {
    return Dep.extend({
        setup() {
            this.defs.createAction = "createMulti";
            Dep.prototype.setup.call(this);
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
    });
});