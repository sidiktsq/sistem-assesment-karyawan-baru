# 📋 Panduan Deployment Lengkap - Sistem Assessment

Dokumen ini berisi panduan step-by-step untuk deploy aplikasi ke **Railway (Backend)** dan **Netlify (Frontend)**.

---

## **🔍 Ringkasan Arsitektur**

```
Aplikasi: sistem-assesment-karyawan-baru

├── 📦 BACKEND (Laravel 12 + Filament)
│   ├── Location: Root folder
│   ├── APIs: /api/* endpoints
│   ├── Admin Panel: /admin (Filament)
│   ├── Reviewer Panel: /reviewer (Livewire/Custom)
│   └── Deploy Target: Railway.app
│
└── 🎨 FRONTEND (Vue 3 + Vite)
    ├── Location: /frontend folder
    ├── Pages: Exam widget, hasil assessment
    └── Deploy Target: Netlify
```

---

## **📝 TAHAP 1: Persiapan (GitHub & Environment)**

### ✅ Step 1.1: Push Project ke GitHub
```bash
# Jika belum di-initialize
git init
git add .
git commit -m "Initial commit - sistem assessment karyawan"
git branch -M main
git remote add origin https://github.com/USERNAME/sistem-assesment-karyawan-baru.git
git push -u origin main
```

### ✅ Step 1.2: Buat File Environment (.env)

#### a) File `.env.example` untuk Backend (dokumentasi)
**Lokasi:** `/env.example`

```env
# APP SETTINGS
APP_NAME="Sistem Assessment"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-backend.railway.app

# DATABASE (Railway MySQL)
DB_CONNECTION=mysql
DB_HOST=your-railway-mysql-host.railway.internal
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-password

# MAIL (Optional - gunakan mailgun/mailtrap)
MAIL_MAILER=mailgun
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Assessment System"

# CORS - untuk frontend Netlify
FRONTEND_URL=https://your-frontend.netlify.app

# Filament
FILAMENT_AUTHENTICATION_GUARD=web

# Optional: File storage
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

#### b) File `.env.example` untuk Frontend
**Lokasi:** `/frontend/.env.example`

```env
VITE_API_URL=https://your-backend.railway.app/api
VITE_APP_URL=https://your-frontend.netlify.app
```

### ✅ Step 1.3: Tambahkan File Konfigurasi Railway

**Lokasi:** `/railway.toml`

```toml
[build]
builder = "nixpacks"

[deploy]
startCommand = "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT"
restartPolicyType = "on_failure"
restartPolicyMaxRetries = 3
```

### ✅ Step 1.4: Konfigurasi CORS di Laravel

**File:** `app/Http/Middleware/HandleCors.php` (buat jika belum ada)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    public function handle(Request $request, Closure $next): Response
    {
        $frontendUrl = config('app.frontend_url');

        return $next($request)
            ->header('Access-Control-Allow-Origin', $frontendUrl)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
```

**Register di:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(append: [
        \App\Http\Middleware\HandleCors::class,
    ]);
})
```

**Update config:** `config/app.php`

```php
'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
```

---

## **🚀 TAHAP 2: Deploy Backend ke Railway**

### ✅ Step 2.1: Setup Railway Project (via UI)

1. Buka https://railway.app
2. Login atau buat akun
3. Klik "**New Project**" → "**Deploy from GitHub**"
4. Authorisasi GitHub akun Anda
5. Pilih repository: `sistem-assesment-karyawan-baru`
6. Railway akan detect sebagai PHP project

### ✅ Step 2.2: Setup Database MySQL di Railway

1. Di Railway Dashboard → "**New Service**" → "**MySQL**"
2. Tunggu database selesai dibuat
3. Copy connection details ke **Variables**

### ✅ Step 2.3: Setup Environment Variables di Railway

Di Railway Dashboard, masuk **Variables** section tambahkan:

```
APP_KEY=          # Isi nanti dari: php artisan key:generate --show
APP_URL=          # https://[PROJECT-NAME].up.railway.app
DB_HOST=          # Dari MySQL service
DB_PORT=          # 3306
DB_DATABASE=      # railway
DB_USERNAME=      # root
DB_PASSWORD=      #Dari MySQL
FRONTEND_URL=     # https://[your-netlify].netlify.app
APP_DEBUG=false
APP_ENV=production
```

### ✅ Step 2.4: Generate APP_KEY Locally

```bash
php artisan key:generate --show
# Copy hasilnya ke Railway APP_KEY variable
```

### ✅ Step 2.5: Deploy Pertama Kali

```bash
# Push changes ke main branch
git add .
git commit -m "Add Railway config dan CORS setup"
git push origin main

# Railway akan auto-deploy dari GitHub
# Monitor di Railway Dashboard
```

### ✅ Step 2.6: Run Database Migrations

Option A: Via Railway CLI
```bash
railway run php artisan migrate --force
```

Option B: Otomatis (sudah di railway.toml)

---

## **🎨 TAHAP 3: Deploy Frontend ke Netlify**

### ✅ Step 3.1: Setup Netlify Project

1. Buka https://netlify.com
2. Login dengan GitHub
3. Klik "**Add new site**" → "**Import an existing project**"
4. Connect ke GitHub repository Anda
5. Pilih repository `sistem-assesment-karyawan-baru`

### ✅ Step 3.2: Konfigurasi Build Settings

Pada step konfigurasi:

**Build Settings:**
- **Base directory:** `frontend`
- **Build command:** `npm run build`
- **Publish directory:** `frontend/dist`

**Environment Variables:**
- `VITE_API_URL` = `https://[railway-app-name].up.railway.app/api`
- `VITE_APP_URL` = `https://[your-netlify-site].netlify.app`

### ✅ Step 3.3: Konfigurasi Redirects (untuk SPA Routing)

**File:** `/frontend/netlify.toml`

```toml
[[redirects]]
from = "/*"
to = "/index.html"
status = 200

# Cache settings untuk assets
[[headers]]
[headers.values]
cache-control = "max-age=31536000, immutable"

[headers.values.headers]
Cache-Control = "max-age=3600"
```

### ✅ Step 3.4: Build Local untuk Testing

```bash
cd frontend
npm install
npm run build
npm run preview
# Akses di http://localhost:4173
```

### ✅ Step 3.5: Deploy Frontend

```bash
git add .
git commit -m "Add Netlify config dan env setup"
git push origin main

# Netlify akan auto-deploy dari GitHub
```

---

## **⚙️ TAHAP 4: Konfigurasi API & CORS**

### ✅ Step 4.1: Update API Base URL di Frontend

**File:** `frontend/src/api.js` (atau sesuai struktur Anda)

```javascript
import axios from 'axios'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Tambah token jika ada
export const setAuthToken = (token) => {
  if (token) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
}

export default apiClient
```

### ✅ Step 4.2: Update Routes API di Backend

**File:** `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Candidate routes
Route::post('/assessments/start', [\App\Http\Controllers\CandidateController::class, 'startAssessment']);
Route::post('/assessments/submit', [\App\Http\Controllers\CandidateController::class, 'submitAssessment']);
Route::get('/assessments/{id}', [\App\Http\Controllers\CandidateController::class, 'getAssessment']);

// Reviewer routes (protected)
Route::middleware(['auth:sanctum', 'role:reviewer'])->group(function () {
    Route::get('/reviews', [\App\Http\Controllers\ReviewerController::class, 'index']);
    Route::post('/reviews/{id}', [\App\Http\Controllers\ReviewerController::class, 'store']);
});
```

### ✅ Step 4.3: Test CORS

```bash
# Test dari browser console atau curl
curl -H "Origin: https://your-netlify.netlify.app" \
     -H "Access-Control-Request-Method: POST" \
     -X OPTIONS \
     https://your-backend.up.railway.app/api/assessments/start
```

---

## **✅ TAHAP 5: Testing & Verification**

### Checklist Deployment:

- [ ] Backend accessible: `https://[app].up.railway.app`
- [ ] Admin panel: `https://[app].up.railway.app/admin`
- [ ] API endpoints: `https://[app].up.railway.app/api/*`
- [ ] Frontend deployed: `https://[site].netlify.app`
- [ ] API calls work dari frontend (no CORS errors)
- [ ] Database migrations berjalan
- [ ] Authentication bekerja (Sanctum tokens)
- [ ] Filament admin accessible
- [ ] File uploads work (jika pakai)

### Test dari Browser Console:

```javascript
// Test API connection
fetch('https://your-backend.up.railway.app/api/user', {
  method: 'GET',
  credentials: 'include',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  }
})
.then(r => r.json())
.then(d => console.log(d))
.catch(e => console.error('API Error:', e))
```

---

## **🐛 Troubleshooting**

| Masalah | Solusi |
|---------|--------|
| **CORS Error** | Check `FRONTEND_URL` di Rails, pastikan middleware active |
| **Database connection failed** | Verify `DB_HOST`, port, credentials di Railway |
| **API 404** | Check route definitions di `routes/api.php` |
| **Migrations failed** | SSH ke Railway container, run manual: `railway run php artisan migrate` |
| **Frontend blank page** | Check build command, verify `vite.config.js`, lihat Netlify build logs |
| **Static assets 404** | Verify `dist/` folder exists, check publish directory di Netlify |

---

## **📞 Contacts & Resources**

- Railway Docs: https://docs.railway.app
- Netlify Docs: https://docs.netlify.com
- Laravel Deployment: https://laravel.com/docs/12/deployment
- Filament Deployment: https://filamentphp.com/docs/3.x/admin/installation

---

**Next Steps:**
1. Mulai dari TAHAP 1 hingga 5 secara urut
2. Jika ada error, refer ke Troubleshooting section
3. Test setiap tahap sebelum lanjut ke tahap berikutnya

Good luck! 🚀
