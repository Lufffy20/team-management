$(document).on('click', '.btn-delete-address', function () {

    if (!confirm('Are you sure you want to delete this address?')) {
        return;
    }

    let btn = $(this);
    let id = btn.data('id');

    $.ajax({
        url: window.addressDeleteUrl,  
        type: 'POST',
        data: {
            id: id
        },
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        success: function () {
            btn.closest('tr').fadeOut(300, function () {
                $(this).remove();
            });
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert('Failed to delete address');
        }
    });
});


$(document).on('click', '#address-grid .pagination a', function (e) {

    e.preventDefault();

    let url = $(this).attr('href');
    if (!url) return;

    $.get(url, function (data) {

        let newGrid = $(data).find('#address-grid').html();
        $('#address-grid').html(newGrid);

        $('html, body').animate({
            scrollTop: $('#address-grid').offset().top - 100
        }, 300);
    });
});