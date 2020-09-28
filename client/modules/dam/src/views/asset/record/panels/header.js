

Espo.define('dam:views/asset/record/panels/header', 'view',
    Dep => {
        return Dep.extend({
            template: "dam:asset/record/panels/header",
            show    : false,
            
            setup() {
                Dep.prototype.setup.call(this);
                this.show = this.options.show || false;
            },
            
            data() {
                return {
                    name    : this.model.get("name"),
                    show    : this.show
                };
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
            }
            
        });
    }
);