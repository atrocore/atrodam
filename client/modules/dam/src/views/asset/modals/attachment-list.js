

Espo.define('dam:views/asset/modals/attachment-list', 'view', function (Dep) {
    return Dep.extend({
        template: "dam:asset/modals/attachment-list",
        items   : [],
        
        data() {
            return {
                items: this.items
            };
        },
        
        setup() {
            this.items = [];
            for (let i = 0; i < this.collection.length; i++) {
                let attachment = this.collection.models[i];
                
                let name = `attachment-${attachment.get('id')}`;
                this.items.push(name);
                this.createView(name, "dam:views/asset/modals/attachment-item", {
                    el     : this.options.el + ` tr[data-name="${name}"]`,
                    model  : attachment,
                    type   : this.model.get('type'),
                    private: this.model.get('private')
                }, view => {
                    view.listenTo(view, "attachment:remove", () => {
                        this.reRender();
                    });
                });
            }
        }
    });
});