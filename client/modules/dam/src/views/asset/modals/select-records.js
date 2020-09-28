

Espo.define('dam:views/asset/modals/select-records', "treo-core:views/modals/select-records", function (Dep) {
    return Dep.extend({

        boolFilterData: {
            onlyActive: function () {
                return true;
            }
        },

        loadSearch() {
            this.boolFilterList = this.boolFilterList.length > 0 ? this.boolFilterList : this.getMetadata().get('clientDefs.' + this.scope + '.selectDefaultFilters.boolFilterList');

            Dep.prototype.loadSearch.call(this);
        }
    });
});