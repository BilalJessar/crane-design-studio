import Swal from 'sweetalert2';

export function notify(icon, message) {
    return Swal.fire({
        toast: true, position: 'bottom-end', icon, titleText: message,
        showConfirmButton: false, showCloseButton: true,
        timer: icon === 'success' ? 3500 : undefined,
    });
}

export function confirmAction(titleText, text, confirmButtonText) {
    return Swal.fire({
        icon: 'warning', titleText, text, confirmButtonText,
        showCancelButton: true, focusCancel: true,
        confirmButtonColor: '#b91c1c', cancelButtonColor: '#475569',
        customClass: { htmlContainer: 'break-words' },
    });
}
