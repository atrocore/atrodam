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

Espo.define('dam:views/rendition/fields/type', ['views/fields/enum', 'dam:config'],
    (Dep, Config) => Dep.extend({
        damConfig: null,
        
        setup() {
            this.damConfig = Config.prototype.init.call(this);
            Dep.prototype.setup.call(this);
        },
        
        setupOptions() {
            if (!this.model.get("assetId")) {
                return;
            }
            this.wait(true);
            this.getModelFactory().create("Asset", model => {
                model.id = this.model.get("assetId");
                model.fetch().then(() => {
                    let type        = this.damConfig.getType(model.get("type"));
                    let enableTypes = this.damConfig.getByType(`${type}.renditions`);
                    
                    let params = [];
                    
                    for (let i in enableTypes) {
                        let item = enableTypes[i];
                        if (!item.auto) {
                            params.push(i);
                        }
                    }
                    
                    this.params.options = params;
                    if (!this.model.has("id")) {
                        this.model.set("type", params[0]);
                    }
                    this.wait(false);
                });
            });
        }
    })
);