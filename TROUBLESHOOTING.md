# 🔧 Troubleshooting & Monitoring Guide

---

## 🚨 Common Issues & Solutions

### 1️⃣ **CORS Errors (Frontend)**

**Error Message:**
```
Access to XMLHttpRequest at 'https://backend.railway.app/api/...'
from origin 'https://frontend.netlify.app' has been blocked by CORS policy
```

**Solutions:**

a) **Verify FRONTEND_URL in Railway**
```bash
# Check Railway Variables
# FRONTEND_URL harus exactly match ke Netlify URL
# ✅ CORRECT: https://sistem-assesment-frontend.netlify.app
# ❌ WRONG: https://netlify.app
# ❌ WRONG: http://frontend.netlify.app (missing https)
```

b) **Ensure CORS Middleware Active**
```bash
# File: app/Http/Middleware/HandleCors.php harus exist
# File: bootstrap/app.php harus include middleware

# Test CORS:
curl -H "Origin: https://your-frontend.netlify.app" \
     -H "Access-Control-Request-Method: GET" \
     -X OPTIONS https://your-backend.up.railway.app/api/user -v
```

Expected response headers:
```
Access-Control-Allow-Origin: https://your-frontend.netlify.app
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

c) **Restart Railway Deployment**
- Railway Dashboard → Project → Deployments
- Klik latest deploy → "Redeploy"

---

### 2️⃣ **Database Connection Failed**

**Error Message:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solutions:**

a) **Verify MySQL Service Status**
```bash
# Railway Dashboard
1. Klik project
2. Scroll down ke "Services"
3. MySQL service harus RUNNING (hijau)
```

b) **Check Database Credentials**
```bash
# Verify di Railway Variables:
DB_HOST=*.railway.internal      # ✅ CORRECT (internal)
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=xxxxx
```

c) **Test Connection Manually**
```bash
# From Railway terminal
railway run php artisan tinker
> DB::connection()->getPDO()
> 
# If success, returns PDO object
# If error, shows connection error
```

d) **Rebuild MySQL Database**
```bash
# If crashed, delete MySQL service dan buat baru:
# Railway → MySQL service → Delete
# Railway → Add Service → MySQL
# Update credentials di Variables
# Re-run migrations:
# railway run php artisan migrate --force
```

---

### 3️⃣ **Blank Page on Frontend (Netlify)**

**Symptoms:**
- Netlify URL opens but page is completely blank
- Browser console shows no React/Vue errors

**Solutions:**

a) **Check Netlify Build Logs**
```
Netlify Dashboard:
1. Klik Site
2. Deploys tab
3. Klik latest deploy
4. View deploy log

Carilah:
- Build command errors
- npm install errors
- dist folder kosong?
```

b) **Verify Build Settings**
```
Netlify → Site settings → Build & Deploy:
Base directory:   frontend
Build command:    npm run build
Publish directory: frontend/dist
```

c) **Test Local Build**
```bash
cd frontend
npm install
npm run build

# Check if dist folder exists and has files
ls dist/
# Harus ada: index.html, assets/, dll
```

d) **Check netlify.toml Redirects**
```toml
# /frontend/netlify.toml harus ada:
[[redirects]]
from = "/*"
to = "/index.html"
status = 200
```

e) **Force Rebuild**
```
Netlify → Deploys → Trigger deploy → Deploy site
```

---

### 4️⃣ **API Endpoints not Found (404)**

**Error:**
```json
{"message": "Not Found"}
```

**Solutions:**

a) **Verify API Routes Exist**
```bash
# List all routes:
php artisan route:list | grep api

# Must show routes like:
# GET|HEAD /api/debug
# GET|HEAD /api/exam/{token}/questions
# POST /api/exam/{token}/submit
```

b) **Check Module Registration**

Jika menggunakan API controller, pastikan di-register:
```php
// routes/api.php
Route::get('/assessments/{id}', [\App\Http\Controllers\API\AssessmentController::class, 'show']);
```

c) **Test from Terminal**
```bash
# Replace yourdomain dengan actual URL
curl https://yourdomain.up.railway.app/api/debug
# Should return: {"message":"API is working!","timestamp":"..."}
```

d) **Check Filament Routes Conflict**

Filament bisa override routes. Pastikan API prefix unique:
```php
// routes/api.php menggunakan /api
// Filament menggunakan /admin
// Tidak ada conflict ✅
```

---

### 5️⃣ **Login/Authentication Not Working**

**Symptoms:**
- Login button tidak responsive
- Token tidak disimpan
- API returns 401 Unauthorized

**Solutions:**

a) **Check Sanctum Configuration**
```bash
# File: config/sanctum.php harus ada
# Verify stateful domains:
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1,127.0.0.1:8000,::1'))

# Update .env:
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:5173,your-backend.up.railway.app,your-frontend.netlify.app
```

b) **Test Token Generation**
```bash
# Create token manually
railway run php artisan tinker
> $user = App\Models\User::first()
> $token = $user->createToken('test')->plainTextToken
> echo $token
```

c) **Verify Token Storage in Browser**
```javascript
// Browser console
localStorage.getItem('auth_token')
// Should return token string, not null

// Also check:
sessionStorage.getItem('auth_token')
```

d) **Test API with Token**
```bash
TOKEN="your-token-here"
curl https://your-backend.up.railway.app/api/user \
  -H "Authorization: Bearer $TOKEN"
```

---

### 6️⃣ **Deployment Timeout**

**Error:**
```
Build failed: timeout after 10 minutes
```

**Solutions:**

a) **Check Build Logs**
- Look for slow npm install, composer install

b) **Optimize Dependencies**
```bash
# Frontend:
npm prune           # Remove unused packages
npm list --depth=0  # Check what's installed

# Backend:
composer show       # List packages
```

c) **Increase Railway Compute** (Paid feature)
- Railway Dashboard → Project → Settings
- Increase CPU/RAM allocation

d) **Split Large Operations**
```bash
# Instead of everything in start command:
# 1. Run migrations separately
# 2. Then start server

# Create separate scripts:
railway run php artisan migrate --force
railway up  # Start server only
```

---

### 7️⃣ **Database Migrations Not Running**

**Error:**
```
Error: Migration table not found
```

**Solutions:**

a) **Manual Migration in Railway**
```bash
railway run php artisan migrate --force
```

b) **Update Start Command**
```bash
# Ensure migrations run before server starts:
startCommand = "php artisan migrate && php artisan serve --host=0.0.0.0 --port=$PORT"
```

c) **Check Migration Files**
```bash
# Verify migration files exist:
ls database/migrations/
# Should show files like: 2026_01_01_create_users_table.php
```

d) **Reset if Corrupted**
```bash
railway run php artisan migrate:reset
railway run php artisan migrate --seed
```

---

## 📊 Monitoring & Logging

### 1. Railway Logs

```bash
# View live logs:
railway run php artisan logs

# Or via Railway CLI:
railway logs --follow

# Common log locations:
# storage/logs/laravel-YYYY-MM-DD.log
```

### 2. Check Database Size

```bash
railway run php artisan tinker
> DB::select('SELECT COUNT(*) as count FROM candidates;')
> DB::select('SELECT COUNT(*) as count FROM candidate_assessments;')
```

### 3. Monitor API Performance

```javascript
// Add timing to API calls (frontend)
const start = performance.now()

fetch(`${API_URL}/exam/${token}/questions`)
  .then(r => r.json())
  .then(data => {
    const end = performance.now()
    console.log(`Request took ${end - start}ms`)
  })
```

### 4. Check Resource Usage

```
Railway Dashboard:
1. Project → Logs tab
2. Monitor CPU, Memory, Bandwidth usage
3. Alerts jika usage naik drastis
```

---

## 🔐 Security Checks

### Pre-Production Checklist

- [ ] **HTTPS enabled** (both Railway & Netlify)
- [ ] **API_DEBUG=false** in production
- [ ] **Sanctum tokens** not exposed in logs
- [ ] **Database credentials** not in code
- [ ] **CORS configured** properly (not allow *)
- [ ] **Rate limiting** enabled
- [ ] **Input validation** on all endpoints
- [ ] **SQL injection prevention** (use Eloquent ORM)
- [ ] **XSS prevention** (Vue auto-escapes)
- [ ] **CSRF tokens** configured (if using forms)

### Test Security Headers

```bash
curl -i https://your-backend.up.railway.app/api/debug | grep -i "security\|x-\|content-"

# Should include:
# X-Content-Type-Options: nosniff
# X-Frame-Options: SAMEORIGIN
# X-XSS-Protection: 1; mode=block
```

---

## 📈 Performance Optimization

### 1. Database Optimization

```bash
# Check slow queries:
railway run php artisan tinker
> DB::enableQueryLog()
> App\Models\CandidateAssessment::with('assessment', 'candidate')->get()
> dd(DB::getQueryLog())

# Add indexes untuk foreign keys:
```

```php
// In migration:
Schema::table('candidate_assessments', function (Blueprint $table) {
    $table->index('assessment_id');
    $table->index('candidate_id');
});
```

### 2. API Caching

```php
// routes/api.php
Route::get('/assessments', function () {
    return Cache::remember('assessments', now()->addHours(1), function () {
        return Assessment::all();
    });
});
```

### 3. Frontend Optimization

```bash
# Check bundle size:
cd frontend
npm run build

# Output shows:
# Output: 150.00 KiB
# ✅ < 300KiB is good

# Optimize:
npm install -g vite-bundle-analyzer
```

---

## 🆘 Quick Diagnosis Script

```bash
#!/bin/bash
# Copy & run this to diagnose issues quickly

echo "=== Railway Status ==="
railway run php artisan tinker -q "echo 'DB OK'"

echo "=== Frontend Build ==="
cd frontend && npm run build && echo "Build OK"

echo "=== API Health ==="
curl -s https://your-backend.up.railway.app/api/debug | jq .

echo "=== CORS Test ==="
curl -i -H "Origin: https://your-frontend.netlify.app" \
     https://your-backend.up.railway.app/api/debug \
     | grep -i "access-control"

echo "=== All checks complete ==="
```

---

## 📞 When to Contact Support

**Railway Support:**
- Deployment fails
- Database service crashed
- Memory/CPU issues
- https://railway.app/docs

**Netlify Support:**
- Build fails
- Domain issues
- https://docs.netlify.com/support

**Laravel/PHP:**
- Logic errors
- Migration issues
- https://laravel.com/docs

---

## 📝 Logging Best Practices

### Debug Log

```php
// Instead of using die() or var_dump()
Log::debug('User login attempt', [
    'email' => $email,
    'timestamp' => now()
]);

// View logs:
// railway run php artisan logs
```

### Production Monitoring

```php
// Monitor critical operations
try {
    // Important operation
} catch (Exception $e) {
    Log::critical('Critical error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Alert admin
}
```

---

**Last Updated:** April 2026
**Keep this guide handy during deployment!** 🚀
