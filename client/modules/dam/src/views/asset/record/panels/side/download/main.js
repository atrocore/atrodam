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
 * This software is not allowed to be used in Russia and Belarus.
 */

Espo.define('dam:views/asset/record/panels/side/download/main', ['view', "dam:config"],
    (Dep, Config) => {
        return Dep.extend({
            template: "dam:asset/record/panels/side/download/main",
            active: "original",
            viewsLists: [
                "original",
                // "renditions",
                "custom"
            ],
            damConfig: null,

            events: {
                'change input[name="downloadType"]': function (e) {
                    let $el = $(e.currentTarget);
                    this._updateActive($el.val());
                },

                'click a[data-name="custom-download"]': function (e) {
                    let $el = $(e.currentTarget);
                    $el.prop("href", this._buildUrl());
                }
            },

            setup() {
                Dep.prototype.setup.call(this);
                this.damConfig = Config.prototype.init.call(this);

                if (this.model.get("type")) {
                    this._buildViews();
                } else {
                    this.listenToOnce(this.model, "sync", () => {
                        if (this.model.get("type")) {
                            this._buildViews();
                            this.reRender();
                        }
                    });
                }
            },

            isImage() {
                const imageExtensions = this.getMetadata().get('dam.image.extensions') || [];
                const fileExt = (this.model.get('fileName') || '').split('.').pop().toLowerCase();

                return $.inArray(fileExt, imageExtensions) !== -1;
            },

            _buildUrl() {
                return this.getView(this.active).buildUrl();
            },

            _buildViews() {
                this._renderOriginal();
                if (this.isImage()) {
                    // this._renderRenditions();
                    this._renderCustom();
                }
            },

            _renderOriginal() {
                this.waitForView("original");
                this.createView("original", "dam:views/asset/record/panels/side/download/original", {
                    el: this.options.el + ' div[data-name="original"]',
                    model: this.model
                });
            },

            _renderRenditions() {
                this.waitForView("renditions");
                this.createView("renditions", "dam:views/asset/record/panels/side/download/renditions", {
                    el: this.options.el + ' div[data-name="renditions"]',
                    model: this.model
                });
            },

            _renderCustom() {
                this.waitForView("custom");
                this.createView("custom", "dam:views/asset/record/panels/side/download/custom", {
                    el: this.options.el + ' div[data-name="custom"]',
                    model: this.model
                });
            },

            _updateActive(type) {
                for (let i in this.viewsLists) {
                    this.getView(this.viewsLists[i]).hide();
                }

                this.active = type;
                this.getView(type).show();
            }
        });
    }
);