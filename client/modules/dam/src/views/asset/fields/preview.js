

Espo.define('dam:views/asset/fields/preview', 'view',
    Dep => Dep.extend({
        template: "dam:asset/fields/preview/list",
    
        events: {
            'click a[data-action="showImagePreview"]': function (e) {
                e.stopPropagation();
                e.preventDefault();
                let id = $(e.currentTarget).data('id');
                this.createView('preview', 'dam:views/asset/modals/image-preview', {
                    id   : id,
                    model: this.model,
                    type : "asset"
                }, function (view) {
                    view.render();
                });
            }
        },
        
        data() {
            return {
                "timestamp": this.getTimestamp()
            };
        },
        setup() {
            Dep.prototype.setup.call(this);
        },
        getTimestamp() {
            return (Math.random() * 10000000000).toFixed();
        }
    })
);