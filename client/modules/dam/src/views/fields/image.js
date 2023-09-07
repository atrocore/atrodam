/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.txt, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

Espo.define('dam:views/fields/image', 'views/fields/file', function (Dep) {
    return Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.type = 'image';
            this.showPreview = true;
            this.accept = ['image/*'];
            this.defaultType = 'image/jpeg';
            this.previewSize = 'small'
        }

    });
});
