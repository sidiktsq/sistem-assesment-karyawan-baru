<template>
  <div class="start-page font-outfit">
    <!-- Elegant Background Glows -->
    <div class="elegant-bg">
      <div class="glow-1"></div>
      <div class="glow-2"></div>
    </div>
    
    <div class="main-wrapper">
      <div class="start-card fade-in">
        <div class="top-accent"></div>

        <header class="card-header">
          <div class="logo-container">
            <div class="logo-box">
              <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Universitas_Teknologi_Bandung_Logo.png" alt="UTB Logo" class="main-logo">
              <div class="logo-pulse"></div>
            </div>
          </div>
          <h1 class="title">Selamat Datang</h1>
          <p class="subtitle">Sistem Assessment Mahasiswa & Karyawan</p>
          <div class="institution-name">Universitas Teknologi Bandung</div>
        </header>

        <main class="card-body">
          <div class="content-section">
            <!-- Loading State -->
            <div v-if="loading && token" class="loading-container">
              <div class="loader-spinner"></div>
              <p class="loading-text">Memvalidasi akses Anda...</p>
            </div>

            <!-- Token Input State -->
            <div v-else-if="!token || error" class="input-form">
              <div v-if="error" class="error-alert">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>{{ error }}</span>
              </div>
              
              <p class="instruction">Silakan masukkan Token Akses yang telah dikirimkan melalui email untuk memulai.</p>
              
              <div class="form-group">
                <input 
                  v-model="inputToken" 
                  type="text" 
                  placeholder="Masukkan Token Disini" 
                  class="premium-input"
                  @keyup.enter="useInputToken"
                >
                <button @click="useInputToken" class="btn-primary-elegant">
                  Periksa Akses
                </button>
              </div>
            </div>

            <!-- Ready Section -->
            <div v-else-if="assessment" class="ready-panel">
              <div class="info-card">
                <h3 class="assessment-name">{{ assessment.assessment?.title || 'Assessment Dashboard' }}</h3>
                <p class="assessment-type">{{ assessment.assessment?.type || 'Assessment Umum' }}</p>
                
                <div class="meta-info">
                  <div class="meta-item">
                    <span class="m-label">ESTIMASI WAKTU</span>
                    <span class="m-value">{{ assessment.assessment?.duration_minutes || assessment.duration || '-' }} Menit</span>
                  </div>
                  <div class="meta-item">
                    <span class="m-label">JUMLAH SOAL</span>
                    <span class="m-value">{{ assessment.assessment?.questions_count || '-' }} Pertanyaan</span>
                  </div>
                </div>
              </div>

              <div class="notice-box">
                <div class="notice-icon">i</div>
                <p>Klik tombol di bawah untuk memulai. Pastikan Anda berada di lingkungan yang tenang.</p>
              </div>

              <button @click="startExam" :disabled="starting" class="btn-start-premium">
                {{ starting ? 'Menyiapkan...' : 'Mulai Sekarang' }}
              </button>
            </div>
          </div>
        </main>


      </div>
      
      <div class="page-copyright">
        &copy; 2024 Universitas Teknologi Bandung. Powered by Assessment System.
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Start',
  data() {
    return {
      loading: false,
      starting: false,
      error: null,
      assessment: null,
      inputToken: ''
    }
  },
  computed: {
    token() { return this.$route.params.token || null }
  },
  async mounted() {
    if (this.token) await this.validateToken()
  },
  methods: {
    async validateToken() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/api/exam/${this.token}/questions`)
        this.assessment = response.data
      } catch (error) {
        this.error = error.response?.data?.error || 'Token tidak valid'
        this.assessment = null
      } finally { this.loading = false }
    },
    useInputToken() {
      if (!this.inputToken.trim()) return
      this.$router.push(`/start/${this.inputToken.trim()}`)
      setTimeout(() => this.validateToken(), 100)
    },
    async startExam() {
      this.starting = true
      try { this.$router.push(`/exam/${this.token}/take`) } 
      catch (error) { console.error(error) } 
      finally { this.starting = false }
    }
  },
  watch: {
    '$route.params.token'(newToken) { if (newToken) this.validateToken() }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

.font-outfit { font-family: 'Outfit', sans-serif; }

.start-page {
  min-height: 100vh;
  background-color: #0a0c10;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  color: #e2e8f0;
}

.elegant-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.glow-1 {
  position: absolute;
  top: -10%; left: -10%;
  width: 60%; height: 60%;
  background: radial-gradient(circle, rgba(0, 84, 164, 0.15) 0%, transparent 70%);
}

.glow-2 {
  position: absolute;
  bottom: -10%; right: -10%;
  width: 60%; height: 60%;
  background: radial-gradient(circle, rgba(255, 194, 14, 0.08) 0%, transparent 70%);
}

.main-wrapper {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 480px;
  padding: 20px;
}

.start-card {
  background: rgba(17, 25, 40, 0.75);
  backdrop-filter: blur(20px);
  border-radius: 32px;
  box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.5);
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.top-accent {
  height: 4px;
  background: linear-gradient(90deg, #0054A4, #FFC20E);
  opacity: 0.6;
}

.card-header {
  padding: 40px 40px 20px;
  text-align: center;
}

.logo-container {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.logo-box {
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.03);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
  padding: 12px;
}

.main-logo {
  width: 100%;
  height: auto;
  z-index: 2;
}

.logo-pulse {
  position: absolute;
  inset: -10px;
  border: 1px solid rgba(0, 84, 164, 0.3);
  border-radius: 30px;
  animation: pulse-ring 4s infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.9); opacity: 0; }
  50% { opacity: 0.4; }
  100% { transform: scale(1.15); opacity: 0; }
}

.title {
  font-size: 2.25rem;
  font-weight: 700;
  background: linear-gradient(to bottom, #ffffff, #94a3b8);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 8px;
  letter-spacing: -0.03em;
}

.subtitle {
  font-size: 0.9rem;
  color: #94a3b8;
  margin-bottom: 4px;
}

.institution-name {
  font-size: 0.75rem;
  font-weight: 600;
  color: #FFC20E;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.card-body {
  padding: 0 40px;
}

.content-section {
  min-height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.instruction {
  text-align: center;
  font-size: 0.95rem;
  line-height: 1.6;
  color: #cbd5e1;
  margin-bottom: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.premium-input {
  background: rgba(13, 17, 23, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 16px;
  color: #f1f5f9;
  font-size: 1rem;
  text-align: center;
  transition: all 0.3s ease;
}

.premium-input:focus {
  outline: none;
  border-color: #0054A4;
  background: rgba(13, 17, 23, 0.8);
  box-shadow: 0 0 0 4px rgba(0, 84, 164, 0.1);
}

.btn-primary-elegant {
  background: #0054A4;
  color: white;
  border: none;
  padding: 16px;
  border-radius: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary-elegant:hover {
  background: #004080;
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -5px rgba(0, 84, 164, 0.4);
}

.error-alert {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171;
  padding: 12px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
  font-size: 0.85rem;
}

/* Assessment Panel */
.ready-panel {
  animation: fadeIn 0.5s ease-out;
}

.info-card {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 20px;
  padding: 24px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  margin-bottom: 20px;
}

.assessment-name {
  color: #f1f5f9;
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.assessment-type {
  color: #FFC20E;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 20px;
}

.meta-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.meta-item {
  background: rgba(255, 255, 255, 0.02);
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.03);
}

.m-label {
  display: block;
  font-size: 0.65rem;
  color: #64748b;
  margin-bottom: 4px;
  font-weight: 700;
}

.m-value {
  color: #e2e8f0;
  font-size: 0.9rem;
  font-weight: 600;
}

.notice-box {
  display: flex;
  gap: 12px;
  background: rgba(0, 84, 164, 0.08);
  padding: 16px;
  border-radius: 16px;
  border: 1px solid rgba(0, 84, 164, 0.2);
  margin-bottom: 24px;
  align-items: center;
}

.notice-icon {
  width: 20px;
  height: 20px;
  background: #0054A4;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 800;
  flex-shrink: 0;
}

.notice-box p {
  font-size: 0.85rem;
  color: #94a3b8;
  line-height: 1.4;
}

.btn-start-premium {
  width: 100%;
  background: linear-gradient(135deg, #0054A4, #FFC20E);
  color: white;
  border: none;
  padding: 18px;
  border-radius: 18px;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.4s ease;
  box-shadow: 0 10px 25px -5px rgba(0, 84, 164, 0.4);
}

.btn-start-premium:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 20px 40px -10px rgba(0, 84, 164, 0.5);
}

.card-footer {
  padding: 30px 40px;
  text-align: center;
}

.secure-note {
  font-size: 0.75rem;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.page-copyright {
  text-align: center;
  margin-top: 24px;
  font-size: 0.7rem;
  color: #475569;
  letter-spacing: 0.02em;
}

.loader-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(0, 84, 164, 0.1);
  border-top-color: #FFC20E;
  border-radius: 50%;
  margin: 0 auto 16px;
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.fade-in {
  animation: fadeInContainer 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInContainer {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 480px) {
  .main-wrapper { padding: 15px; }
  .start-card { border-radius: 24px; }
  .card-header, .card-body, .card-footer { padding-left: 25px; padding-right: 25px; }
}
</style>
