

Espo.define('dam:views/asset_meta_data/detail', 'dam:views/detail',
    Dep => Dep.extend({
        array: [],
        setupHeader: function () {
            this.waitForView("header");
            if (this.model.get("assetId")) {
                this.createAssetBreadcrumbs();
            }

            if (this.model.get("renditionId")) {
                this.createRenditionBreadcrumbs();
            }

            this.listenTo(this.model, 'sync', function (model) {
                this.waitForView("header");
                if (model.get("assetId")) {
                    this.createAssetBreadcrumbs();
                }
                if (model.get("renditionId")) {
                    this.createRenditionBreadcrumbs();
                }
                if (model.hasChanged('name')) {
                    this.getView('header').reRender();
                    this.updatePageTitle();
                }
            }, this);
        },

        createRenditionBreadcrumbs() {
            this.getModelFactory().create("Rendition", (renditionModel) => {
                renditionModel.id = this.model.get("renditionId");
                renditionModel.fetch().then(() => {
                    if (renditionModel.get("assetId")) {
                        this.getModelFactory().create("Asset", (assetModel) => {
                            assetModel.id = renditionModel.get("assetId");
                            assetModel.fetch().then(() => {
                                let name = Handlebars.Utils.escapeExpression(this.model.get('name'));
                                let assetName = Handlebars.Utils.escapeExpression(assetModel.get('name'));
                                let renditionName = Handlebars.Utils.escapeExpression(renditionModel.get('name'));

                                this.array = [
                                    '<a href="#Asset">' + this.getLanguage().translate("Asset", 'scopeNamesPlural') + '</a>',
                                    '<a href="#Asset/view/' + assetModel.get('id') + '">' + assetName + '</a>',
                                    this.getLanguage().translate("Rendition", "scopeNamesPlural"),
                                    '<a href="#Rendition/view/' + renditionModel.get('id') + '">' + renditionName + '</a>',
                                    this.getLanguage().translate(this.scope, 'scopeNamesPlural'),
                                    name
                                ];

                                this.createView('header', this.headerView, {
                                    model: this.model,
                                    el: '#main > .header',
                                    scope: this.scope
                                });
                            });
                        })
                    }
                });
            })
        },

        createAssetBreadcrumbs() {
            this.getModelFactory().create("Asset", (model) => {
                model.id = this.model.get("assetId");
                model.fetch().then(() => {
                    let name = Handlebars.Utils.escapeExpression(this.model.get('name'));
                    let assetName = Handlebars.Utils.escapeExpression(model.get('name'));

                    this.array = [
                        '<a href="#Asset">' + this.getLanguage().translate("Asset", 'scopeNamesPlural') + '</a>',
                        '<a href="#Asset/view/' + model.get('id') + '">' + assetName + '</a>',
                        this.getLanguage().translate(this.scope, 'scopeNamesPlural'),
                        name
                    ];

                    this.createView('header', this.headerView, {
                        model: this.model,
                        el: '#main > .header',
                        scope: this.scope
                    });
                });
            });
        },

        getHeader() {
            return this.buildHeaderHtml(this.array);
        }
    })
);