

Espo.define('dam:views/asset/record/panels/relations/asset-meta-data/main', 'views/record/panels/relationship',
    Dep => {
        return Dep.extend({
            setup() {
                this.defs.recordListView = "dam:views/asset/record/panels/relations/asset-meta-data/list";
                Dep.prototype.setup.call(this);
            }
        });
    }
);