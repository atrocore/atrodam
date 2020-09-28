

Espo.define('dam:controllers/block-show-list', 'controllers/record',
    Dep => {
        return Dep.extend({
            beforeList() {
                throw new Espo.Exceptions.NotFound("Action is not found");
            }
        });
    }
);
