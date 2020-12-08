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

Espo.define('dam:views/fields/file', 'views/fields/file', function (Dep) {
    return Dep.extend({
        uploadFile: function (file) {

            var isCanceled = false;

            var exceedsMaxFileSize = false;

            var maxFileSize = this.params.maxFileSize || 0;
            var appMaxUploadSize = this.getHelper().getAppParam('maxUploadSize') || 0;
            if (!maxFileSize || maxFileSize > appMaxUploadSize) {
                maxFileSize = appMaxUploadSize;
            }

            if (maxFileSize) {
                if (file.size > maxFileSize * 1024 * 1024) {
                    exceedsMaxFileSize = true;
                }
            }
            if (exceedsMaxFileSize) {
                var msg = this.translate('fieldMaxFileSizeError', 'messages')
                    .replace('{field}', this.getLabelText())
                    .replace('{max}', maxFileSize);
                this.showValidationMessage(msg, '.attachment-button label');
                return;
            }

            this.isUploading = true;

            this.getModelFactory().create('Attachment', function (attachment) {
                var $attachmentBox = this.addAttachmentBox(file.name, file.type);

                this.$el.find('.attachment-button').addClass('hidden');

                $attachmentBox.find('.remove-attachment').on('click.uploading', function () {
                    isCanceled = true;
                    this.$el.find('.attachment-button').removeClass('hidden');
                    this.isUploading = false;
                }.bind(this));

                var fileReader = new FileReader();
                fileReader.onload = function (e) {
                    this.handleFileUpload(file, e.target.result, function (result, fileParams) {
                        attachment.set('name', fileParams.name);
                        attachment.set('type', fileParams.type || 'text/plain');
                        attachment.set('size', fileParams.size);
                        attachment.set('role', 'Attachment');
                        attachment.set('relatedType', this.model.name);
                        attachment.set('file', result);
                        attachment.set('field', this.name);
                        attachment.set('modelAttributes', this.model.attributes);

                        attachment.save({}, {timeout: 0}).then(function () {
                            this.isUploading = false;
                            if (!isCanceled) {
                                $attachmentBox.trigger('ready');
                                this.setAttachment(attachment);
                            }
                        }.bind(this)).fail(function () {
                            $attachmentBox.remove();
                            this.$el.find('.uploading-message').remove();
                            this.$el.find('.attachment-button').removeClass('hidden');
                            this.isUploading = false;
                        }.bind(this));
                    }.bind(this));
                }.bind(this);
                fileReader.readAsDataURL(file);
            }, this);
        }
    });
});
