# Ruang Kelapa

Etalase toko kerajinan kelapa sederhana dengan alur belanja dari katalog hingga riwayat pesanan.

## Menjalankan aplikasi

1. Pastikan PHP 8.1 atau lebih baru tersedia.
2. Dari folder proyek, jalankan `php -S localhost:8000`.
3. Buka `http://localhost:8000` di browser.
4. File JSON penyimpanan dibuat otomatis pada folder privat di direktori temporary sistem. Pastikan PHP memiliki izin tulis ke sana.

## Alur fitur

- Pengunjung dapat mencari dan menyaring katalog, melihat detail produk, dan menyimpan produk ke keranjang.
- Pendaftaran dan login membuat akun lokal. Keranjang pengunjung digabungkan dengan keranjang akun saat login.
- Checkout menyimpan alamat, metode pembayaran, dan pesanan. Data pesanan dapat dilihat dan pesanan yang belum diproses dapat dibatalkan.
- Profil dapat digunakan untuk mengelola nama, nomor telepon, dan alamat.

## Catatan

Data akun, keranjang, dan pesanan disimpan sebagai JSON lokal di luar document root. Atur environment variable `RUANG_KELAPA_DATA_DIR` ke folder privat yang persisten untuk deployment. Pembayaran transfer dan bayar di tempat hanya simulasi; aplikasi belum terhubung ke gateway pembayaran, layanan email, maupun sistem pengelolaan stok/pesanan penjual. Jangan gunakan data produksi tanpa database, backup, dan konfigurasi server yang sesuai.
