

Espo.define('dam:views/fields/code-from-name', 'dam:views/fields/varchar-with-pattern',
    Dep => Dep.extend({
        
        validationPattern: '^[a-z_0-9]+$',
        
        getPatternValidationMessage() {
            return this.translate('fieldHasPattern', 'messages').replace('{field}', this.translate(this.name, 'fields', this.model.name));
        },
        
        setup() {
            Dep.prototype.setup.call(this);
            
            this.listenTo(this.model, 'change:name', () => {
                if (!this.model.get('code') || this.model.isNew()) {
                    this.model.set(this.name, this.transformToPattern(this.model.get('name')));
                    
                }
            });
        },
        
        transformToPattern(value) {
            return value.toLowerCase().replace(/ /g, '_').replace(/[^a-z_0-9]/g, '');
        }
        
    })
);
