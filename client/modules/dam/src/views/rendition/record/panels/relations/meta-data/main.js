

Espo.define("dam:views/rendition/record/panels/relations/meta-data/main", "views/record/panels/relationship", Dep =>
    Dep.extend({
        setup() {
            this.defs.recordListView = "dam:views/rendition/record/panels/relations/meta-data/list";
            Dep.prototype.setup.call(this);
        }
    })
);