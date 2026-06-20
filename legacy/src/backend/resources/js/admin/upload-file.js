var reader = new FileReader(),
    imgBlock = document.querySelector('div.__image-file'),
    imgElement = imgBlock.querySelector('img'),
    removeImgElement = imgBlock.querySelector('span'),
    label = document.querySelector('label.custom-file-label'),
    fileTypes = [
        'image/jpeg',
        'image/pjpeg',
        'image/png'
    ];


changeLabelName(label.getAttribute('data-translate'))

function handleChange(input) {
    if (input.files.length === 0) {
        console.log('cancel');
        return;
    }

    let file = input.files[0];

    if (!isValidFile(file)) {
        return;
    }

    addFile(file);
    reader.onloadend = function () {
        imgElement.src = reader.result;
    }
}

// functions
if (removeImgElement.hasAttribute('data-car')) {
    removeImgElement.onclick = function (event) {
        let url = removeImgElement.getAttribute('action'),
            image = imgElement.src,
            title = removeImgElement.getAttribute('data-title'),
            confirm = removeImgElement.getAttribute('data-confirm'),
            cancel = removeImgElement.getAttribute('data-cancel'),
            car = removeImgElement.getAttribute('data-car'),
            token = removeImgElement.getAttribute('data-token');

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
                jQuery.ajax({
                    url: url,
                    data: {
                        _token: token,
                        image: image,
                        car: car
                    },
                    method: 'DELETE',
                    success: function (data) {
                        if (data) {
                            removeFile()
                        }
                    }
                })
            }
        })
    }
} else {
    removeImgElement.onclick = function (event) {
        removeFile()
    };
}


function addFile(file) {
    imgBlock.classList.remove('d-none')
    imgBlock.parentNode.parentNode.style.height = '250px'
    reader.readAsDataURL(file);
    changeLabelName(file.name)
}

function removeFile() {
    imgBlock.classList.add('d-none')
    imgBlock.parentNode.parentNode.style.height = '38px'
    imgElement.src = '';
    changeLabelName(label.getAttribute('data-translate'))
}

function removeFileFromStorage() {
    callConfirmModal(removeImgElement)
}

function changeLabelName(name) {
    label.textContent = name;
}

function isValidFile(file) {
    for (var i = 0; i < fileTypes.length; i++) {
        if (file.type === fileTypes[i]) {
            return true;
        }
    }

    return false;
}

