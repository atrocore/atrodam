

Espo.define('dam:views/asset_relation/modals/attachment-list', 'view',
    Dep => Dep.extend({
        template: "dam:asset_relation/modals/attachment-list",
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
                this.createView(name, "dam:views/asset_relation/modals/attachment-item", {
                    el     : this.options.el + ` tr[data-name="${name}"]`,
                    model  : attachment,
                    type   : this.model.get('type'),
                    private: this.model.get('private'),
                    entityName: this.options.entityName
                }, view => {
                    view.listenTo(view, "attachment:remove", () => {
                        this.reRender();
                    });
                });
            }
        },

        validate() {
            let notValid = false;
            for (let key in this.nestedViews) {
                const view = this.nestedViews[key];
                if (view && typeof view.validate === 'function') {
                    notValid = view.validate() || notValid;
                }
            }
            return notValid
        }
    })
);