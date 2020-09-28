

Espo.define('dam:views/asset/modals/image-preview', 'dam:views/modals/image-preview',
    Dep => Dep.extend({
        getImageUrl() {
            return `${this.getBasePath()}?entryPoint=preview&size=original&id=${this.options.id}`;
        }
    })
);