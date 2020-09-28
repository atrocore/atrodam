

Espo.define('dam:views/asset_relation/record/panels/header', 'view',
    Dep => Dep.extend({

        template: "dam:asset_relation/record/panels/header",
        show: false,

        setup() {
            Dep.prototype.setup.call(this);
            this.show = this.options.show || false;
        },

        data() {
            return {
                name: this.model.get("name"),
                hasItems: this.model.get("hasItem"),
                show: this.show
            }
        },

        showPanel() {
            this.show = !this.show;
            this.reRender();
            if (this.show) {
                this.getParentView().showInfo();
            } else {
                this.getParentView().hideInfo();
            }

        },
        events: {
            'click .show-view': function (e) {
                e.stopPropagation();
                e.preventDefault();

                this.showPanel();
            }
        },
    })
);