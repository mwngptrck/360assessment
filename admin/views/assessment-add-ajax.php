<?php
// Add AJAX functionality to the assessment add form
?>
<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // AJAX Assign Users
    $('#assign-users-ajax').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var status = $('#assign-status');
        var selectedUsers = [];
        var assessmentId = $('#assessment_id').val() || 0;
        
        // Get selected users
        $('input[name="assign_users[]"]:checked').each(function() {
            selectedUsers.push($(this).val());
        });
        
        // Skip system admin (user ID 1)
        selectedUsers = selectedUsers.filter(function(id) {
            return parseInt(id) > 1;
        });
        
        if (selectedUsers.length === 0) {
            status.html('<span class="error">' + org360_admin.strings.no_users_selected + '</span>');
            return;
        }
        
        // Disable button and show loading
        button.prop('disabled', true);
        status.html('<span class="loading">' + org360_admin.strings.assigning + '</span>');
        
        // Send AJAX request
        $.ajax({
            url: org360_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'org360_assign_users',
                nonce: org360_admin.nonce,
                assessment_id: assessmentId,
                user_ids: selectedUsers
            },
            success: function(response) {
                if (response.success) {
                    status.html('<span class="success">' + response.data.message + '</span>');
                    // Optionally clear selections
                    $('input[name="assign_users[]"]').prop('checked', false);
                } else {
                    status.html('<span class="error">' + response.data.message + '</span>');
                }
            },
            error: function() {
                status.html('<span class="error">' + org360_admin.strings.error + '</span>');
            },
            complete: function() {
                button.prop('disabled', false);
                setTimeout(function() {
                    status.html('');
                }, 5000);
            }
        });
    });
    
    // Enhanced form submission
    $('#org360-assessment-form').on('submit', function(e) {
        var form = $(this);
        var selectedUsers = [];
        
        // Get selected users for potential assignment after creation
        $('input[name="assign_users[]"]:checked').each(function() {
            selectedUsers.push($(this).val());
        });
        
        // Skip system admin
        selectedUsers = selectedUsers.filter(function(id) {
            return parseInt(id) > 1;
        });
        
        if (selectedUsers.length > 0) {
            form.data('selected-users', selectedUsers);
        }
    });
});
</script>

<style>
.org360-assign-actions {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.org360-assign-status {
    font-weight: 600;
}

.org360-assign-status .loading {
    color: #0073aa;
}

.org360-assign-status .success {
    color: #46b450;
}

.org360-assign-status .error {
    color: #dc3232;
}

#assign-users-ajax .dashicons {
    margin-top: 3px;
}
</style>