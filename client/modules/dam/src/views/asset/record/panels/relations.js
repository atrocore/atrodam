

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

Espo.define('dam:views/asset/record/panels/relations', 'treo-core:views/record/panels/relationship',
    Dep => {
        return Dep.extend({
            template: "dam:asset/record/panels/relations",
            blocks  : [],
            
            data() {
                return {
                    blocks: this.blocks
                };
            },
            
            setup() {
                this.getGroupsInfo();
            },
            
            getGroupsInfo() {
                this.wait(true);
                let url       = `AssetRelation/EntityList/${this.model.id}`;
                this.blocks   = [];
                let showFirst = true;
                
                this.getCollectionFactory().create("AssetRelation", (collection) => {
                    collection.url = url;
                    collection.fetch().then(() => {
                        this.collection = collection;
                        this.collection.forEach((model) => {
                            model.set({
                                entityName: "Asset",
                                entityId  : this.model.id
                            });
                            
                            let params = {
                                model: model,
                                el   : this.options.el + ' .group[data-name="' + model.get("name") + '"]'
                            };
                            
                            this.blocks.push(model.get("name"));
                            this.createView(model.get('name'), "dam:views/asset/record/panels/entity-block", params);
                        });
                        this.wait(false);
                    });
                });
            }
        });
    }
);