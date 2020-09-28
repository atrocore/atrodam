

Espo.define('dam:views/asset/plate', 'dam:views/asset/list',
    Dep => Dep.extend({

        name: 'plate',

        setup() {
            Dep.prototype.setup.call(this);

            this.collection.maxSize = 20;
        },

        getRecordViewName: function () {
            return this.getMetadata().get('clientDefs.' + this.scope + '.recordViews.plate') || 'views/asset/record/plate';
        }

    })
);

