jQuery( document ).ready(function() {

    var random = Math.floor(Math.random() * jQuery('.advertisment-row').length);
    console.log(random);
    jQuery('#advertisment-views-row-' + random).removeClass("hidden");

});