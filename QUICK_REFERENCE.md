# 🎯 Quick Reference - Deployment URLs & Commands

## 📊 Deployment Architecture

```
┌─────────────────────────────────┐
│   GITHUB REPOSITORY             │
│ (Main branch = Auto Deploy)     │
└────────┬──────────────────────┬─┘
         │                      │
         ▼                      ▼
    RAILWAY                  NETLIFY
    (Laravel Backend)         (Vue Frontend)
    PHP 8.2, MySQL           Node 20, Vite
    https://xxxx.            https://xxxx.
    up.railway.app           netlify.app
         │                      │
         └──────────┬──────────┘
                    ▼
            📱 User Browsers
            API + UI Interaction
```

---

## 🚀 Quick Start Commands

### Lokal Testing
```bash
# Backend
php artisan serve                    # http://localhost:8000

# Frontend  
cd frontend && npm run dev           # http://localhost:5173

# Database
php artisan migrate --seed           # Setup DB lokal
```

### Deployment Push
```bash
# Push ke GitHub (trigger auto-deploy)
git add .
git commit -m "Update deployment"
git push origin main
```

### Generate App Key
```bash
php artisan key:generate --show      # Copy ke Railway APP_KEY
```

---

## 🔗 Important URLs

| Service | URL | Login |
|---------|-----|-------|
| **Backend API** | `https://your-project.up.railway.app/api` | - |
| **Filament Admin** | `https://your-project.up.railway.app/admin` | Email + Password |
| **Frontend** | `https://your-site.netlify.app` | Exam interface |
| **API Health** | `https://your-project.up.railway.app/up` | 200 OK |

---

## 🔐 Environment Variables

### Railway (.env)
```bash
APP_KEY=base64:xxxxx               # Generate locally
APP_URL=https://xxxx.up.railway.app
DB_HOST=host.railway.internal      # MySQL service
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=xxxxx
FRONTEND_URL=https://xxxx.netlify.app
```

### Netlify (Build vars)
```bash
VITE_API_URL=https://xxxx.up.railway.app/api
VITE_APP_URL=https://xxxx.netlify.app
```

---

## 🔧 API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/user` | GET | Current user |
| `/api/assessments` | GET | List assessments |
| `/api/assessments/{id}` | GET | Get assessment |
| `/api/assessments/start` | POST | Start exam |
| `/api/assessments/{id}/submit` | POST | Submit answers |
| `/api/reviews` | GET | List reviews (reviewer) |
| `/api/reviews/{id}` | POST | Submit review |

**Auth Headers:**
```bash
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 🐛 Debug Commands

### Check Backend Status
```bash
# Test API from terminal
curl https://your-project.up.railway.app/up

# Check specific endpoint
curl https://your-project.up.railway.app/api/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### View Logs
```bash
# Railway Laravel logs
railway run php artisan logs

# Database status
railway run php artisan tinker
> DB::connection()->getPDO()
```

### Frontend Console
```javascript
// Check API URL config
console.log(import.meta.env)

// Test API call
fetch('https://your-project.up.railway.app/api/assessments')
  .then(r => r.json())
  .then(d => console.log(d))
```

---

## 📝 File Locations

| File | Purpose | Location |
|------|---------|----------|
| Environment Config | Backend config | `/railway.toml` |
| Deploy Steps | Detailed guide | `/DEPLOYMENT_STEPS.md` |
| CORS Middleware | Request handling | `/app/Http/Middleware/HandleCors.php` |
| API Client | Frontend API | `/frontend/src/api.js` |
| Netlify Config | Frontend deploy | `/frontend/netlify.toml` |
| Env Example | Template vars | `/.env.example` |

---

## ✅ Testing Checklist

Quick test setelah deploy:

```javascript
// 1. Frontend loads
✓ https://your-site.netlify.app    

// 2. Admin panel accessible
✓ https://your-project.up.railway.app/admin

// 3. API responds
✓ /api/user returns data

// 4. CORS working
✓ No CORS error di browser console

// 5. Database connected
✓ Can see data di Filament admin

// 6. Login works
✓ Can login dan submit assessment
```

---

## 🆘 Common Issues

| Issue | Fix |
|-------|-----|
| **Blank page** | Check Netlify build logs, verify `frontend/dist` exists |
| **CORS error** | Update `FRONTEND_URL` in Railway to exact Netlify URL |
| **API 404** | Check route definitions in `routes/api.php` |
| **DB error** | Verify MySQL service active in Railway |
| **Build timeout** | Check logs, optimize dependencies, increase compute |

---

## 📞 Support Links

- **Railway:** https://railway.app/dashboard
- **Netlify:** https://app.netlify.com
- **GitHub:** https://github.com/your-username/sistem-assesment-karyawan-baru

---

## 🎉 Status

- ✅ Backend deployed to Railway
- ✅ Frontend deployed to Netlify  
- ✅ Database configured
- ✅ CORS enabled
- ✅ Auto-deploys from GitHub main branch

**Ready for production!**
