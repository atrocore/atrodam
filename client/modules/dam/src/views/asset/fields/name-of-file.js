

Espo.define('dam:views/asset/fields/name-of-file', 'dam:views/asset/fields/name',
    Dep => Dep.extend({
        detailTemplate: "dam:fields/name-of-file/detail",
        data() {
            return _.extend({
                attachmentId: this.model.get("fileId")
            }, Dep.prototype.data.call(this));
        },
        updateName() {
            this.model.set("nameOfFile", this._getFileName());
        }
    })
);