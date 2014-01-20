jQuery(document).ready(function () {
    jQuery(window).resize(function(){
        statistikk_deltakere();
        statistikk_sjangerfordeling();
        statistikk_kjonnsfordeling();
        statistikk_malgruppe();
        statistikk_aldersfordeling();
        statistikk_details_sjangerfordeling();
    });
});