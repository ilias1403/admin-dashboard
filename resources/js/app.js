import './bootstrap';

$(document).ready(function() {

    // Check if the toggle is active
    if (localStorage.getItem('nav-toggle') === 'true') {
        $('#main-wrapper').addClass('toggled');
    } else {
        $('#main-wrapper').removeClass('toggled');
    }
});

$('#nav-toggle').click(function() {
    if ($('#main-wrapper').hasClass('toggled')) { // true, menu is open
        localStorage.setItem('nav-toggle', 'true');
    } else { // false, menu is closed
        localStorage.setItem('nav-toggle', 'false');
    }
});

function swalOne(title, text) {
    Swal.fire({
        title: title,
        html: text,
        icon: 'info',
        confirmButtonText: 'OK'
    });
}


