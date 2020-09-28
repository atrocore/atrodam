
Espo.define('dam:controllers/dam-config', 'controllers/record', function (Dep) {

    return Dep.extend({

        defaultAction: 'index',

        index: function () {
            this.main('dam:views/dam-config/index', null);
        }

    });

});
