/*
 *  This file is part of AtroDAM.
 *
 *  AtroDAM - Open Source DAM application.
 *  Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 *  Website: https://atrodam.com
 *
 *  AtroDAM is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  AtroDAM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with AtroDAM. If not, see http://www.gnu.org/licenses/.
 *
 *  The interactive user interfaces in modified source and object code versions
 *  of this program must display Appropriate Legal Notices, as required under
 *  Section 5 of the GNU General Public License version 3.
 *
 *  In accordance with Section 7(b) of the GNU General Public License version 3,
 *  these Appropriate Legal Notices must retain the display of the "AtroDAM" word.
 */

Espo.define('dam:views/asset/fields/files', 'views/fields/attachment-multiple',
    Dep => Dep.extend({

        editTemplate: 'dam:asset/fields/files/edit',

        showPreviews: false,

        data() {
            return _.extend({}, Dep.prototype.data.call(this), {
                isUploading: this.isUploading,
                percentCompleted: this.getPercentCompleted()
            });
        },

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, "change:type", () => this.empty());
        },

        getPercentCompleted() {
            return Math.round((100 / this.totalCount) * this.uploadedCount);
        },

        uploadFiles: function (files) {
            this.uploadedCount = 0;
            this.totalCount = 0;

            var exceedsMaxFileSize = false;

            var maxFileSize = this.params.maxFileSize || 0;
            var appMaxUploadSize = this.getHelper().getAppParam('maxUploadSize') || 0;
            if (!maxFileSize || maxFileSize > appMaxUploadSize) {
                maxFileSize = appMaxUploadSize;
            }

            if (maxFileSize) {
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    if (file.size > maxFileSize * 1024 * 1024) {
                        exceedsMaxFileSize = true;
                    }
                }
            }
            if (exceedsMaxFileSize) {
                var msg = this.translate('fieldMaxFileSizeError', 'messages')
                    .replace('{field}', this.getLabelText())
                    .replace('{max}', maxFileSize);

                this.showValidationMessage(msg, 'label');
                return;
            }

            this.isUploading = true;
            this.model.trigger('updating-started');

            this.getModelFactory().create('Attachment', function (model) {
                var canceledList = [];

                var fileList = [];
                for (var i = 0; i < files.length; i++) {
                    fileList.push(files[i]);
                    this.totalCount++;
                }

                fileList.forEach(function (file) {
                    var $attachmentBox = this.addAttachmentBox(file.name, file.type);

                    $attachmentBox.find('.remove-attachment').on('click.uploading', function () {
                        canceledList.push(attachment.cid);
                        this.totalCount--;
                        if (this.uploadedCount == this.totalCount) {
                            this.isUploading = false;
                            if (this.totalCount) {
                                this.afterAttachmentsUploaded.call(this);
                            }
                        }
                    }.bind(this));

                    var attachment = model.clone();

                    var fileReader = new FileReader();
                    fileReader.onload = function (e) {
                        attachment.set('name', file.name);
                        attachment.set('type', file.type || 'text/plain');
                        attachment.set('role', 'Attachment');
                        attachment.set('size', file.size);
                        attachment.set('parentType', 'Asset');
                        attachment.set('file', e.target.result);
                        attachment.set('field', this.name);
                        attachment.set('modelAttributes', this.model.attributes);

                        attachment.save({}, {timeout: 0}).then(function () {
                            if (canceledList.indexOf(attachment.cid) === -1) {
                                $attachmentBox.trigger('ready');
                                this.pushAttachment(attachment);
                                $attachmentBox.attr('data-id', attachment.id);
                                this.uploadedCount++;
                                if (this.uploadedCount == this.totalCount && this.isUploading) {
                                    this.isUploading = false;
                                    this.afterAttachmentsUploaded.call(this);
                                }
                            }
                        }.bind(this)).fail(function () {
                            $attachmentBox.remove();
                            this.totalCount--;
                            if (!this.totalCount) {
                                this.isUploading = false;
                                this.$el.find('.uploading-message').remove();
                            }
                            if (this.uploadedCount == this.totalCount && this.isUploading) {
                                this.isUploading = false;
                                this.afterAttachmentsUploaded.call(this);
                            }
                        }.bind(this));
                    }.bind(this);
                    fileReader.readAsDataURL(file);
                }, this);
            }.bind(this));
        },

        afterAttachmentsUploaded() {
            this.reRender();
            this.model.trigger('updating-ended');
        }

    })
);