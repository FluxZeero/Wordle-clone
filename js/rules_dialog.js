document.addEventListener('DOMContentLoaded', function() {
    const rulebtn = document.getElementById('regole');
    const dialog = document.getElementById('regole-dialog');
    const close = document.getElementById('close-dialog');


    rulebtn.addEventListener('click', function(e) {
        dialog.classList.add('active');
    });

    close.addEventListener('click', function() {
        dialog.classList.remove('active');
    });


    dialog.addEventListener('click', function(e) {
        if (e.target === dialog) {
            dialog.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && dialog.classList.contains('active')) {
            dialog.classList.remove('active');
        }
    });
});
