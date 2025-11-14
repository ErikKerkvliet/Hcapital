$(document).ready(function() {
    // Toggles the visibility of child rows (links) for an entry
    $('#invalid-links-table').on('click', '.parent-row', function() {
        const entryId = $(this).data('entry-id');
        $('.child-of-' + entryId).slideToggle('fast');
    });

    // Toggles the visibility of the IP address list for a single link
    $('#invalid-links-table').on('click', '.ip-count-toggle', function(e) {
        e.stopPropagation(); // Prevents the parent row's click event from firing
        const targetId = $(this).data('target');
        $(targetId).slideToggle('fast');
    });
});