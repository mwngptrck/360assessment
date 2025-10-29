/**
 * Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Org360 Admin JS loaded');
    
    // AJAX User Assignment
    $('.org360-assign-users-ajax').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var assessmentId = button.data('assessment-id');
        var selectedUsers = [];
        
        $('input[name="user_ids[]"]:checked').each(function() {
            selectedUsers.push($(this).val());
        });
        
        if (selectedUsers.length === 0) {
            alert(org360_admin.strings.no_users_selected || 'Please select at least one user.');
            return;
        }
        
        button.prop('disabled', true).text(org360_admin.strings.assigning || 'Assigning...');
        
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
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || org360_admin.strings.error || 'An error occurred.');
                }
            },
            error: function() {
                alert(org360_admin.strings.error || 'An error occurred.');
            },
            complete: function() {
                button.prop('disabled', false).text(org360_admin.strings.assign || 'Assign to Selected Users');
            }
        });
    });
    
    // AJAX Get Competencies
    function loadCompetencies(callback) {
        $.ajax({
            url: org360_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'org360_get_competencies',
                nonce: org360_admin.nonce
            },
            success: function(response) {
                if (response.success && callback) {
                    callback(response.data);
                }
            }
        });
    }
    
    // AJAX Save Questionnaire
    $('.org360-save-questionnaire-ajax').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var form = button.closest('form');
        var questionnaireData = form.serializeArray();
        
        button.prop('disabled', true).text(org360_admin.strings.saving || 'Saving...');
        
        $.ajax({
            url: org360_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'org360_save_questionnaire',
                nonce: org360_admin.nonce,
                questionnaire: questionnaireData
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || org360_admin.strings.error || 'An error occurred.');
                }
            },
            error: function() {
                alert(org360_admin.strings.error || 'An error occurred.');
            },
            complete: function() {
                button.prop('disabled', false).text(org360_admin.strings.save || 'Save');
            }
        });
    });
    
    // AJAX Delete Questionnaire
    $('.org360-delete-questionnaire-ajax').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(org360_admin.strings.confirm_delete || 'Are you sure you want to delete this questionnaire?')) {
            return;
        }
        
        var button = $(this);
        var questionnaireId = button.data('questionnaire-id');
        
        button.prop('disabled', true);
        
        $.ajax({
            url: org360_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'org360_delete_questionnaire',
                nonce: org360_admin.nonce,
                questionnaire_id: questionnaireId
            },
            success: function(response) {
                if (response.success) {
                    button.closest('.questionnaire-item').fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || org360_admin.strings.error || 'An error occurred.');
                    button.prop('disabled', false);
                }
            },
            error: function() {
                alert(org360_admin.strings.error || 'An error occurred.');
                button.prop('disabled', false);
            }
        });
    });
    
    // Sortable Questionnaires (if jQuery UI is available)
    if ($.fn.sortable) {
        $('#questionnaires-container').sortable({
            handle: '.questionnaire-header',
            placeholder: 'questionnaire-placeholder',
            update: function(event, ui) {
                // Update order numbers
                $(this).find('.questionnaire-item').each(function(index) {
                    $(this).find('input[name*="[order_num]"]').val(index);
                });
            }
        });
        
        $('.questions-container').sortable({
            handle: 'h5',
            placeholder: 'question-placeholder',
            update: function(event, ui) {
                // Update order numbers
                $(this).find('.question-item').each(function(index) {
                    $(this).find('input[name*="[order_num]"]').val(index);
                });
            }
        });
    }
    
    // Enhanced form validation
    $('form[id^="org360"]').on('submit', function(e) {
        var form = $(this);
        var isValid = true;
        
        // Check required fields
        form.find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('error-field');
            } else {
                $(this).removeClass('error-field');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert(org360_admin.strings.required_fields || 'Please fill in all required fields.');
            
            // Scroll to first error
            var firstError = form.find('.error-field').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }
    });
    
    // Remove error class on input
    $('[required]').on('input change', function() {
        $(this).removeClass('error-field');
    });
    
    // Confirm delete actions
    $('a[href*="action=delete"]').on('click', function(e) {
        if (!confirm(org360_admin.strings.confirm_delete || 'Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
    
    // Auto-save draft (optional feature)
    var autoSaveTimer;
    var autoSaveInterval = 60000; // 1 minute
    
    function autoSaveDraft() {
        var form = $('#org360-assessment-form');
        if (form.length && form.data('autosave') === true) {
            var formData = form.serialize();
            
            $.ajax({
                url: org360_admin.ajax_url,
                type: 'POST',
                data: formData + '&action=org360_autosave_assessment&nonce=' + org360_admin.nonce,
                success: function(response) {
                    if (response.success) {
                        $('.autosave-indicator').text(org360_admin.strings.draft_saved || 'Draft saved').fadeIn().delay(2000).fadeOut();
                    }
                }
            });
        }
    }
    
    // Start auto-save timer if enabled
    if ($('#org360-assessment-form').data('autosave') === true) {
        autoSaveTimer = setInterval(autoSaveDraft, autoSaveInterval);
    }
    
    // Clear auto-save timer on form submit
    $('#org360-assessment-form').on('submit', function() {
        if (autoSaveTimer) {
            clearInterval(autoSaveTimer);
        }
    });
    
    // Character counter for textareas
    $('textarea[maxlength]').each(function() {
        var textarea = $(this);
        var maxLength = textarea.attr('maxlength');
        var counter = $('<div class="char-counter"></div>');
        textarea.after(counter);
        
        function updateCounter() {
            var remaining = maxLength - textarea.val().length;
            counter.text(remaining + ' characters remaining');
        }
        
        textarea.on('input', updateCounter);
        updateCounter();
    });
    
    // Tooltips (if available)
    if ($.fn.tooltip) {
        $('[data-tooltip]').tooltip();
    }
    
    // Smooth scroll to anchors
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
});