

Espo.define('dam:views/asset_category/record/list', 'dam:views/record/list',
    Dep => Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this, 'after:save', () => {
                this.listenToOnce(this.collection, 'sync', () => this.reRender());
                this.collection.fetch();
            });
        }

    })
);

