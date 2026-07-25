document.addEventListener('DOMContentLoaded', function () {
    var checkbox = document.getElementById('alamat-sama');
    var wrap = document.getElementById('wrap-s-alamat');

    if (!checkbox || !wrap) {
        return;
    }

    checkbox.addEventListener('change', function () {
        wrap.style.display = checkbox.checked ? 'none' : '';
    });
});
