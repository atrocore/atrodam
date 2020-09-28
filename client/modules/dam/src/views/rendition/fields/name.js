

Espo.define("dam:views/rendition/fields/name", ["views/fields/varchar"], Dep =>
    Dep.extend({
        fileNameFromFile: null,
        
        setup() {
            Dep.prototype.setup.call(this);
            
            this.listenTo(this.model, "change:fileName", () => {
                if (this.model.get("name") && !this._isGeneratedName()) {
                    return;
                }
                
                this.model.set("name", this._setNameFromFile());
            });
        },
        
        _setNameFromFile() {
            let fileName = this._getFileName();
            
            this.fileNameFromFile = this._normalizeFileName(fileName);
            
            return this.fileNameFromFile;
        },
        
        _getFileName() {
            let fileName = this.model.get("fileName");
            
            if (!fileName) {
                return "";
            }
            
            fileName = fileName.split('.');
            fileName.pop();
            return fileName.join('.');
        },
        
        _normalizeFileName(fileName) {
            return fileName.replace(/[_-]+/gm, " ");
        },
    
        _isGeneratedName () {
            return this.model.get("name") === this.fileNameFromFile
        }
    })
);