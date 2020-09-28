

Espo.define('dam:views/asset/record/panels/asset-relation-record-list', 'views/record/list',
    Dep => {
        return Dep.extend({
            filterListLayout: function (listLayout) {
                let list    = Dep.prototype.filterListLayout.call(this, listLayout);
                let newList = [];
                
                for (let i = 0; i < list.length; i++) {
                    if (list[i].name === "preview") {
                        continue;
                    }
                    newList.push(list[i]);
                }
                
                return newList;
            }
        });
    }
);