/**
 * AtroCore Software
 *
 * This source file is available under GNU General Public License version 3 (GPLv3).
 * Full copyright and license information is available in LICENSE.md, located in the root directory.
 *
 * @copyright  Copyright (c) AtroCore UG (https://www.atrocore.com)
 * @license    GPLv3 (https://www.gnu.org/licenses/)
 */

Espo.define("dam:config", "view", (Dep) => {
    
    let Config = function () {
    };
    
    _.extend(Config.prototype, {
        data : null,
        url  : "DamConfig",
        cache: null,
        
        init() {
            let obj = new Config();
            
            if (obj.loadFromCache(this._helper.cache)) {
                return obj;
            }
            
            obj.fetch();
            return obj;
        },
        
        loadFromCache(cache) {
            this.cache = cache;
            
            if (this.cache) {
                var cached = this.cache.get('app', 'damconfig');
                if (cached) {
                    this.data = cached;
                    return true;
                }
            }
            return null;
        },
        
        fetch() {
            $.ajax({
                url     : this.url,
                type    : 'GET',
                dataType: 'JSON',
                async   : false,
                success : (data) => {
                    this.data = data;
                    this.storeToCache();
                }
            });
        },
        
        storeToCache() {
            if (this.cache) {
                this.cache.set('app', 'damconfig', this.data);
            }
        },
        
        get(path, defaultValue) {
            defaultValue = defaultValue || null;
            var arr;
            if (Array && Array.isArray && Array.isArray(path)) {
                arr = path;
            } else {
                arr = path.split('.');
            }
            
            var pointer = this.data;
            var result  = defaultValue;
            
            for (var i = 0; i < arr.length; i++) {
                var key = arr[i];
                
                if (!(
                    key in pointer
                )) {
                    result = defaultValue;
                    break;
                }
                if (arr.length - 1 == i) {
                    result = pointer[key];
                }
                pointer = pointer[key];
            }
            
            return result;
        },
        
        getByType(path) {
            let arr;
            if (Array && Array.isArray && Array.isArray(path)) {
                arr = path;
            } else {
                arr = path.split('.');
            }
            if (!this.data.type.custom[arr[0]]) {
                return this.data.type.default;
            }
            path = arr.join('.');
            
            return this.get(`type.custom.${path}`);
        },
        
        getType(name) {
            return name.replace(" ", "-").toLowerCase();
        }
        
    }, Dep, Backbone.Events);
    
    return Config;
});