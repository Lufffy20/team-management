/******************************************************
 🔥 COMMENT ACTIONS — AJAX Implementation
******************************************************/

$(document).on('submit', '#commentForm', function (e) {
    e.preventDefault();

    let form = $(this);
    let comment = $('#commentInput').val().trim();
    let url = form.attr('action');

    if (!comment) return;

    // Loading state
    let btn = $('#postCommentBtn');
    let originalText = btn.text();
    btn.prop('disabled', true).text('Posting...');

    $.ajax({
        url: url,
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                // Remove "No comments" text if exists
                $('#commentList .no-comment').remove();

                // Prepend new comment to list
                $('#commentList').prepend(res.html);

                // Clear input
                $('#commentInput').val('');

                if (typeof showToast === 'function') {
                    showToast('Comment added');
                }
            } else {
                alert('Failed to add comment');
            }
        },
        error: function () {
            alert('AJAX error while adding comment');
        },
        complete: function () {
            btn.prop('disabled', false).text(originalText);
        }
    });
});
