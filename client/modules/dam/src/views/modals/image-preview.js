/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

Espo.define('dam:views/modals/image-preview', 'views/modals/image-preview', function (Dep) {
    return Dep.extend({
        template: "dam:modals/image-preview",

        data() {
            return _.extend(Dep.prototype.data.call(this), {
                path: this.options.el,
                name: this.model.get('name'),
            });
        },

        getImageUrl() {
            return this.getBasePath() + '/' + this.model.get('filePathsData').thumbs.large;
        },

        getOriginalImageUrl: function () {
            return this.getBasePath() + '/' + this.model.get('filePathsData').download;
        },

    });
});