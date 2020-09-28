

Espo.define('dam:views/asset_category/fields/category-parent', 'treo-core:views/fields/filtered-link',
    Dep => Dep.extend({

        selectBoolFilterList:  ['onlyActive', 'notEntity', 'notChildCategory', 'notAttachment'],
    
        boolFilterData: {
            notEntity() {
                return [this.model.id, this.model.get('categoryParentId')] || this.model.get('ids') || [];
            },
            notChildCategory() {
                return this.model.id;
            }
        },

    })
);
