/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

Espo.define('dam:views/asset/fields/file', 'views/fields/file',
    Dep => Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, "change:fileId", () => {
                if (this.mode === 'edit') {
                    this.model.set("name", this.model.get("fileName"));
                }
            });

            this.listenTo(this.model, "change:fileName", () => {
                this.reRender();
            });

            this.listenTo(this.model, "after:save", () => {
                this.reRender();
            });
        },
        afterRender() {
            Dep.prototype.afterRender.call(this);
            if (this.model.get('massCreate')) this.hide()
        },
    })
);