$(document).ready(function () {
    var ipAddress = '';
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

    // IP Info Popup
    $('#invalid-links-table').on('click', '.ip-click', function (e) {
        e.stopPropagation();
        const ip = $(this).data('ip');
        ipAddress = ip;
        const url = 'https://ipinfo.io/' + ip + '/json';

        let x = e.pageX - 30;
        let y = e.pageY + 25;
        y -= window.scrollY;

        $.ajax({
            url: url,
            type: 'GET',
            dataType: "json",
        })
            .done(response => {
                $.ajax({
                    url: '/?',
                    type: 'GET',
                    dataType: "json",
                    data: {
                        action: 'add',
                        type: 'ipData',
                        ipData: response
                    }
                }).done(resp => {
                    $('#ip-model').html(resp.content);
                    $('#ip-model').show();

                    $('#ip-model').css('left', x + 'px');
                    $('#ip-model').css('top', y + 'px');
                    $('#ip-model').css({
                        "-webkit-transform": 'translate(0%, -100%)',
                        "-ms-transform": 'translate(0%, -100%)',
                        "transform": 'translate(0%, -100%)'
                    });
                });
            });
    });

    $('#ip-model').on({
        mousemove: function (event) {
            $(this).show();

            let x = event.pageX - 30;
            let y = event.pageY + 25;
            y -= window.scrollY;

            $(this).css('left', x + 'px');
            $(this).css('top', y + 'px');

            $(this).css({
                "-webkit-transform": 'translate(0%, -100%)',
                "-ms-transform": 'translate(0%, -100%)',
                "transform": 'translate(0%, -100%)'
            });
        },

        mousedown: function (e) {
            switch (e.which) {
                case 1:
                    $(this).hide();
                    break;
                case 3:
                    var url = 'https://ipinfo.io/' + ipAddress + '/json';
                    window.open(url, '_blank');
                    break;
            }
        },

        mouseleave: function () {
            $(this).hide();
        }
    });

    // Host Filter Logic
    $('#filter-btn').on('click', function () {
        const selectedHosts = [];
        $('.host-checkbox:checked').each(function () {
            selectedHosts.push($(this).val());
        });

        const showAll = selectedHosts.length === 0;

        if (showAll) {
            $('.parent-row').show();
            $('.child-row').hide();
            return;
        }

        // Hide all parents initially, we will show them if they have matching children
        $('.parent-row').hide();

        // Iterate over all child rows
        $('.child-row').each(function () {
            const row = $(this);
            const host = row.data('host');

            if (selectedHosts.includes(host)) {
                row.show();
                // Show parent
                const entryId = row.attr('class').match(/child-of-(\d+)/)[1];
                $('.parent-row[data-entry-id="' + entryId + '"]').show();
            } else {
                row.hide();
            }
        });
    });
});