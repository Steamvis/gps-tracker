function callConfirmModal(button) {
    let form = button.parentNode,
        title = button.getAttribute('data-title'),
        confirm = button.getAttribute('data-confirm'),
        cancel = button.getAttribute('data-cancel');
    Swal.fire({
        title: title,
        icon: 'warning',
        showCancelButton: true,
        focusCancel: true,
        confirmButtonColor: '#d33',
        cancelButtonText: cancel,
        confirmButtonText: confirm,
    }).then(function (request) {
        if (request.value) {
            let actionValues = document.querySelector('input[name="action[]"]')

            if (getCarIDs().length !== 0) {
                actionValues.value = getCarIDs()
            }

            form.submit()
        }
    });
}
