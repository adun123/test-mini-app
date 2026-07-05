# Mini Attendance App & HR Dashboard

Laravel + MySQL + Blade mini project untuk attendance employee dan dashboard HR.

## Fitur

- Register dan login admin/employee
- Role admin dan employee
- CRUD employee untuk admin/HR
- CRUD department dan position untuk dropdown employee
- Work schedule default dan override per department untuk hitung telat
- Check-in dan check-out employee
- Attendance history dengan filter employee/date
- HR dashboard summary
- Employee dashboard
- Late accumulation
- Location area detection sederhana memakai latitude/longitude dan radius lokasi
- Link Google Maps dari koordinat GPS check-in
- Attendance correction request untuk koreksi data attendance
- API utama sesuai brief
- Non-DB log file: `storage/app/attendance_events.jsonl`

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Sesuaikan database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_attendance
DB_USERNAME=root
DB_PASSWORD=
```

## Akun Demo

```txt
Admin:
email: admin@example.com
password: password

Employee:
email: employee@example.com
password: password
```

## API

```http
POST /api/attendance/check-in
POST /api/attendance/check-out
GET  /api/attendance/history
GET  /api/hr/dashboard-summary
```

Contoh body check-in/check-out:

```json
{
  "employee_id": 1,
  "timestamp": "2026-07-03 08:00:00",
  "location": "Head Office",
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

Filter history:

```http
GET /api/attendance/history?employee_id=1&date=2026-07-03
```
