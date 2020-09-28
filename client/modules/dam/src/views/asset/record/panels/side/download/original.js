

Espo.define('dam:views/asset/record/panels/side/download/original', 'view',
    Dep => {
        return Dep.extend({
            template: "dam:asset/record/panels/side/download/original",
            active  : true,
            
            setup() {
            
            },
            
            hide() {
                this.active = false;
            },
            
            show() {
                this.active = true;
            },
    
            buildUrl() {
                let attachmentId = this.model.get("fileId") || this.model.get("imageId");
                return `?entryPoint=download&showInline=false&id=${attachmentId}`
            }
        });
    }
);