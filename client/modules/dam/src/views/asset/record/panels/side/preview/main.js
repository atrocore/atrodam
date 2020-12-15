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

Espo.define('dam:views/asset/record/panels/side/preview/main', ['view', "dam:config"],
    (Dep, Config) => {
        return Dep.extend({
            template: "dam:asset/record/panels/side/preview/main",
            damConfig: null,

            events: {
                'click a[data-action="showImagePreview"]': function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                    let id = $(e.currentTarget).data('id');
                    this.createView('preview', 'dam:views/modals/image-preview', {
                        id: id,
                        model: this.model
                    }, function (view) {
                        view.render();
                    });
                }
            },
            setup() {
                this.damConfig = Config.prototype.init.call(this);
                Dep.prototype.setup.call(this);

                this.listenTo(this.model, "change:fileId", () => {
                    this.reRender();
                });
            },
            data() {
                let hasPreview = this.getMetadata().get(`fields.asset.typeNatures.${this.model.get("type")}`) === "Image";
                if (!hasPreview && this.model.get('name')) {
                    let fileExt = this.model.get('name').split('.').pop().toLowerCase();
                    if (fileExt === 'pdf') {
                        hasPreview = true;
                    }
                }

                return {
                    previewId: hasPreview ? this.model.get('fileId') : null,
                    path: this.options.el,
                    icon: this.model.get('icon')
                };
            }
        });
    }
);