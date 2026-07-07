function setStatus(value) {
    const statusInput = document.getElementById('status-input');
    statusInput.value = value;

    const buttons = document.querySelectorAll('#status-switch a');
    buttons.forEach(button => {
        const buttonStatus = button.getAttribute('data-status');

        if (buttonStatus == value) {
            button.classList.add('active');
            if (buttonStatus == "0") {
                button.classList.add('btn-danger');
                button.classList.remove('btn-default', 'btn-success');
                document.querySelector('[data-status="1"]').style.border = '1px solid gray';
                button.style.border = '';
            } else {
                button.classList.add('btn-success');
                button.classList.remove('btn-default', 'btn-danger');
                document.querySelector('[data-status="0"]').style.border = '1px solid gray';
                button.style.border = '';
            }
        } else {
            button.classList.remove('active');
            button.classList.add('btn-default');
            button.classList.remove('btn-danger', 'btn-success');
           // button.style.border = '';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const defaultStatus = document.getElementById('status-input').value;
    setStatus(defaultStatus);
});
