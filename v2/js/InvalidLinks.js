$(document).ready(function() {
    $('.delete-invalid-link').on('click', function() {
        var invalidLinkId = $(this).data('id');
        var row = $(this).closest('tr');

        if (confirm('Are you sure you want to delete this record?')) {
            $.ajax({
                url: '?action=delete',
                type: 'POST',
                data: {
                    entity: 'invalidLink',
                    id: invalidLinkId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(500, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Failed to delete the record.');
                    }
                },
                error: function() {
                    alert('An error occurred while trying to delete the record.');
                }
            });
        }
    });
});