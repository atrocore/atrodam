

Espo.define('dam:views/asset_relation/edit', 'dam:views/detail',
    Dep => Dep.extend({
        getHeader() {
            let name = Handlebars.Utils.escapeExpression(this.model.get('name'));
            
            if (name === '') {
                name = this.model.id;
            }
            
            return this.buildHeaderHtml([
                this.getLanguage().translate(this.scope, 'scopeNamesPlural'),
                name
            ]);
        }
    })
);