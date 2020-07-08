var tbodyQuerySelector = 'table[id=dataTable] tbody';

var tbody = document.querySelector(tbodyQuerySelector),
    tbodyElements = document.querySelectorAll(tbodyQuerySelector + ' tr'),
    checkedTableElements = [];

tbody.onclick = function (event) {
    let target = event.target.closest('tr')
        // id = target.getAttribute('data-car-id')

    if (target) {
        checkChecked(target)
    }

    highlight(target)
}

function highlight(target, toggle = true, method = false) {
    let classes = ['bg-gradient-primary', 'text-white']

    if (toggle) {
        classes.forEach(value => {
            target.classList.toggle(value)
        })
    } else {
        if (method) {
            classes.forEach(value => {
                target.classList.add(value)
            })
        } else {
            classes.forEach(value => {
                target.classList.remove(value)
            })
        }
    }
}

function checkChecked(element, check = true) {
    if (check) {
        if (checkedTableElements.includes(element)) {
            let index = checkedTableElements.indexOf(element)

            checkedTableElements.splice(index, 1)
        } else {
            checkedTableElements.push(element)
            allowButton(true)
        }
    }

    if (checkedTableElements.length === 0) {
        allowButton(false)
    }
}

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

function checkAll() {
    checkedTableElements = []
    tbodyElements.forEach(value => {
        checkChecked(value)
        highlight(value, false, true)
    })
}

function unCheckAll() {
    checkedTableElements = []
    tbodyElements.forEach(value => {
        checkChecked(value, false)
        highlight(value, false, false)
    })
}

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
            actionValues.value = getCarIDs()

            form.submit()
        }
    });
}

function getCarIDs() {
    let carIDs = [];
    checkedTableElements.forEach(item => {
        carIDs.push(item.getAttribute('data-car-id'))
    })

    return carIDs;
}

