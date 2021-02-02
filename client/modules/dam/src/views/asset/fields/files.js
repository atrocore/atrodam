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
                    let $div = $(e.currentTarget).parent();
                    let id = $div.attr('data-id');
                    if (id) {
                        $.ajax({
                            type: 'DELETE',
                            url: `Attachment/${id}?silent=true`,
                            contentType: "application/json"
                        });

                        this.uploadedCount--;

                        let filesIds = [];
                        (this.model.get('filesIds') || []).forEach(function (fileId) {
                            if (fileId !== id) {
                                filesIds.push(fileId);
                            }
                        });
                        this.model.set('filesIds', filesIds, {silent: true});
                    }
                    this.ignoredNumbers.push($div.data('number'));
                    $div.parent().remove();
                }
            },
        ),

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, "change:type", () => this.empty());

            this.listenTo(this.model, "updating-started", function () {
                $('.attachment-upload .progress').show();
                $('.attachment-upload .btn-upload .btn').addClass('disabled');
                $('.attachment-upload input.file').addClass('disabled').attr('type', 'hidden');
            });

            this.listenTo(this.model, "updating-ended", function () {
                $('.attachment-upload .progress').hide();
                $('.attachment-upload .btn-upload .btn').removeClass('disabled');
                $('.attachment-upload input.file').removeClass('disabled').attr('type', 'file');

                if (this.failedCount > 0) {
                    let message = this.translate('notAllAssetsWereUploaded', 'messages', 'Asset');
                    message = message.replace('XX', this.failedCount);
                    message = message.replace('YY', this.failedCount + this.uploadedCount);

                    Espo.Ui.notify(message, 'error', 1000 * 120, true);
                }
            }.bind(this));
        },

        getPercentCompleted() {
            return Math.round((100 / this.totalSize) * this.uploadedSize);
        },

        getMaxUploadSize: function () {
            return File.prototype.getMaxUploadSize.call(this);
        },

        slice: function (file, start, end) {
            return File.prototype.slice.call(this, file, start, end);
        },

        createFilePieces: function (file, sliceSize, start, stream) {
            return File.prototype.createFilePieces.call(this, file, sliceSize, start, stream);
        },

        addFileBox: function (file, number) {
            let $attachments = this.$attachments;
            let removeLink = '<a href="javascript:" class="remove-attachment pull-right"><span class="fas fa-times"></span></a>';
            let $att = $('<div>')
                .attr('data-number', number)
                .addClass('gray-box')
                .append(removeLink)
                .append($('<span class="preview">' + file.name + '</span>').css('width', 'cacl(100% - 30px)'));

            let $container = $('<div>').append($att);
            $attachments.append($container);

            let $loading = $('<span class="small uploading-message">' + this.translate('Pending...') + '</span>');
            $container.append($loading);

            return $att;
        },

        uploadFiles: function (files) {
            this.totalSize = 0;
            this.uploadedSize = 0;
            let fileList = [];
            let attachmentBoxes = [];
            for (let i = 0; i < files.length; i++) {
                fileList.push(files[i]);
                attachmentBoxes.push(this.addFileBox(files[i], i));
                this.totalSize += files[i].size;
            }

            this.ignoredNumbers = [];
            this.uploadedCount = 0;
            this.failedCount = 0;

            this.currentNumber = null;

            this.model.trigger('updating-started');
            this.model.set('name', 'massUpload', {silent: true});

            this.updateProgress();

            this.createAttachments(fileList, attachmentBoxes);
        },

        createAttachments: function (files, attachmentBoxes) {
            if (files.length === 0 || !this.isModalOpen()) {
                return;
            }

            if (this.currentNumber === null) {
                this.currentNumber = 0;
            } else {
                this.currentNumber++;
            }

            let file = files.shift();
            let $attachmentBox = attachmentBoxes.shift();
            $attachmentBox.parent().find('.uploading-message').html(this.translate('Uploading...'));

            if (this.isCanceled()) {
                this.uploadedSize += file.size;
                this.updateProgress();
                this.createAttachments(files, attachmentBoxes);
                return;
            }

            if (file.size > this.getMaxUploadSize() * 1024 * 1024) {
                this.chunkCreateAttachments(file, $attachmentBox, files, attachmentBoxes);
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
                        this.uploadedSize += file.size;
                        this.updateProgress();

                        this.uploadSuccess(response, $attachmentBox, files, attachmentBoxes);
                    }.bind(this)).error(function (response) {
                        this.uploadedSize += file.size;
                        this.updateProgress();

                        this.uploadFailed(file, response, $attachmentBox, files, attachmentBoxes);
                    }.bind(this));
                }.bind(this);
                fileReader.readAsDataURL(file);
            }
        },

        chunkCreateAttachments: function (file, $attachmentBox, files, attachmentBoxes) {
            const chunkId = File.prototype.createChunkId.call(this);
            const chunkFileSize = this.getConfig().get('chunkFileSize') || 2;
            const sliceSize = chunkFileSize * 1024 * 1024;

            this.streams = this.getConfig().get('fileUploadStreamCount') || 3;
            this.piecesTotal = Math.ceil(file.size / sliceSize);
            this.pieceNumber = 0;
            this.chunkFailedResponse = null;

            this.setProgressMessage($attachmentBox);

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
                    this.sendChunk(resolve, file, pieces, chunkId, $attachmentBox, files, attachmentBoxes);
                    stream++;
                }
            }.bind(this)));

            Promise.all(promiseList).then(function () {
                this.createByChunks(file, chunkId, $attachmentBox, files, attachmentBoxes);
            }.bind(this));

        },

        isCanceled: function () {
            return this.ignoredNumbers.indexOf(this.currentNumber) !== -1;
        },

        isModalOpen: function () {
            return $('.attachment-upload').length > 0;
        },

        sendChunk: function (resolve, file, pieces, chunkId, $attachmentBox, files, attachmentBoxes) {
            if (!this.isModalOpen()) {
                return;
            }

            if (pieces.length === 0 || this.chunkFailedResponse) {
                resolve();
                return;
            }

            const item = pieces.shift();

            // if is canceled
            if (this.isCanceled()) {
                this.pieceNumber++;
                this.uploadedSize += item.piece.size;

                this.updateProgress();

                if (pieces.length > 0) {
                    this.sendChunk(resolve, file, pieces, chunkId, $attachmentBox, files, attachmentBoxes);
                }

                if (this.pieceNumber === this.piecesTotal) {
                    resolve();
                }

                return;
            }

            const reader = new FileReader();
            reader.readAsDataURL(item.piece);

            reader.onloadend = function () {
                $.ajax({
                    type: 'POST',
                    url: 'Attachment/action/CreateChunks?silent=true',
                    contentType: "application/json",
                    data: JSON.stringify({
                        chunkId: chunkId,
                        start: item.start,
                        piece: reader.result,
                    }),
                }).done(function (response) {
                    this.pieceNumber++;
                    this.uploadedSize += item.piece.size;

                    this.setProgressMessage($attachmentBox);
                    this.updateProgress();

                    if (pieces.length > 0) {
                        this.sendChunk(resolve, file, pieces, chunkId, $attachmentBox, files, attachmentBoxes);
                    }

                    if (this.pieceNumber === this.piecesTotal) {
                        resolve();
                    }
                }.bind(this)).error(function (response) {
                    this.uploadedSize += item.piece.size;
                    while (pieces.length > 0) {
                        this.uploadedSize += pieces.shift().piece.size;
                    }
                    this.updateProgress();
                    this.chunkFailedResponse = response;
                    resolve();
                }.bind(this));
            }.bind(this)
        },

        createByChunks: function (file, chunkId, $attachmentBox, files, attachmentBoxes) {
            if (!this.isModalOpen()) {
                return;
            }

            if (this.pieces.length === 0 || this.isCanceled()) {
                this.createAttachments(files, attachmentBoxes);
                return;
            }

            this.pieces = [];
            if (this.chunkFailedResponse) {
                this.uploadFailed(file, this.chunkFailedResponse, $attachmentBox, files, attachmentBoxes);
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'Attachment/action/CreateByChunks?silent=true',
                contentType: "application/json",
                data: JSON.stringify({
                    chunkId: chunkId,
                    name: file.name,
                    type: file.type || 'text/plain',
                    size: file.size,
                    role: 'Attachment',
                    relatedType: this.model.name,
                    field: this.name,
                    modelAttributes: this.model.attributes
                }),
            }).done(function (response) {
                this.uploadSuccess(response, $attachmentBox, files, attachmentBoxes);
            }.bind(this)).error(function (response) {
                this.uploadFailed(file, response, $attachmentBox, files, attachmentBoxes);
            }.bind(this));
        },

        setProgressMessage: function ($attachmentBox) {
            let total = this.piecesTotal + 1;
            let percent = this.pieceNumber / total * 100;

            $attachmentBox.parent().find('.uploading-message').html(this.translate('Uploading...') + ' <span class="uploading-progress-message">' + percent.toFixed(0) + '%</span>');
        },

        uploadSuccess: function (attachment, $attachmentBox, files, attachmentBoxes) {
            $attachmentBox.parent().find('.uploading-message').remove();

            if (attachment !== null) {
                $attachmentBox.attr('data-id', attachment.id);
            }

            let filesIds = this.model.get('filesIds') || [];
            filesIds.push(attachment.id);

            let filesNames = this.model.get('filesNames') || {};
            filesNames[attachment.id] = attachment.name;

            this.model.set('filesIds', filesIds, {silent: true});
            this.model.set('filesNames', filesNames, {silent: true});

            this.uploadedCount++;

            if (this.isDone()) {
                this.model.trigger('updating-ended');
            } else {
                this.createAttachments(files, attachmentBoxes);
            }
        },

        uploadFailed: function (file, response, $attachmentBox, files, attachmentBoxes) {
            let reason = response.getResponseHeader('X-Status-Reason') || this.translate('assetCouldNotBeUploaded', 'messages', 'Asset');

            $attachmentBox.parent().find('.uploading-message').html(reason);
            $attachmentBox.css('background-color', '#f2dede');

            this.failedCount++;

            if (this.isDone()) {
                this.model.trigger('updating-ended');
            } else {
                this.createAttachments(files, attachmentBoxes);
            }
        },

        isDone: function () {
            return this.totalSize === this.uploadedSize;
        },

        updateProgress: function () {
            let percentCompleted = this.getPercentCompleted();
            $('.attachment-upload .progress .progress-bar').css('width', percentCompleted + '%').html(percentCompleted + '% ' + this.translate('uploaded', 'labels', 'Asset'));
        },

    })
});