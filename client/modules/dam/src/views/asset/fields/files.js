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

Espo.define('dam:views/asset/fields/files', ['views/fields/attachment-multiple', 'views/fields/file'], function (Dep, File) {

    return Dep.extend({

        editTemplate: 'dam:asset/fields/files/edit',

        showPreviews: false,

        events: _.extend(Dep.prototype.events, {
                'click a.remove-attachment': function (e) {
                    $(e.currentTarget).parent().parent().remove();
                }
            },
        ),

        files: {},

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, "change:type", () => this.empty());
        },

        getPercentCompleted() {
            return Math.round((100 / this.totalCount) * this.uploadedCount);
        },

        getMaxUploadSize: function () {
            return File.prototype.getMaxUploadSize.call(this);
        },

        uploadFiles: function (files) {
            const maxFileSize = this.getMaxUploadSize();

            let fileList = [];
            for (let i = 0; i < files.length; i++) {
                fileList.push(files[i]);
            }

            this.uploadedCount = 0;
            this.totalCount = fileList.length;

            this.isUploading = true;

            this.files = {};

            this.model.trigger('updating-started');

            this.createAttachments(fileList);
        },

        createAttachments: function (files) {
            if (files.length === 0) {
                return;
            }

            let file = files.shift();

            let $attachmentBox = this.addAttachmentBox(file.name, file.type);

            $attachmentBox.find('.remove-attachment').on('click.uploading', function () {
                this.totalCount--;
                if (this.uploadedCount === this.totalCount) {
                    this.isUploading = false;
                    this.afterAttachmentsUploaded.call(this);
                }
            }.bind(this));

            let fileReader = new FileReader();
            fileReader.onload = function (e) {
                $.ajax({
                    type: 'POST',
                    url: 'Attachment',
                    contentType: "application/json",
                    data: JSON.stringify({
                        name: file.name,
                        type: file.type || 'text/plain',
                        size: file.size,
                        parentType: 'Asset',
                        role: 'Attachment',
                        file: e.target.result,
                        field: this.name,
                        modelAttributes: this.model.attributes
                    }),
                }).done(function (attachment) {
                    $attachmentBox.trigger('ready');
                    $attachmentBox.attr('data-id', attachment.id);

                    this.files[attachment.id] = attachment.name;

                    this.uploadedCount++;

                    if (this.isUploading) {
                        if (this.uploadedCount === this.totalCount) {
                            this.isUploading = false;
                            this.afterAttachmentsUploaded.call(this);
                        } else {
                            this.afterAttachmentsUploaded.call(this);
                            this.createAttachments(files);
                        }
                    }
                }.bind(this)).error(function (data) {
                    $attachmentBox.remove();
                    this.totalCount--;
                    if (!this.totalCount) {
                        this.isUploading = false;
                        this.$el.find('.uploading-message').remove();
                    }

                    if (this.uploadedCount === this.totalCount) {
                        this.isUploading = false;
                        this.afterAttachmentsUploaded.call(this);
                    }
                }.bind(this));
            }.bind(this);
            fileReader.readAsDataURL(file);
        },

        afterAttachmentsUploaded() {
            let $progress = $('.attachment-upload .progress');

            let percentCompleted = 0;
            if (this.isUploading) {
                $progress.show();
                percentCompleted = this.getPercentCompleted();
            } else {
                $progress.hide();

                let filesIds = [];
                $.each(this.files, function (fileId, fileName) {
                    filesIds.push(fileId);
                });

                this.model.set('name', 'massUpload', {silent: true});
                this.model.set('filesIds', filesIds, {silent: true});
                this.model.set('filesNames', this.files, {silent: true});

                this.model.trigger('updating-ended');
            }

            $progress.find('.progress-bar').css('width', percentCompleted + '%');
            $progress.find('.progress-bar').html(percentCompleted + '% ' + this.translate('uploaded', 'labels', 'Asset'));
        },

    })
});