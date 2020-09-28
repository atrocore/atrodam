

Espo.define('dam:controllers/asset', 'controllers/record',
    Dep => {

        return Dep.extend({

            defaultAction: 'list',

            beforePlate() {
                this.handleCheckAccess('read');
            },

            plate() {
                this.getCollection(function (collection) {
                    this.main(this.getViewName('plate'), {
                        scope: this.name,
                        collection: collection
                    });
                });
            },
        });
    });
