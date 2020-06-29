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
            form.submit()
        }
    });
}

const checkboxes = document.querySelectorAll('#dataTable input[type=checkbox]');

function checkAll() {
    checkboxes.forEach(input => input.checked = true)
    allowButton(true)
}

function unCheckAll() {
    checkboxes.forEach(input => input.checked = false)
    allowButton(false)
}

checkboxes.forEach(input => input.addEventListener('change', function (event) {
    checkedCheckboxes = document.querySelectorAll('#dataTable input:checked')
    if (checkedCheckboxes.length === 0) {
        allowButton(false)
    } else {
        allowButton(true)
    }
}))

function allowButton(action) {
    deleteBtn = document.getElementById('__delete-many-btn');
    if (action) {
        if (deleteBtn.hasAttribute('disabled')) {
            deleteBtn.removeAttribute('disabled')
        }
    } else {
        deleteBtn.setAttribute('disabled', true)
    }
}

