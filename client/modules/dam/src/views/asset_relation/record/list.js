

Espo.define('dam:views/asset_relation/record/list', 'dam:views/record/list',
    Dep => Dep.extend({
        _getHeaderDefs() {
            
            for (var i in this.listLayout) {
                this.listLayout[i].notSortable = true;
            }
            
            return Dep.prototype._getHeaderDefs.call(this);
        }
    })
);