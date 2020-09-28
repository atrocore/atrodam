

Espo.define('dam:views/fields/varchar', 'views/fields/varchar', function (Dep) {
    return Dep.extend({
        editTemplate: "dam:fields/varchar/edit",
        data() {
            return _.extend({
                readOnly: this.readOnly
            }, Dep.prototype.data.call(this));
        }
    });
});