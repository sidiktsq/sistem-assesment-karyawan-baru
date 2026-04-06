# 📚 API Documentation - Assessment System

**Base URL:** `https://your-backend.up.railway.app/api`

**Authentication:** Bearer Token (Sanctum)

---

## 🔑 Authentication Endpoints

### Get Current User
```http
GET /user
Authorization: Bearer {token}

Response:
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@test.com",
  "role": "admin"
}
```

---

## 📝 Exam Endpoints

### Get Assessment Questions
```http
GET /exam/{token}/questions
Content-Type: application/json

Parameters:
- token (required): Candidate's access token

Response:
{
  "questions": [
    {
      "id": 1,
      "type": "multiple_choice",
      "question": "Question text here?",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "score": 10,
      "user_answer": null
    }
  ],
  "assessment": {
    "title": "Technical Assessment",
    "description": "Assessment description",
    "duration_minutes": 60,
    "type": "technical",
    "passing_score": 70
  },
  "candidate": {
    "name": "John Doe",
    "email": "john@test.com"
  },
  "time_remaining": 3600  // seconds
}
```

### Save Single Answer
```http
POST /exam/{token}/save-answer
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "question_id": 1,
  "answer": "Option A"
}

Response:
{
  "success": true
}
```

### Save Multiple Answers (Batch)
```http
POST /exam/{token}/save-answers
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "answers": {
    "1": "Option A",
    "2": "Option B",
    "3": "Option C"
  }
}

Response:
{
  "success": true
}
```

### Submit Assessment
```http
POST /exam/{token}/submit
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "answers": {
    "1": "Option A",
    "2": "Option B",
    "3": "Option C"
  }
}

Response:
{
  "success": true,
  "score": 85,
  "status": "completed",
  "message": "Assessment submitted successfully"
}
```

### Get Assessment Result/Status
```http
GET /exam/{token}/result
Authorization: Bearer {token}

Response:
{
  "status": "completed",
  "score": 85,
  "passing_score": 70,
  "passed": true,
  "answers_submitted_at": "2026-04-06T10:30:00Z",
  "results": {
    "total_questions": 10,
    "correct_answers": 8,
    "wrong_answers": 2
  }
}
```

---

## 👥 Candidate Endpoints (Protected)

### Get My Profile
```http
GET /candidate/profile
Authorization: Bearer {token}

Response:
{
  "id": 1,
  "name": "John Doe",
  "email": "john@test.com",
  "phone": "081234567890",
  "position": "Software Engineer",
  "department": "Engineering"
}
```

### Update My Profile
```http
PUT /candidate/profile
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "name": "John Doe",
  "phone": "081234567890",
  "position": "Senior Engineer"
}

Response:
{
  "success": true,
  "profile": {...}
}
```

### Get My Invitations
```http
GET /candidate/invitations
Authorization: Bearer {token}

Response:
{
  "invitations": [
    {
      "id": 1,
      "assessment": {
        "id": 1,
        "title": "Technical Assessment",
        "type": "technical"
      },
      "status": "pending",
      "invited_at": "2026-04-01T00:00:00Z",
      "deadline": "2026-04-15T00:00:00Z",
      "access_token": "abc123..."
    }
  ]
}
```

### Get My Assessment Results
```http
GET /candidate/assessments
Authorization: Bearer {token}

Response:
{
  "assessments": [
    {
      "assessment_id": 1,
      "assessment_title": "Technical Assessment",
      "status": "completed",
      "score": 85,
      "passed": true,
      "submitted_at": "2026-04-06T10:30:00Z"
    }
  ]
}
```

---

## 🔍 Reviewer Endpoints (Protected - role:reviewer only)

### List All Reviews
```http
GET /reviews
Authorization: Bearer {token}

Response:
{
  "reviews": [
    {
      "id": 1,
      "candidate": {
        "id": 1,
        "name": "John Doe"
      },
      "assessment": {
        "id": 1,
        "title": "Technical Assessment"
      },
      "status": "pending",
      "score": 85,
      "submitted_at": "2026-04-06T10:30:00Z"
    }
  ]
}
```

### Get Review Details
```http
GET /reviews/{reviewId}
Authorization: Bearer {token}

Response:
{
  "id": 1,
  "candidate": {
    "id": 1,
    "name": "John Doe",
    "email": "john@test.com"
  },
  "assessment": {
    "id": 1,
    "title": "Technical Assessment",
    "questions": [...]
  },
  "answers": [
    {
      "question_id": 1,
      "question_text": "Question?",
      "answer": "Option A",
      "points_earned": 10,
      "points_total": 10
    }
  ],
  "total_score": 85,
  "status": "pending_review"
}
```

### Submit Review/Score
```http
POST /reviews/{reviewId}
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "overall_score": 85,
  "comments": "Good technical knowledge. Needs improvement in system design.",
  "recommendations": "Consider reading about microservices architecture",
  "status": "reviewed"  // or "rejected"
}

Response:
{
  "success": true,
  "message": "Review submitted successfully"
}
```

### Update Review
```http
PUT /reviews/{reviewId}
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "overall_score": 88,
  "comments": "Updated comments"
}

Response:
{
  "success": true
}
```

---

## ⚙️ Admin Endpoints (Protected - role:admin only)

### List Assessments
```http
GET /admin/assessments
Authorization: Bearer {token}

Response:
{
  "assessments": [
    {
      "id": 1,
      "title": "Technical Assessment",
      "type": "technical",
      "duration_minutes": 60,
      "passing_score": 70,
      "questions_count": 10,
      "created_at": "2026-01-01T00:00:00Z"
    }
  ]
}
```

### Create Assessment
```http
POST /admin/assessments
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "title": "Technical Assessment",
  "description": "Assessment description",
  "type": "technical",
  "duration_minutes": 60,
  "passing_score": 70,
  "questions": [
    {
      "type": "multiple_choice",
      "question_text": "Question?",
      "options": ["A", "B", "C", "D"],
      "correct_answer": "A",
      "score": 10
    }
  ]
}

Response:
{
  "success": true,
  "assessment": {...}
}
```

### Send Invitations
```http
POST /admin/invitations/send
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "assessment_id": 1,
  "candidates": [
    {"email": "john@test.com", "name": "John Doe"},
    {"email": "jane@test.com", "name": "Jane Smith"}
  ],
  "deadline_days": 7
}

Response:
{
  "success": true,
  "invitations_sent": 2,
  "message": "Invitations sent to 2 candidates"
}
```

---

## 🐛 Debug Endpoint

### Health Check
```http
GET /debug
Content-Type: application/json

Response:
{
  "message": "API is working!",
  "timestamp": "2026-04-06T10:30:00Z"
}
```

---

## 🔴 Error Responses

### 400 Bad Request
```json
{
  "error": "Validation failed",
  "details": {
    "question_id": ["The field is required"]
  }
}
```

### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Token invalid or expired"
}
```

### 403 Forbidden
```json
{
  "error": "Forbidden",
  "message": "Assessment tidak dalam progress"
}
```

### 404 Not Found
```json
{
  "error": "Token tidak valid"
}
```

### 429 Too Many Requests
```json
{
  "error": "Rate limited",
  "retry_after": 60
}
```

### 500 Server Error
```json
{
  "error": "Internal server error",
  "message": "Error details here"
}
```

---

## 🔐 Authentication

### Login (if needed)
```http
POST /login
Content-Type: application/json

Request:
{
  "email": "admin@test.com",
  "password": "password"
}

Response:
{
  "token": "1|laravel_sanctum_token...",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@test.com"
  }
}
```

### Logout
```http
POST /logout
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## 📊 Response Headers

Semua responses include headers:
```
Content-Type: application/json
Access-Control-Allow-Origin: {FRONTEND_URL}
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
X-Request-ID: {unique-request-id}
```

---

## 🧪 Testing API dengan cURL

```bash
# Test debug endpoint
curl https://your-backend.up.railway.app/api/debug

# Get exam questions
curl https://your-backend.up.railway.app/api/exam/{token}/questions

# Save answer
curl -X POST https://your-backend.up.railway.app/api/exam/{token}/save-answer \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"question_id": 1, "answer": "Option A"}'

# Submit assessment
curl -X POST https://your-backend.up.railway.app/api/exam/{token}/submit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"answers": {"1": "Option A", "2": "Option B"}}'
```

---

## 📖 Integration Examples

### Frontend (JavaScript/Vue)
```javascript
import axios from 'axios'

const api = axios.create({
  baseURL: 'https://your-backend.up.railway.app/api'
})

// Get exam questions
async function getQuestions(token) {
  const res = await api.get(`/exam/${token}/questions`)
  return res.data
}

// Save answer
async function saveAnswer(token, questionId, answer) {
  const res = await api.post(`/exam/${token}/save-answer`, {
    question_id: questionId,
    answer
  })
  return res.data
}

// Submit assessment
async function submitAssessment(token, answers) {
  const res = await api.post(`/exam/${token}/submit`, { answers })
  return res.data
}
```

---

## 📝 Rate Limiting

- **Unauthenticated:** 60 requests per minute
- **Authenticated:** 300 requests per minute
- **Admin:** Unlimited

Exceeded rate limit → HTTP 429 with `Retry-After` header

---

## ✅ API Checklist

Test semua endpoints sebelum go-live:

- [ ] `/debug` - Health check
- [ ] `/user` - Current user
- [ ] `/exam/{token}/questions` - Get questions
- [ ] `/exam/{token}/save-answer` - Save single answer
- [ ] `/exam/{token}/submit` - Submit assessment
- [ ] `/reviews` - Reviewer list
- [ ] `/admin/assessments` - Admin list
- [ ] Error handling (400, 401, 403, 404, 500)
- [ ] CORS headers present
- [ ] Authentication working

---

**Last Updated:** April 2026
**Version:** 1.0
