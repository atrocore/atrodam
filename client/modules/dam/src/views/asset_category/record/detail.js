

Espo.define('dam:views/asset_category/record/detail', 'views/record/detail',
    Dep => Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this, 'after:save', () => this.model.fetch());
        }
    })
);

