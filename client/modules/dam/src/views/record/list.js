

Espo.define('dam:views/record/list', 'views/record/list',
    Dep => Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            (this.getMetadata().get(['clientDefs', this.scope, 'disabledMassActions']) || []).forEach(item => this.removeMassAction(item));
        },
    
        actionQuickRemove: function (data) {
            data = data || {}
            var id = data.id;
            if (!id) return;
        
            var model = this.collection.get(id);
            if (!this.getAcl().checkModel(model, 'delete')) {
                this.notify('Access denied', 'error');
                return false;
            }
        
            this.confirm({
                message: this.translate('removeRecordConfirmation', 'messages'),
                confirmText: this.translate('Remove')
            }, function () {
                this.collection.trigger('model-removing', id);
                this.collection.remove(model);
                this.notify('Removing...');
                model.destroy({
                    wait: true,
                    success: function () {
                        this.notify('Removed', 'success');
                        this.trigger("after:model-remove");
                    }.bind(this),
                    error: function () {
                        this.notify('Error occured', 'error');
                        this.collection.push(model);
                    }.bind(this)
                });
            }, this);
        },
    })
);

