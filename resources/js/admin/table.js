var tbodyQuerySelector = 'table[id=dataTable] tbody',
    tbody = document.querySelector(tbodyQuerySelector),
    tbodyElements = document.querySelectorAll(tbodyQuerySelector + ' tr'),
    checkedTableElements = [];

tbody.onclick = function (event) {
    let target = event.target.closest('tr')

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


function getCarIDs() {
    let carIDs = [];
    checkedTableElements.forEach(item => {
        carIDs.push(item.getAttribute('data-car-id'))
    })

    return carIDs;
}

