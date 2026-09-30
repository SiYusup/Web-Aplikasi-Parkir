</main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Konfirmasi global: pakai atribut data-confirm pada <form> atau <a>.
        // Contoh: <form data-confirm="Yakin?"> / <a href="..." data-confirm="Yakin?">
        // Placeholder {plat} pada form parkir-masuk diisi otomatis dari input nomor_plat.
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-confirm') || form.dataset.confirmed === '1') return;
            e.preventDefault();
            var message = form.getAttribute('data-confirm') || 'Yakin ingin melanjutkan?';
            if (message.indexOf('{plat}') !== -1) {
                var platInput = form.querySelector('input[name="nomor_plat"]');
                var plat = platInput && platInput.value.trim() !== '' ? platInput.value.trim().toUpperCase() : 'tersebut';
                message = message.replace('{plat}', plat);
            }
            Swal.fire({
                title: 'Konfirmasi',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        }, true);

        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[data-confirm]');
            if (!link || link.dataset.confirmed === '1') return;
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: link.getAttribute('data-confirm') || 'Yakin ingin melanjutkan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    link.dataset.confirmed = '1';
                    window.location.href = link.href;
                }
            });
        }, true);

        // Popup otomatis untuk flash message (success/error) setelah redirect.
        (function () {
            var flashEl = document.querySelector('[data-flash-message]');
            if (!flashEl) return;
            var type = flashEl.getAttribute('data-flash-type') || 'info';
            var icon = ['success', 'error', 'warning', 'info', 'question'].indexOf(type) !== -1 ? type : 'info';
            Swal.fire({
                icon: icon,
                title: type.charAt(0).toUpperCase() + type.slice(1),
                text: flashEl.getAttribute('data-flash-message'),
                timer: 3500,
                showConfirmButton: false
            });
        })();
    </script>
</body>

</html>