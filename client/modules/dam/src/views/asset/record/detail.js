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

Espo.define('dam:views/asset/record/detail', 'views/record/detail-tree',
    Dep => Dep.extend({

        duplicateAction: false,

        sideView: "dam:views/asset/record/detail-side",

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'before:save', attrs => {
                if (attrs) {
                    let name = attrs[this.name] || null;
                    let filename = attrs['fileName'] || this.model.get("fileName") || '';

                    if (name && filename && name !== filename) {
                        attrs[this.name] = filename;
                    }
                }
            });

            this.listenTo(this.model, "change:name", () => {
                const name = this.model.get('name');
                if (this.mode === 'edit' && name) {
                    const ext = (this.model.get('fileName') || '').split('.').pop();
                    if (!name.endsWith('.' + ext)) {
                        this.model.set('name', name + '.' + ext, {silent: true});
                        this.model.set('fileName', this.model.get('name'));
                    }
                }
            });

            this.listenTo(this.model, "change:fileId", () => {
                this.toggleVisibilityForImagesAttributesFields();
            });
        },

        afterRender() {
            Dep.prototype.afterRender.call(this);

            this.toggleVisibilityForImagesAttributesFields();
        },

        toggleVisibilityForImagesAttributesFields() {
            ['width', 'height', 'orientation', 'colorDepth', 'colorSpace'].forEach(name => {
                if (this.isImage()) {
                    this.getView('middle').getView(name).show();
                } else {
                    this.getView('middle').getView(name).hide();
                }
            });
        },

        isImage() {
            const imageExtensions = this.getMetadata().get('dam.image.extensions') || [];
            const fileExt = (this.model.get('fileName') || '').split('.').pop().toLowerCase();

            return $.inArray(fileExt, imageExtensions) !== -1;
        },

    })
);