

Espo.define('dam:views/fields/image', 'dam:views/fields/file', function (Dep) {
    return Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.type = 'image';
            this.showPreview = true;
            this.accept = ['image/*'];
            this.defaultType = 'image/jpeg';
            this.previewSize = 'small'
        }

    });
});
