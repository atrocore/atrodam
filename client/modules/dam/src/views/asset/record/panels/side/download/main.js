

Espo.define('dam:views/asset/record/panels/side/download/main', ['view', "dam:config"],
    (Dep, Config) => {
        return Dep.extend({
            template  : "dam:asset/record/panels/side/download/main",
            active    : "original",
            viewsLists: ["original", "renditions", "custom"],
            damConfig : null,
            
            events: {
                'change input[name="downloadType"]': function (e) {
                    let $el = $(e.currentTarget);
                    this._updateActive($el.val());
                },
                
                'click a[data-name="custom-download"]': function (e) {
                    let $el = $(e.currentTarget);
                    $el.prop("href", this._buildUrl());
                }
            },
            
            setup() {
                Dep.prototype.setup.call(this);
                this.damConfig = Config.prototype.init.call(this);
                
                if (this.model.get("type")) {
                    let type   = this.damConfig.getType(this.model.get("type"));
                    let nature = this.damConfig.getByType(`${type}.nature`);
                    
                    this._buildViews(nature);
                } else {
                    this.listenToOnce(this.model, "sync", () => {
                        if (this.model.get("type")) {
                            let type   = this.damConfig.getType(this.model.get("type"));
                            let nature = this.damConfig.getByType(`${type}.nature`);
                            
                            this._buildViews(nature);
                            this.reRender();
                        }
                    });
                }
            },
            
            _buildUrl() {
                return this.getView(this.active).buildUrl();
            },
            
            _buildViews(type) {
                this._renderOriginal();
                if (type === "image") {
                    this._renderRenditions();
                    this._renderCustom();
                }
            },
            
            _renderOriginal() {
                this.waitForView("original");
                this.createView("original", "dam:views/asset/record/panels/side/download/original", {
                    el   : this.options.el + ' div[data-name="original"]',
                    model: this.model
                });
            },
            
            _renderRenditions() {
                this.waitForView("renditions");
                this.createView("renditions", "dam:views/asset/record/panels/side/download/renditions", {
                    el   : this.options.el + ' div[data-name="renditions"]',
                    model: this.model
                });
            },
            
            _renderCustom() {
                this.waitForView("custom");
                this.createView("custom", "dam:views/asset/record/panels/side/download/custom", {
                    el   : this.options.el + ' div[data-name="custom"]',
                    model: this.model
                });
            },
            
            _updateActive(type) {
                for (let i in this.viewsLists) {
                    this.getView(this.viewsLists[i]).hide();
                }
                
                this.active = type;
                this.getView(type).show();
            }
        });
    }
);