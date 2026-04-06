<template>
  <div class="result-page font-outfit">
    <div class="elegant-bg">
      <div class="glow-1"></div>
      <div class="glow-2"></div>
    </div>
    
    <div class="main-wrapper">
      <div class="result-card fade-in">
        <div class="top-accent"></div>

        <header class="card-header">
          <div class="icon-container">
            <div class="check-box">
              <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Universitas_Teknologi_Bandung_Logo.png" alt="UTB Logo" style="width: 40px; height: auto;">
              <div class="icon-pulse"></div>
            </div>
          </div>
          <h1 class="title">Terima Kasih</h1>
          <p class="subtitle">
            Assessment <span class="highlight">{{ assessment?.assessment?.title || 'Modul' }}</span> Berhasil Terkirim
          </p>
        </header>

        <main class="card-body">
          <div class="message-section">
            <p class="primary-text">
              Kami menghargai dedikasi Anda dalam menyelesaikan asesmen ini. 
              Informasi Anda telah kami simpan dengan aman dalam sistem evaluasi kami.
            </p>
            
            <div class="status-indicator">
              <span class="pulse-dot"></span>
              <span class="status-text">
                {{ result?.result === 'pending' ? 'Dalam Proses Peninjauan' : 'Penilaian Selesai' }}
              </span>
            </div>

            <div class="instruction-box">
              <p>Tim kami akan meninjau jawaban Anda secara menyeluruh. <strong>Hasil resmi akan dikirimkan melalui email</strong> setelah proses validasi selesai.</p>
            </div>
          </div>

          <section class="data-summary">
            <div class="divider">
              <span>RINGKASAN KONFIRMASI</span>
            </div>
            
            <div class="info-table" v-if="assessment">
              <div class="info-row">
                <span class="label">Nama Peserta</span>
                <span class="value">{{ assessment?.candidate?.name || '-' }}</span>
              </div>
              <div class="info-row">
                <span class="label">Posisi Dilamar</span>
                <span class="value">{{ assessment?.candidate?.position_applied || '-' }}</span>
              </div>
              <div class="info-row">
                <span class="label">Kategori Ujian</span>
                <span class="value text-upper">{{ assessment?.assessment?.type || '-' }}</span>
              </div>
              <div class="info-row">
                <span class="label">Waktu Selesai</span>
                <span class="value">{{ formatDate(assessment?.assessment?.completed_at) }}</span>
              </div>
            </div>
            <div v-else class="text-center py-4 text-slate-500">
              Memuat data...
            </div>
          </section>
        </main>

        <footer class="card-footer">
          <p class="exit-message">Anda dapat menutup halaman ini sekarang.</p>
          <button @click="goHome" class="btn-home">
            Kembali ke Beranda
          </button>
        </footer>
      </div>
      
      <div class="copyright">
        &copy; 2024 Assessment System. Terenkripsi secara aman.
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AssessmentResult',
  data() {
    return {
      loading: true,
      result: null,
      assessment: null
    }
  },
  async mounted() {
    await this.loadResult()
  },
  methods: {
    async loadResult() {
      try {
        const token = this.$route.params.token
        const response = await axios.get(`/api/exam/${token}/result`)
        this.result = response.data.result
        this.assessment = response.data
      } catch (err) {
        console.error('Data error:', err)
      } finally {
        this.loading = false
      }
    },
    formatDate(date) {
      if (!date) return '-'
      const options = { 
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
      }
      return new Date(date).toLocaleString('id-ID', options)
    },
    goHome() {
      this.$router.push('/')
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

.font-outfit { font-family: 'Outfit', sans-serif; }

.result-page {
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
  max-width: 500px;
  padding: 20px;
}

.result-card {
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

.icon-container {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.check-box {
  width: 64px;
  height: 64px;
  background: rgba(0, 84, 164, 0.1);
  color: #FFC20E;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
  border: 1px solid rgba(0, 84, 164, 0.3);
  position: relative;
}

.icon-pulse {
  position: absolute;
  inset: -8px;
  border: 1px solid rgba(0, 84, 164, 0.3);
  border-radius: 24px;
  animation: pulse-ring 3s infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.9); opacity: 0; }
  50% { opacity: 0.5; }
  100% { transform: scale(1.2); opacity: 0; }
}

.title {
  font-size: 2.2rem;
  font-weight: 700;
  background: linear-gradient(to bottom, #ffffff, #94a3b8);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 8px;
  letter-spacing: -0.03em;
}

.subtitle {
  font-size: 0.95rem;
  color: #94a3b8;
}

.highlight {
  color: #f1f5f9;
  font-weight: 600;
}

.card-body {
  padding: 0 40px;
}

.message-section {
  text-align: center;
  margin-bottom: 30px;
}

.primary-text {
  font-size: 1rem;
  line-height: 1.7;
  color: #cbd5e1;
  margin-bottom: 20px;
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.03);
  padding: 6px 16px;
  border-radius: 100px;
  margin-bottom: 24px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.pulse-dot {
  width: 6px;
  height: 6px;
  background: #00A651;
  border-radius: 50%;
  box-shadow: 0 0 10px #00A651;
}

.status-text {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #94a3b8;
}

.instruction-box {
  background: rgba(0, 84, 164, 0.05);
  padding: 18px;
  border-radius: 16px;
  border: 1px dashed rgba(0, 84, 164, 0.3);
  font-size: 0.85rem;
  line-height: 1.6;
  color: #94a3b8;
}

.instruction-box strong {
  color: #e2e8f0;
}

.data-summary {
  margin-top: 35px;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin-bottom: 15px;
}

.divider::before, .divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.divider span {
  padding: 0 12px;
  font-size: 0.65rem;
  font-weight: 700;
  color: #475569;
  letter-spacing: 0.2em;
}

.info-table {
  background: rgba(255, 255, 255, 0.01);
  border-radius: 16px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.label {
  font-size: 0.85rem;
  color: #64748b;
}

.value {
  font-size: 0.85rem;
  font-weight: 500;
  color: #cbd5e1;
}

.text-upper { text-transform: uppercase; }

.card-footer {
  padding: 40px;
  text-align: center;
}

.exit-message {
  font-size: 0.8rem;
  color: #475569;
  margin-bottom: 20px;
}

.btn-home {
  width: 100%;
  background: rgba(0, 84, 164, 0.1);
  color: #FFC20E;
  border: 1px solid rgba(0, 84, 164, 0.3);
  padding: 14px;
  border-radius: 14px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-home:hover {
  background: rgba(0, 84, 164, 0.2);
  color: #ffffff;
  border-color: #0054A4;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px -5px rgba(0, 84, 164, 0.4);
}

.copyright {
  text-align: center;
  margin-top: 24px;
  font-size: 0.7rem;
  color: #475569;
  letter-spacing: 0.02em;
}

/* Animasi Entry */
.fade-in {
  animation: fadeInContainer 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInContainer {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 480px) {
  .main-wrapper { padding: 15px; }
  .result-card { border-radius: 24px; }
  .card-header, .card-body, .card-footer { padding-left: 25px; padding-right: 25px; }
}
</style>