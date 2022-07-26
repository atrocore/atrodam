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
 *
 *  This software is not allowed to be used in Russia and Belarus.
 */

Espo.define('dam:views/asset/fields/is-main-image', 'views/fields/bool',
    Dep => Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'change:fileName', () => {
                this.reRender();
            });
        },

        afterRender() {
            Dep.prototype.afterRender.call(this);

            let inRelatingEntities = false;

            let relatingEntities = this.getMetadata().get(['entityDefs', 'Asset', 'fields', 'isMainImage', 'relatingEntityField']);
            if (relatingEntities) {
                if (this.mode === 'edit' || this.mode === 'detail') {
                    let entityType = window.location.hash.split('/').shift().replace('#', '');
                    if (relatingEntities.includes(entityType)) {
                        inRelatingEntities = true;
                    }
                }
            }

            if (inRelatingEntities && this.isImage()) {
                this.show();
            } else {
                this.hide();
            }
        },

        isImage() {
            const imageExtensions = this.getMetadata().get('dam.image.extensions') || [];
            const fileExt = (this.model.get('fileName') || '').split('.').pop().toLowerCase();

            return $.inArray(fileExt, imageExtensions) !== -1;
        },

    })
);
