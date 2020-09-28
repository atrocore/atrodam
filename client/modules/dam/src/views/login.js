

Espo.define('dam:views/login', 'class-replace!dam:views/login',
    Dep => Dep.extend({

        getLogoSrc: function () {
            const companyLogoId = this.getConfig().get('companyLogoId');
            if (!companyLogoId) {
                return this.getBasePath() + 'client/modules/dam/img/treo_dam_logo_white.png';
            }
            return this.getBasePath() + '?entryPoint=LogoImage&id='+companyLogoId+'&t=' + companyLogoId;
        }

    })
);