# 🚀 Panduan Step-by-Step Deployment Lengkap

> **Tanggal: April 2026**  
> **Project:** Sistem Assessment Karyawan Baru  
> **Target:** Railway (Backend) + Netlify (Frontend)

---

## **📌 Checklist Pre-Deployment**

Pastikan sebelum mulai:

- [ ] Project di GitHub ✅
- [ ] PHP 8.2+ installed locally
- [ ] Node.js 20+ & npm installed
- [ ] Railway dan Netlify akun sudah dibuat
- [ ] Database MySQL siap (local atau Railway)

---

## **TAHAP 1️⃣: GITHUB SETUP & LOCAL TESTING**

### Step 1.1: Initialize Git (Jika belum)

```bash
# Di root project folder
cd c:\Users\Hype\Documents\sistem-assesment-karyawan-baru

# Initialize git
git init
git add .
git commit -m "Initial commit - sistem assessment karyawan"
git branch -M main

# Add remote
git remote add origin https://github.com/YOUR_USERNAME/sistem-assesment-karyawan-baru.git
git push -u origin main
```

✅ **Verifikasi:** Cek di GitHub apakah repo sudah ada files-nya

---

### Step 1.2: Setup Local Environment

```bash
# Copy .env.example ke .env (sudah ada di root)
cp .env.example .env

# Edit .env untuk local development
# Pastikan:
# - APP_KEY kosong (akan di-generate)
# - DB_HOST=127.0.0.1 (lokal)
# - DB_DATABASE=assessment_db
# - FRONTEND_URL=http://localhost:5173
```

### Step 1.3: Generate Laravel App Key

```bash
php artisan key:generate

# Output akan menampilkan key, copy ke .env
# APP_KEY=base64:xxxxx...
```

### Step 1.4: Setup Database Lokal

**Option A: MySQL via XAMPP/WAMP/Docker**

```bash
# Create database
mysql -u root -p
> CREATE DATABASE assessment_db;
> EXIT;

# Run migrations
php artisan migrate --seed
```

**Option B: SQLite (Cepat untuk testing)**

```bash
# Update .env: DB_CONNECTION=sqlite
# Buat file database
touch database/database.sqlite

php artisan migrate --seed
```

### Step 1.5: Test Backend Lokal

```bash
# Terminal 1 - Laravel Server
php artisan serve
# Akses: http://localhost:8000

# Terminal 2 - Vite Dev Server
npm run dev
# Akses: http://localhost:5173
```

✅ **Verifikasi:**
- Admin Panel: http://localhost:8000/admin (harus accessible)
- Frontend: http://localhost:5173 (harus tidak error di console)
- API test: `curl http://localhost:8000/api/user`

---

### Step 1.6: Test Frontend Build Lokal

```bash
cd frontend

# Install dependencies
npm install

# Test development mode
npm run dev
# Akses http://localhost:5173

# Test production build
npm run build
npm run preview
# Akses http://localhost:4173
```

✅ **Verifikasi:**
- Frontend runs tanpa error
- Console browser tidak ada CORS error
- Dist folder ada files-nya

### Step 1.7: Push ke GitHub

```bash
# Jika ada file baru (.env.example, railway.toml, dll)
git add .
git commit -m "Add deployment config files"
git push origin main
```

✅ **Verifikasi:** Semua file ada di GitHub repo

---

## **TAHAP 2️⃣: SETUP RAILWAY (Backend Deployment)**

### Step 2.1: Buat Railway Project

1. **Login ke Railway**
   - Buka https://railway.app
   - Login dengan GitHub account
   
2. **Create New Project**
   - Klik "**New Project**"
   - Pilih "**Deploy from GitHub**"
   - Authorize Railway access ke GitHub
   - Pilih repo: `sistem-assesment-karyawan-baru`

3. **Railway akan detect:**
   - Framework: Laravel ✅
   - Language: PHP ✅

### Step 2.2: Tambah MySQL Database di Railway

1. **Di Railway Dashboard:**
   - Klik "**+ Add New Service**"
   - Pilih "**Database**" → "**MySQL**"
   - Tunggu hingga ready (hijau)

2. **Copy Connection Details:**
   - Klik MySQL service
   - Buka tab "**Connect**"
   - Copy: Host, Port, Database, Username, Password

Expected:
```
MYSQL_URL=mysql://root:password@host.railway.internal:3306/railway
DB_HOST=host.railway.internal
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=xxxxx
```

### Step 2.3: Setup Environment Variables di Railway

1. **Di Railway Project → Settings → Variables:**

```
APP_NAME=Sistem Assessment
APP_ENV=production
APP_DEBUG=false
APP_KEY=[GENERATE LOCALLY - see step 2.4]
APP_URL=https://yourproject.up.railway.app

DB_CONNECTION=mysql
DB_HOST=[dari MySQL service]
DB_PORT=3306
DB_DATABASE=[dari MySQL service]
DB_USERNAME=[dari MySQL service]
DB_PASSWORD=[dari MySQL service]

FRONTEND_URL=https://yourfrontend.netlify.app

LOG_CHANNEL=stack
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

FILAMENT_AUTHENTICATION_GUARD=web
```

### Step 2.4: Generate APP_KEY di Lokal

```bash
# Di lokal project
php artisan key:generate --show

# Output: base64:xxxxxxxxxxxxx
# Copy value tersebut ke Railway APP_KEY variable
```

### Step 2.5: Set Start Command di Railway

1. **Di Railway → Settings → Deploy:**

```
Start Command: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

atau biarkan Railway gunakan default dari `railway.toml` (sudah ada)

### Step 2.6: Verifikasi Config Files di Root

Pastikan ada files ini di root repo:
- ✅ `railway.toml` (sudah dibuat)
- ✅ `.env.example` (sudah dibuat)
- ✅ `composer.json` (existing)
- ✅ `app/Http/Middleware/HandleCors.php` (sudah dibuat)

### Step 2.7: Trigger Deploy

```bash
# Push changes ke main (jika ada)
git add .
git commit -m "Final deployment config"
git push origin main

# Railway akan auto-deploy dari GitHub
# Monitor di Dashboard → "Deployments" tab
```

**Tunggu hingga deployment selesai (status "Success")**

### Step 2.8: Verify Backend Deployment

```bash
# Cek di Railway Dashboard
1. Klik Project name
2. Lihat "Environment" tab
3. Klik "View Logs" untuk lihat deployment process
4. Cari message: "Laravel development server is running"
```

URL Backend akan ada di Railway Dashboard:
```
https://yourproject-production.up.railway.app
```

### Step 2.9: Test Backend API Routes

```bash
# Test dari terminal lokal
BACKEND_URL="https://yourproject-production.up.railway.app"

# Test health check
curl $BACKEND_URL/up

# Test API endpoint
curl $BACKEND_URL/api/user

# Test CORS headers
curl -H "Origin: https://yourfrontend.netlify.app" \
     -H "Access-Control-Request-Method: GET" \
     -X OPTIONS $BACKEND_URL/api/user -v
```

✅ **Verifikasi:**
- Status 200 OK
- CORS headers muncul di response
- Database connected (check Filament admin panel)

---

## **TAHAP 3️⃣: SETUP NETLIFY (Frontend Deployment)**

### Step 3.1: Buat Netlify Project

1. **Login ke Netlify**
   - Buka https://netlify.com
   - Login dengan GitHub
   
2. **Add New Site**
   - Klik "**Add new site**"
   - Pilih "**Import an existing project**"
   - Authorize GitHub

3. **Select Repository**
   - Search: `sistem-assesment-karyawan-baru`
   - Pilih repo yang muncul

### Step 3.2: Configure Build Settings

Pada form configuration, isi:

```
Team:           Pilih team Anda
Site name:      sistem-assesment-frontend  (atau nama lain)
Branch:         main

Build Settings:
├─ Base directory:   frontend
├─ Build command:    npm run build
└─ Publish directory: frontend/dist

Environment Variables:
├─ VITE_API_URL    = https://yourproject.up.railway.app/api
├─ VITE_APP_URL    = https://sistem-assesment-frontend.netlify.app
└─ NODE_VERSION    = 20
```

### Step 3.3: Deploy Pertama Kali

Klik "**Deploy site**" - Netlify akan:
1. Clone repo
2. Build frontend (npm run build)
3. Deploy dist folder

**Tunggu hingga "Deploying" berubah "Published"**

Expected URL:
```
https://sistem-assesment-frontend.netlify.app
```

### Step 3.4: Verify Frontend Deployment

```bash
# Buka di browser
https://sistem-assesment-frontend.netlify.app

# Verifikasi:
✅ Frontend loads (no blank page)
✅ Vue app renders
✅ No console errors
✅ Network tab shows API calls ke backend
```

### Step 3.5: Update Environment Variables di Netlify

Jika perlu mengupdate API URL nanti:

1. **Netlify Dashboard → Site Settings → Environment**
2. Klik "**Edit variables**"
3. Update `VITE_API_URL` dengan backend URL yang tepat
4. Trigger rebuild: **Deploys → Trigger deploy → Deploy site**

---

## **TAHAP 4️⃣: TESTING & INTEGRATION**

### Step 4.1: Test API Connection dari Frontend

**Buka developer console (F12) di frontend Netlify:**

```javascript
// Test API call
fetch('https://yourproject.up.railway.app/api/user', {
  method: 'GET',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json'
  }
})
.then(r => r.json())
.then(d => console.log('API OK:', d))
.catch(e => console.error('API ERROR:', e))
```

**Expected output:**
```javascript
API OK: {id: 1, email: "admin@test.com", ...}
// atau
API ERROR: Unauthorized (jika belum login)
```

### Step 4.2: Test Login Flow

1. **Frontend:** Klik login
2. **Admin Panel:** Buka `https://yourproject.up.railway.app/admin`
3. **Verify:**
   - Login works ✅
   - Token stored di localStorage ✅
   - Can access protected routes ✅

### Step 4.3: Verify Database & Filament

Dari Filament admin panel check:
- [ ] Candidates list loads
- [ ] Assessments data shows
- [ ] Can create/edit/delete records
- [ ] File uploads work (jika ada)

### Step 4.4: Check CORS & Headers

Terminal check:
```bash
BACKEND_URL="https://yourproject.up.railway.app"

# Check CORS headers
curl -i -H "Origin: https://yourfrontend.netlify.app" \
        -H "Access-Control-Request-Method: POST" \
        -X OPTIONS $BACKEND_URL/api/assessments/submit

# Ensure response headers include:
# Access-Control-Allow-Origin: https://yourfrontend.netlify.app
# Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS
# Access-Control-Allow-Headers: Content-Type, Authorization, ...
```

---

## **TAHAP 5️⃣: TROUBLESHOOTING CHECKLIST**

### ❌ Problem: Blank page di Frontend Netlify

**Solutions:**
1. Check Netlify build logs
   - Netlify → Deploys → Latest deploy → **Deploy log**
   - Cari error messages
   
2. Verify publish directory
   ```bash
   # Lokal build test
   cd frontend
   npm run build
   ls dist/  # Harus ada files
   ```

3. Check `frontend/netlify.toml` redirects

### ❌ Problem: CORS Error di Frontend

**Solutions:**
1. Verify `FRONTEND_URL` di Railway:
   ```bash
   echo $FRONTEND_URL  # Harus exact match ke Netlify URL
   ```

2. Check `app/Http/Middleware/HandleCors.php`:
   ```php
   // Harus include Netlify URL
   $allowedOrigins = [
       'https://sistem-assesment-frontend.netlify.app',
       ...
   ]
   ```

3. Restart Railway deployment

### ❌ Problem: 404 API Not Found

**Solutions:**
1. Check routes di `routes/api.php`
2. Verify API base URL:
   ```javascript
   console.log(import.meta.env.VITE_API_URL)
   // Harus ada /api suffix
   ```

3. Check Filament routes conflict

### ❌ Problem: Database Connection Failed di Railway

**Solutions:**
1. Verify MySQL service status (hijau di Railway)
2. Test connection:
   ```bash
   railway run php artisan tinker
   > DB::connection()->getPDO()
   ```

3. Check firewall/security rules di Railway

### ❌ Problem: Deployment Stuck atau Timeout

**Solutions:**
1. Check Railway build logs untuk errors
2. Increase Railway compute
3. Optimize build (remove unused packages)

---

## **📋 Final Verification Checklist**

Sebelum announce deployment complete:

- [ ] **Backend (Railway)**
  - [ ] Dashboard accessible: `https://yourproject.up.railway.app/admin`
  - [ ] API responds: `/api/user` returns proper data
  - [ ] Database connected: Can see data di Filament
  - [ ] Logs clean (no critical errors)

- [ ] **Frontend (Netlify)**
  - [ ] Deploys successfully
  - [ ] No blank page
  - [ ] Vue app renders
  - [ ] Can make API calls (check Network tab)

- [ ] **Integration**
  - [ ] Login works end-to-end
  - [ ] Can submit assessment dari UI
  - [ ] Data saves ke database
  - [ ] Admin can review submissions
  - [ ] CORS headers correct

- [ ] **Domain Setup (Optional)**
  - [ ] Custom domain configured (jika pakai)
  - [ ] SSL certificate valid
  - [ ] Redirects working

---

## **🎯 Next Steps After Deployment**

1. **Monitor Logs:**
   - Railway → Logs tab
   - Netlify → Deploys tab

2. **Setup Auto-Deploys:**
   - Both Railway & Netlify auto-deploy dari main branch
   - Pastikan untuk test di staging dulu

3. **Backup Database:**
   - Railway MySQL → Export
   - Schedule automatic backups

4. **Setup Monitoring:**
   - Railway metrics
   - Netlify Analytics
   - Error tracking (optional: Sentry)

---

## **📞 Resources**

- **Railway Docs:** https://docs.railway.app/deploy/deployments
- **Netlify Docs:** https://docs.netlify.com/
- **Laravel Deployment:** https://laravel.com/docs/12/deployment
- **Filament Admin:** https://filamentphp.com/docs/3.x/admin

---

## ✅ Deployment Complete!

Jika semua tahap berhasil, aplikasi Anda sekarang live:

- **Admin/Reviewer Panel:** https://yourproject.up.railway.app/admin
- **Frontend:** https://sistem-assesment-frontend.netlify.app
- **API Endpoint:** https://yourproject.up.railway.app/api

🚀 **Happy deploying!**
