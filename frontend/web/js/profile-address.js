$(function () {

  // ===== SAME AS HOME BILLING =====
  $('#sameAsHomeBilling').on('change', function () {
    let form = $('#billing-form');

    if (this.checked) {
      form.find('#address-address').val($('#user-address').val());
      form.find('#address-city').val($('#user-city').val());
      form.find('#address-state').val($('#user-state').val());
      form.find('#address-pincode').val($('#user-pincode').val());
    } else {
      form.find('input[type="text"]').val('');
    }
  });

  // ===== SAME AS HOME SHIPPING =====
  $('#sameAsHomeShipping').on('change', function () {
    let form = $('#shipping-form');

    if (this.checked) {
      form.find('#address-address').val($('#user-address').val());
      form.find('#address-city').val($('#user-city').val());
      form.find('#address-state').val($('#user-state').val());
      form.find('#address-pincode').val($('#user-pincode').val());
    } else {
      form.find('input[type="text"]').val('');
    }
  });

});
