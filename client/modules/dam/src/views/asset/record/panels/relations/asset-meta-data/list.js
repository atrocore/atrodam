

Espo.define('dam:views/asset/record/panels/relations/asset-meta-data/list', 'views/record/list',
    Dep => {
        return Dep.extend({
            rowActionsDisabled: true
        });
    }
);