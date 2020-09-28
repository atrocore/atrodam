

Espo.define('dam:views/asset/fields/file', 'dam:views/fields/file',
    Dep => Dep.extend({
        
        getDownloadUrl(id) {
            var url = this.getBasePath() + '?entryPoint=download&showInline=false&id=' + id;
            if (this.getUser().get('portalId')) {
                url += '&portalId=' + this.getUser().get('portalId');
            }
            return url;
        },
        
        setup() {
            Dep.prototype.setup.call(this);
            
            this.listenTo(this.model, "change:type", () => {
                this.deleteAttachment();
            });
        }
    })
);