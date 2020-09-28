

Espo.define('dam:views/asset_category/list', 'dam:views/list', function (Dep) {
    return Dep.extend({

        afterRender() {
            this.collection.isFetched = false;
            this.clearView('list');
            Dep.prototype.afterRender.call(this);
        }

    });
});
