$(document).ready(function () {
    // Toggles the visibility of child rows (links) for an entry
    $('#invalid-links-table').on('click', '.parent-row', function () {
        const entryId = $(this).data('entry-id');
        $('.child-of-' + entryId).slideToggle('fast');
    });

    // Toggles the visibility of the IP address list for a single link
    $('#invalid-links-table').on('click', '.ip-count-toggle', function (e) {
        e.stopPropagation(); // Prevents the parent row's click event from firing
        const targetId = $(this).data('target');
        $(targetId).slideToggle('fast');
    });

    // Ban IP
    $('#invalid-links-table').on('click', '.ban-btn', function (e) {
        e.stopPropagation();
        const btn = $(this);
        const ip = btn.data('ip');
        const parentDiv = btn.closest('div');

        $.post('?action=ban', { ip: ip }, function (response) {
            try {
                const res = JSON.parse(response);
                if (res.success) {
                    btn.hide();
                    parentDiv.find('.unban-btn').show();
                    parentDiv.find('.banned-indicator').html(' <span style="color:red;">🔴</span>');
                } else {
                    alert('Failed to ban IP');
                }
            } catch (e) {
                console.error('Error parsing response', e);
                alert('An error occurred');
            }
        });
    });

    // Unban IP
    $('#invalid-links-table').on('click', '.unban-btn', function (e) {
        e.stopPropagation();
        const btn = $(this);
        const ip = btn.data('ip');
        const parentDiv = btn.closest('div');

        $.post('?action=delete&entity=banned', { ip: ip }, function (response) {
            try {
                const res = JSON.parse(response);
                if (res.success) {
                    btn.hide();
                    parentDiv.find('.ban-btn').show();
                    parentDiv.find('.banned-indicator').empty();
                } else {
                    alert('Failed to unban IP');
                }
            } catch (e) {
                console.error('Error parsing response', e);
                alert('An error occurred');
            }
        });
    });
});