jQuery(document).ready(function($) {
    // Target all deactivate links on the plugins page
    $('tr[data-slug="anber-wp-security"] .deactivate a').on('click', function(e) {
        e.preventDefault();  // Prevent the default action

        // Show the modal instead of confirm
        $('#deactivationModal').modal('show');

        var deactivateLink = $(this).attr('href');  // Save the href link

        // When the user clicks "Deactivate" on the modal
        $('#confirmDeactivate').off('click').on('click', function() {
            // Redirect to the plugin deactivation link
            window.location.href = deactivateLink;
        });
    });

});




