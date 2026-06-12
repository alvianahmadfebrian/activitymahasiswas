# Update UI & Alur CampusHub

Perubahan utama:
- Merombak layout utama menjadi sidebar profesional, topbar sticky, mobile drawer, dan kartu glassmorphism.
- Merapikan halaman Login dan Register agar lebih modern dan jelas.
- Merombak Dashboard menjadi ringkasan aktivitas, progress tugas, statistik, tugas terbaru, dan riwayat terbaru.
- Merombak Kelola Tugas menjadi form input di kiri dan daftar tugas berbasis kartu, lengkap dengan pencarian, badge status, update status cepat, lampiran, dan hapus.
- Merombak Drive menjadi file manager berbasis kartu, upload file, buat folder, pencarian, navigasi folder, dan statistik file/folder.
- Merombak Diskusi menjadi alur percakapan profesional: daftar room/user, pencarian, modal group, empty state, dan tampilan chat yang lebih rapi.
- Merombak Riwayat Aktivitas menjadi timeline kartu dengan pencarian.

Catatan teknis:
- Tidak mengubah route inti dan controller utama, sehingga alur backend lama tetap dipakai.
- Chatbot tetap menggunakan layout khusus bawaan agar fungsi chat dan script lama tidak rusak.
- Validasi syntax Blade dasar sudah dicek dengan `php -l`.
- `php artisan route:list` berhasil menampilkan route. `php artisan view:clear` di environment ini gagal karena ekstensi PHP DOMDocument belum aktif, bukan karena perubahan kode view.
