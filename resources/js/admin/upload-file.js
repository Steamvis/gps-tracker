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
removeImgElement.onclick = function (event) {
    removeFile()
};

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
