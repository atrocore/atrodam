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

Espo.define('dam:views/asset/fields/files', ['views/fields/attachment-multiple', 'views/fields/file', 'lib!MD5'], function (Dep, File, MD5) {

    return Dep.extend({

        editTemplate: 'dam:asset/fields/files/edit',

        showPreviews: false,

        isUploading: false,

        events: _.extend(Dep.prototype.events, {
                'click a.remove-attachment': function (e) {
                    let $div = $(e.currentTarget).parent();
                    let id = $div.attr('data-id');
                    let hash = $div.attr('data-unique');

                    if (id) {
                        $.ajax({
                            type: 'DELETE',
                            url: `Attachment/${id}?silent=true`,
                            contentType: "application/json"
                        });

                        let filesIds = [];
                        (this.model.get('filesIds') || []).forEach(function (fileId) {
                            if (fileId !== id) {
                                filesIds.push(fileId);
                            }
                        });
                        this.model.set('filesIds', filesIds, {silent: true});
                    } else {
                        delete this.uploadedSize[hash];
                        delete this.filesSize[hash];
                    }

                    this.updateProgress();

                    $div.parent().remove();

                    if (this.isDone()) {
                        this.isUploading = false;
                    }
                }
            },
        ),

        setup() {
            Dep.prototype.setup.call(this);

            this.model.set('name', 'massUpload', {silent: true});

            this.fileList = [];
            this.uploadedSize = {};
            this.filesSize = {};

            this.listenTo(this.model, "change:type", () => this.empty());

            this.listenTo(this.model, "updating-started", function () {
                this.isUploading = true;
            });

            this.listenTo(this.model, "updating-ended", function () {
                setTimeout(function () {
                    let failedCount = $('.file-uploading-failed').length;
                    if (failedCount > 0) {
                        let message = this.translate('notAllAssetsWereUploaded', 'messages', 'Asset');
                        message = message.replace('XX', failedCount);
                        message = message.replace('YY', $('.uploaded-file').length);

                        Espo.Ui.notify(message, 'error', 1000 * 120, true);
                    }
                }.bind(this), 100);
                this.isUploading = false;
            }.bind(this));
        },

        getPercentCompleted: function () {
            let uploaded = this.getFilesSize();
            if (uploaded === 0) {
                return 0;
            }

            return 100 / uploaded * this.getUploadedSize();
        },

        getMaxUploadSize: function () {
            return File.prototype.getMaxUploadSize.call(this);
        },

        createFileUniqueHash: function (file) {
            return MD5(`${file.name}_${file.size}`);
        },

        slice: function (file, start, end) {
            return File.prototype.slice.call(this, file, start, end);
        },

        createFilePieces: function (file, sliceSize, start, stream) {
            return File.prototype.createFilePieces.call(this, file, sliceSize, start, stream);
        },

        addFileBox: function (file) {
            let $attachments = this.$attachments;
            let removeLink = '<a href="javascript:" class="remove-attachment pull-right"><span class="fas fa-times"></span></a>';
            let $att = $(`<div class="uploaded-file gray-box" data-unique="${file.uniqueId}">`)
                .append(removeLink)
                .append($('<span class="preview">' + file.name + '</span>').css('width', 'cacl(100% - 30px)'));

            let $container = $('<div>').append($att);
            $attachments.append($container);

            let $loading = $('<span class="small uploading-message">' + this.translate('Pending...') + '</span>');
            $container.append($loading);

            return $att;
        },

        uploadFiles: function (files) {
            for (let i = 0; i < files.length; i++) {
                let file = files[i];
                file['uniqueId'] = this.createFileUniqueHash(file);

                if (!this.isFileInList(file['uniqueId'])) {
                    file['attachmentBox'] = this.addFileBox(file);
                    this.fileList.push(file);
                    this.filesSize[file.uniqueId] = file.size;
                    this.uploadedSize[file.uniqueId] = [];

                    this.updateProgress();
                }
            }

            if (!this.isUploading) {
                this.model.trigger('updating-started');
                this.updateProgress();
                this.createAttachments();
            }
        },

        createAttachments: function () {
            if (this.fileList.length === 0 || !this.isModalOpen()) {
                return;
            }

            let file = this.fileList.shift();

            if (!this.isFileInList(file.uniqueId)) {
                this.createAttachments();
                return;
            }

            file.attachmentBox.parent().find('.uploading-message').html(this.translate('Uploading...'));

            if (file.size > this.getMaxUploadSize() * 1024 * 1024) {
                this.chunkCreateAttachments(file);
            } else {
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
                    }).done(function (response) {
                        this.pushPieceSize(file.uniqueId, file.size);
                        this.updateProgress();

                        this.uploadSuccess(file, response);
                    }.bind(this)).error(function (response) {
                        this.pushPieceSize(file.uniqueId, file.size);
                        this.updateProgress();

                        this.uploadFailed(file, response);
                    }.bind(this));
                }.bind(this);
                fileReader.readAsDataURL(file);
            }
        },

        chunkCreateAttachments: function (file) {
            const chunkFileSize = this.getConfig().get('chunkFileSize') || 2;
            const sliceSize = chunkFileSize * 1024 * 1024;

            this.streams = this.getConfig().get('fileUploadStreamCount') || 3;

            this.setProgressMessage(file);

            // create file pieces
            this.pieces = [];
            this.createFilePieces(file, sliceSize, 0, 1);

            let promiseList = [];
            promiseList.push(new Promise(function (resolve) {
                let stream = 1;
                while (stream <= this.streams) {
                    let pieces = [];
                    this.pieces.forEach(function (row) {
                        if (row.stream === stream) {
                            pieces.push(row);
                        }
                    });
                    this.sendChunk(resolve, file, pieces);
                    stream++;
                }
            }.bind(this)));

            Promise.all(promiseList).then(function () {
                this.createByChunks(file);
            }.bind(this));

        },

        isModalOpen: function () {
            return $('.attachment-upload').length > 0;
        },

        sendChunk: function (resolve, file, pieces) {
            if (!this.isModalOpen()) {
                return;
            }

            if (pieces.length === 0) {
                resolve();
                return;
            }

            const item = pieces.shift();

            const reader = new FileReader();
            reader.readAsDataURL(item.piece);

            reader.onloadend = function () {
                $.ajax({
                    type: 'POST',
                    url: 'Attachment/action/CreateChunks?silent=true',
                    contentType: "application/json",
                    data: JSON.stringify({
                        chunkId: file.uniqueId,
                        start: item.start,
                        piece: reader.result,
                    }),
                }).done(function (response) {
                    if (!this.pushPieceSize(file.uniqueId, item.piece.size) || file.attachmentBox.hasClass('file-uploading-failed')) {
                        resolve();
                        return;
                    }

                    this.setProgressMessage(file);
                    this.updateProgress();

                    if (pieces.length > 0) {
                        this.sendChunk(resolve, file, pieces);
                    }

                    let piecesSize = this.uploadedSize[file.uniqueId].reduce((a, b) => a + b, 0);
                    if (piecesSize === this.filesSize[file.uniqueId]) {
                        resolve();
                    }
                }.bind(this)).error(function (response) {
                    this.chunkFailedResponse = response;
                    resolve();
                }.bind(this));
            }.bind(this)
        },

        createByChunks: function (file) {
            if (!this.isModalOpen()) {
                return;
            }

            if (this.pieces.length === 0 || !this.isFileInList(file.uniqueId)) {
                this.createAttachments();
                return;
            }

            this.pieces = [];

            if (this.chunkFailedResponse) {
                this.uploadFailed(file, this.chunkFailedResponse);
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'Attachment/action/CreateByChunks?silent=true',
                contentType: "application/json",
                data: JSON.stringify({
                    chunkId: file.uniqueId,
                    name: file.name,
                    type: file.type || 'text/plain',
                    size: file.size,
                    role: 'Attachment',
                    relatedType: this.model.name,
                    field: this.name,
                    modelAttributes: this.model.attributes
                }),
            }).done(function (response) {
                this.uploadSuccess(file, response);
            }.bind(this)).error(function (response) {
                this.uploadFailed(file, response);
            }.bind(this));
        },

        setProgressMessage: function (file) {
            let piecesSize = this.uploadedSize[file.uniqueId].reduce((a, b) => a + b, 0);
            let percent = piecesSize / this.filesSize[file.uniqueId] * 100;

            file.attachmentBox.parent().find('.uploading-message').html(this.translate('Uploading...') + ' <span class="uploading-progress-message">' + percent.toFixed(0) + '%</span>');
        },

        uploadSuccess: function (file, attachment) {
            file.attachmentBox.parent().find('.uploading-message').remove();

            if (attachment !== null) {
                file.attachmentBox.attr('data-id', attachment.id);
            }

            let filesIds = this.model.get('filesIds') || [];
            filesIds.push(attachment.id);

            let filesNames = this.model.get('filesNames') || {};
            filesNames[attachment.id] = attachment.name;

            this.model.set('filesIds', filesIds, {silent: true});
            this.model.set('filesNames', filesNames, {silent: true});

            if (this.isDone()) {
                this.model.trigger('updating-ended');
            } else {
                this.createAttachments();
            }
        },

        uploadFailed: function (file, response) {
            let reason = response.getResponseHeader('X-Status-Reason') || this.translate('assetCouldNotBeUploaded', 'messages', 'Asset');

            file.attachmentBox.parent().find('.uploading-message').html(reason);
            file.attachmentBox.addClass('file-uploading-failed');

            delete this.uploadedSize[file.uniqueId];
            delete this.filesSize[file.uniqueId];

            this.updateProgress();

            if (this.isDone()) {
                this.model.trigger('updating-ended');
            } else {
                this.createAttachments();
            }
        },

        isDone: function () {
            return this.getFilesSize() === this.getUploadedSize();
        },

        getFilesSize: function () {
            let filesSize = 0;
            $.each(this.filesSize, function (hash, size) {
                filesSize += size;
            });

            return filesSize;
        },

        getUploadedSize: function () {
            let uploadedSize = 0;
            $.each(this.uploadedSize, function (hash, pieces) {
                pieces.forEach(function (size) {
                    uploadedSize += size;
                });
            });

            return uploadedSize;
        },

        updateProgress: function () {
            let $progress = $('.attachment-upload .progress .progress-bar');
            let percentCompleted = this.getPercentCompleted();

            if (percentCompleted !== 0 && percentCompleted !== 100) {
                percentCompleted = Math.round(percentCompleted);
                $progress.parent().show();
                $progress.css('width', percentCompleted + '%').html(percentCompleted + '% ' + this.translate('uploaded', 'labels', 'Asset'));
            } else {
                $progress.parent().hide();
            }
        },

        findFile: function (uniqueId) {
            let result = null;
            this.fileList.forEach(function (item) {
                if (item.uniqueId === uniqueId) {
                    result = item;
                }
            });

            return result;
        },

        pushPieceSize: function (hash, size) {
            if (this.isFileInList(hash)) {
                this.uploadedSize[hash].push(size);
                return true;
            }

            return false;
        },

        isFileInList: function (hash) {
            return typeof this.uploadedSize[hash] !== 'undefined'
        },

    })
});