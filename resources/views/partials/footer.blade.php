<footer class="site-footer">
    <div class="site-footer__inner panel">
        <div class="footer-block">
            <span class="footer-kicker">Gudang Sekolah</span>
            <strong>Aplikasi inventaris berbasis Blade.</strong>
            <p>
                Layout sekarang memakai partial Blade terpisah untuk `header`, `menu`, dan `footer`
                supaya lebih gampang dirawat saat halaman inventaris bertambah.
            </p>
        </div>

        <div class="footer-actions">
            <div class="footer-note">
                Jalankan <code>php artisan serve</code> lalu buka <strong>http://127.0.0.1:8000</strong>
            </div>
            <a class="button-secondary" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
        </div>
    </div>
</footer>
