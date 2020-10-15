

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

Espo.define('dam:views/asset/record/panels/bottom-panel', 'views/record/panels/relationship', Dep => {
    return Dep.extend({
        setup() {
            this.defs.createAction = "createMulti";
            Dep.prototype.setup.call(this);
        },
        
        actionCreateMulti: function (data) {
            data = data || {};
            
            var link        = data.link;
            var scope       = this.model.defs['links'][link].entity;
            var foreignLink = this.model.defs['links'][link].foreign;
            
            var attributes = {};
            
            this.notify('Loading...');
            
            var viewName = this.getMetadata().get('clientDefs.' + scope + '.modalViews.edit') || "dam:views/asset/modals/multi-create";
            this.createView('quickCreate', viewName, {
                scope     : scope,
                relate    : {
                    model: this.model,
                    link : foreignLink
                },
                attributes: attributes
            }, function (view) {
                view.render();
                view.notify(false);
                this.listenToOnce(view, 'after:save', function () {
                    this.updateRelationshipPanel();
                }, this);
            }.bind(this));
        },
        
        updateRelationshipPanel: function () {
            this.notify('Success');
            this.collection.fetch();
        }
    });
});