Tabel users (Hak Akses)

id (INT, PK, Auto Increment)

username (VARCHAR 50, Unique)

password (VARCHAR 255)

role (ENUM: 'admin', 'mahasiswa')

created_at (TIMESTAMP)

Tabel prodi (Program Studi)

id (INT, PK, Auto Increment)

nama_prodi (VARCHAR 100)

fakultas (VARCHAR 100)

Tabel mahasiswa (Biodata Utama)

id (INT, PK, Auto Increment)

user_id (INT, FK -> users.id, Nullable)

id_prodi (INT, FK -> prodi.id)

nim (VARCHAR 20, Unique)

nama (VARCHAR 150)

jenis_kelamin (ENUM: 'L', 'P')

email (VARCHAR 100)

no_hp (VARCHAR 20)

alamat (TEXT)

latitude (DECIMAL 10,8, Nullable)

longitude (DECIMAL 11,8, Nullable)

angkatan (YEAR)

status_akademik (ENUM: 'Aktif', 'Cuti', 'Lulus', 'DO')

dpa (VARCHAR 150)

foto (VARCHAR 255)

dokumen (VARCHAR 255, Nullable)

is_deleted (TINYINT 1, Default 0)

deleted_at (TIMESTAMP, Nullable)

Tabel audit_logs (Riwayat Aktivitas)

id (INT, PK)

user_id (INT, FK -> users.id)

action (VARCHAR 50 - misal: 'CREATE', 'UPDATE', 'SOFT_DELETE')

table_name (VARCHAR 50)

record_id (INT)

created_at (TIMESTAMP)