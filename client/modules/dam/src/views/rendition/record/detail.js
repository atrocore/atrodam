

Espo.define('dam:views/rendition/record/detail', 'views/record/detail',
    Dep => Dep.extend({
        duplicateAction: false,
        setup() {
            Dep.prototype.setup.call(this);
            
            this.listenTo(this.model, "sync", () => {
                this.setReturnUrl();
            });
            
            this.setReturnUrl();
        },
    
        setReturnUrl () {
            if (!this.model.get("assetId")) {
                return ;
            }
            
            this.returnUrl = `Asset/view/${this.model.get("assetId")}`
        }
    })
);