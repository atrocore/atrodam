

Espo.define('dam:views/asset/record/plate-item', 'view',
    Dep => Dep.extend({

        template: 'dam:asset/record/plate-item',

        setup() {
            Dep.prototype.setup.call(this);

            if (this.options.rowActionsView) {
                this.waitForView('rowActions');
                this.createView('rowActions', this.options.rowActionsView, {
                    el: `${this.options.el} .actions`,
                    model: this.model,
                    acl: this.options.acl
                });
            }
        },

        data() {
            return {
                version: moment(this.model.get('modifiedAt')).format("X")
            };
        }

    })
);

