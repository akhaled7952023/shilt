function limitTextById(inputId, maxLength) {
    const inputElement = document.getElementById(inputId);

    if (inputElement && inputElement.value.length > maxLength) {
        inputElement.value = inputElement.value.substring(0, maxLength);
    }
}
