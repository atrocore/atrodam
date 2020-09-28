

Espo.define('dam:views/rendition/record/panels/side/download/main', 'view',
    Dep => Dep.extend({
        template: "dam:rendition/record/panels/side/download",
        data() {
            return _.extend({
                attachmentId: this._getAttachmentId()
            });
        },
        _getAttachmentId() {
            return this.model.get("fileId") || this.model.get("imageId")
        }
    })
);