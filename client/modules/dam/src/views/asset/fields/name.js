

Espo.define('dam:views/asset/fields/name', 'views/fields/varchar',
    Dep => Dep.extend({
        fileName: null,
        
        setup() {
            Dep.prototype.setup.call(this);
            this.fileName = this._getFileName();
            
            this.registerListeners();
        },
        
        registerListeners() {
            this.listenTo(this.model, "change:fileId", () => {
                this.updateName();
            });
            
            this.listenTo(this.model, "change:imageId", () => {
                this.updateName();
            });
        },
        
        updateName() {
            if (this._isGeneratedName()) {
                this.model.set("name", this._normalizeName(this._getFileName()));
                this.fileName = this._getFileName();
            }
        },
        
        _getFileName() {
            let name = this.model.get("fileName") || this.model.get("imageName");
        
            if (!name) {
                return '';
            }
            
            name = name.split('.');
            name.pop();
            return name.join('.');
        },
        
        _normalizeName(name) {
            return name.replace(/[_-]+/gm, " ");
        },
        
        _isGeneratedName() {
            if (!this.model.get("name")) {
                return true;
            }
            
            return this.model.get("name") === this._normalizeName(this.fileName);
        }
    })
);