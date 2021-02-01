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
                    var $div = $(e.currentTarget).parent();
                    let id = $div.attr('data-id');
                    if (id) {
                        $.ajax({
                            type: 'DELETE',
                            url: `Attachment/${id}?silent=true`,
                            contentType: "application/json"
                        })
                    }
                    $div.parent().remove();

                    this.attachmentBoxes[$div.data('hash')] = null;
                }
            },
        ),

        files: {},

        attachmentBoxes: {},

        failedCount: 0,

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

        addFileBox: function (file) {
            let $attachments = this.$attachments;
            let removeLink = '<a href="javascript:" class="remove-attachment pull-right"><span class="fas fa-times"></span></a>';
            let $att = $('<div>')
                .attr('data-hash', file.size + '_hash_' + file.name)
                .addClass('gray-box')
                .append(removeLink)
                .append($('<span class="preview">' + file.name + '</span>').css('width', 'cacl(100% - 30px)'));

            let $container = $('<div>').append($att);
            $attachments.append($container);

            let $loading = $('<span class="small uploading-message">' + this.translate('Uploading...') + '</span>');
            $container.append($loading);

            return $att;
        },

        uploadFiles: function (files) {
            const maxFileSize = this.getMaxUploadSize();

            let fileList = [];
            for (let i = 0; i < files.length; i++) {
                fileList.push(files[i]);
            }

            this.uploadedCount = 0;
            this.totalCount = fileList.length;

            this.files = {};
            this.failedCount = 0;

            this.model.trigger('updating-started');

            this.attachmentBoxes = {};
            fileList.forEach(function (file) {
                let $attachmentBox = this.addFileBox(file);
                this.attachmentBoxes[$attachmentBox.data('hash')] = $attachmentBox;
            }, this);

            this.createAttachments(fileList);
        },

        createAttachments: function (files) {
            if (files.length === 0) {
                return;
            }

            let file = files.shift();

            // if (this.attachmentBoxes[file.size + '_hash_' + file.name] === null) {
            //     this.uploadedCount++;
            //     this.createAttachments(files);
            //     return;
            // }

            let $attachmentBox = this.attachmentBoxes[file.size + '_hash_' + file.name];

            let fileReader = new FileReader();
            fileReader.onload = function (e) {
                $.ajax({
                    type: 'POST',
                    url: 'Attachment?silent=true',
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
                    $attachmentBox.parent().find('.uploading-message').remove();
                    $attachmentBox.attr('data-id', attachment.id);

                    this.files[attachment.id] = attachment.name;

                    this.uploadedCount++;

                    this.afterAttachmentUploaded(files);
                }.bind(this)).error(function (response) {
                    let reason = response.getResponseHeader('X-Status-Reason') || this.translate('Failed');

                    $attachmentBox.parent().find('.uploading-message').html(reason);
                    $attachmentBox.css('background-color', '#f2dede');

                    this.totalCount--;
                    this.failedCount++;

                    this.afterAttachmentUploaded(files);
                }.bind(this));
            }.bind(this);
            fileReader.readAsDataURL(file);
        },

        afterAttachmentUploaded(files) {
            let $progress = $('.attachment-upload .progress');

            let percentCompleted = 0;

            let done = this.uploadedCount === this.totalCount || this.totalCount === 0;
            if (!done) {
                $progress.show();
                percentCompleted = this.getPercentCompleted();
                this.createAttachments(files);
            } else {
                $progress.hide();

                if (this.failedCount > 0) {
                    let message = this.translate('notAllAssetsWereUploaded', 'messages', 'Asset');
                    message = message.replace('XX', this.failedCount);
                    message = message.replace('YY', this.failedCount + this.uploadedCount);

                    Espo.Ui.notify(message, 'error', 1000 * 120, true);
                }

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