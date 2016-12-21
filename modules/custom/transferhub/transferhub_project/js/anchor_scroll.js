/**
 * Created by albert on 18/08/16.
 */

// Get any params from the URL
jQuery.extend({
    getUrlVars: function(){
        var vars = [], hash;
        var url = decodeURIComponent(window.location.href);
        var hashes = url.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    },
    getUrlVar: function(name){
        return jQuery.getUrlVars()[name];
    }
});

jQuery(document).ready(function() {
    // Unhide the main content area
    ///jQuery('section.centered').fadeIn('slow');

    // Create a var out of the URL param that we can scroll to
    var page = jQuery.getUrlVar('page');
    if (page.length > 0)
    {
        var scrollElement = '#' + page;

        // Scroll down to the newly specified anchor point
        var destination = jQuery(scrollElement).offset().top;
        jQuery("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, 800 );
    }
    return false;
});
