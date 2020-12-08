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

Espo.define("dam:views/rendition/fields/name", ["views/fields/varchar"], Dep =>
    Dep.extend({
        fileNameFromFile: null,
        
        setup() {
            Dep.prototype.setup.call(this);
            
            this.listenTo(this.model, "change:fileName", () => {
                if (this.model.get("name") && !this._isGeneratedName()) {
                    return;
                }
                
                this.model.set("name", this._setNameFromFile());
            });
        },
        
        _setNameFromFile() {
            let fileName = this._getFileName();
            
            this.fileNameFromFile = this._normalizeFileName(fileName);
            
            return this.fileNameFromFile;
        },
        
        _getFileName() {
            let fileName = this.model.get("fileName");
            
            if (!fileName) {
                return "";
            }
            
            fileName = fileName.split('.');
            fileName.pop();
            return fileName.join('.');
        },
        
        _normalizeFileName(fileName) {
            return fileName.replace(/[_-]+/gm, " ");
        },
    
        _isGeneratedName () {
            return this.model.get("name") === this.fileNameFromFile
        }
    })
);