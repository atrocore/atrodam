

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

Espo.define('dam:views/asset_relation/modals/asset-form', 'views/record/detail', function (Dep) {
    return Dep.extend({
        sideDisabled   : true,
        bottomDisabled : true,
        buttonsDisabled: true,
        isWide         : true,
        layoutName     : "detailSmall",
        fieldsMode     : "edit",
        type           : "edit",
        columnCount    : 1,
        skipRows       : [
            "image", "file", "type", "active", "private"
        ],
        
        getGridLayout: function (callback) {
            if (this.gridLayout !== null) {
                callback(this.gridLayout);
                return;
            }
            
            var gridLayoutType = this.gridLayoutType || 'record';
            if (this.detailLayout) {
                this.gridLayout = {
                    type  : gridLayoutType,
                    layout: this.convertDetailLayout(this.detailLayout)
                };
                callback(this.gridLayout);
                return;
            }
            
            this._helper.layoutManager.get(this.model.name, this.layoutName, function (simpleLayout) {
                
                simpleLayout = this._filterSimpleLayout(simpleLayout);
           
                this.gridLayout = {
                    type  : gridLayoutType,
                    layout: this.convertDetailLayout(simpleLayout)
                };
                callback(this.gridLayout);
            }.bind(this));
        },
        
        _filterSimpleLayout(layout) {
            for (let i = 0; layout.length > i; i++) {
                layout[i]['rows'] = this._parseRows(layout[i]['rows']);
            }
            
            return layout;
        },
        _parseRows(rows) {
            let newRows = [];
            for (let i = 0; rows.length > i; i++) {
                let row = this._parseRow(rows[i]);
                if (row.length > 0) {
                    newRows.push(row);
                }
            }
            
            return newRows;
        },
        _parseRow(row) {
            let newRow = [];
            for (let i = 0; row.length > i; i++) {
                if (this.skipRows.indexOf(row[i].name) !== -1) {
                    continue;
                }
                newRow.push(row[i]);
            }
            return newRow;
        }
    });
});