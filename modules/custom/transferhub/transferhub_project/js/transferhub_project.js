/**
 * Created by albert on 13/08/16.
 */

jQuery( document ).ready(function() {

    //function: update visibility of organization fields, based on user selections
    function updateDisplay()
    {
        if (jQuery("#edit-field-use-existing-organization-value").is(":checked"))
        {
            jQuery("#edit-field-organization-new-wrapper").hide();
            jQuery("#edit-field-organization-existing-wrapper").show();
        }
        else
        {
            jQuery("#edit-field-organization-existing-wrapper").hide();
            jQuery("#edit-field-organization-new-wrapper").show();
        }
    }

    //inicialization
    updateDisplay();

    //set event
    jQuery('#edit-field-use-existing-organization-value').change(function() {
        updateDisplay();
    });

});