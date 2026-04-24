<template>
  <div class="exam-container">
    <!-- Header -->
    <header class="exam-header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="exam-title">{{ assessment?.assessment?.title || 'Assessment' }}</h1>
        </div>
        <div class="header-right">
          <div class="candidate-info">
            Kandidat: <span class="candidate-name">{{ candidate?.name || 'Guest' }}</span>
          </div>
          <div class="timer-container">
            <svg class="timer-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="timer-text" :class="{ 'timer-warning': timeRemaining < 300 }">
              {{ formatTime(timeRemaining) }}
            </span>
          </div>
        </div>
      </div>
    </header>

    <div class="exam-content">
      <div class="exam-layout">
        <!-- Sidebar -->
        <div class="sidebar">
          <div class="sidebar-card">
            <h3 class="sidebar-title">Navigasi Soal</h3>
            <div class="question-nav">
              <button
                v-for="(question, index) in questions"
                :key="question.id"
                @click="currentQuestionIndex = index"
                :class="[
                  'question-btn',
                  getQuestionStatus(question.id) === 'answered' 
                    ? 'question-btn-answered' 
                    : getQuestionStatus(question.id) === 'current'
                    ? 'question-btn-current'
                    : 'question-btn-unanswered'
                ]"
              >
                {{ index + 1 }}
              </button>
            </div>
            
            <div class="sidebar-info">
              <div class="info-item">
                <span class="info-label">Progress:</span>
                <span class="info-value">{{ Object.keys(answers).length }}/{{ questions.length }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ loading ? 'Loading...' : 'Active' }}</span>
              </div>
            </div>
            
            <div class="sidebar-actions">
              <button 
                @click="previousQuestion" 
                :disabled="currentQuestionIndex === 0"
                class="nav-btn nav-btn-prev"
              >
                ← Previous
              </button>
              <button 
                @click="nextQuestion" 
                :disabled="currentQuestionIndex === questions.length - 1"
                class="nav-btn nav-btn-next"
              >
                Next →
              </button>
            </div>
            
            <div class="sidebar-legend">
              <div class="legend-item">
                <div class="legend-color legend-answered"></div>
                <span>Sudah dijawab</span>
              </div>
              <div class="legend-item">
                <div class="legend-color legend-current"></div>
                <span>Soal saat ini</span>
              </div>
              <div class="legend-item">
                <div class="legend-color legend-unanswered"></div>
                <span>Belum dijawab</span>
              </div>
            </div>
            
            <button
              @click="submitExam"
              :disabled="submitting"
              class="submit-btn"
            >
              <span v-if="submitting">Menyimpan...</span>
              <span v-else>Selesai & Submit</span>
            </button>
          </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
          <div v-if="loading" class="loading-container">
            <div class="loading-spinner"></div>
            <p class="loading-text">Memuat soal...</p>
          </div>

          <div v-else-if="currentQuestion" class="question-card">
            <div class="question-header">
              <div class="question-meta">
                <h2 class="question-number">Soal {{ currentQuestionIndex + 1 }}</h2>
                <div class="meta-right">
                  <span class="difficulty-badge" :class="currentQuestion.difficulty">{{ currentQuestion.difficulty }}</span>
                  <span class="question-type">{{ formatQuestionType(currentQuestion.type) }}</span>
                </div>
              </div>
              <div class="question-text">
                <p>{{ currentQuestion.question || currentQuestion.question_text }}</p>
              </div>
            </div>
            
            <div class="question-content">
              <!-- Multiple Choice -->
              <div v-if="currentQuestion.type.toLowerCase() === 'multiple_choice'" class="multiple-choice">
                <div class="options-list">
                  <div 
                    v-for="(option, index) in currentQuestion.options"
                    :key="index"
                    class="option-item"
                    @click="selectAnswer(getOptionKey(option))"
                    :class="{ 'option-selected': currentAnswer === getOptionKey(option) }"
                  >
                    <div class="option-content-wrapper">
                      <div class="option-indicator">
                        <span class="option-letter">{{ getOptionKey(option) }}</span>
                      </div>
                      <div class="option-text">{{ getOptionText(option) }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Essay -->
              <div v-else-if="currentQuestion.type.toLowerCase() === 'essay'" class="essay">
                <textarea
                  v-model="currentAnswer"
                  class="essay-textarea"
                  placeholder="Ketik jawaban lengkap Anda di sini..."
                  rows="8"
                ></textarea>
                <div class="essay-footer">
                  <span class="word-count">{{ (currentAnswer || '').split(/\s+/).filter(x => x).length }} kata</span>
                </div>
              </div>

              <!-- True/False -->
              <div v-else-if="currentQuestion.type.toLowerCase() === 'true_false'" class="true-false">
                <div class="tf-options">
                  <div 
                    class="tf-option"
                    @click="selectAnswer('True')"
                    :class="{ 'tf-selected': currentAnswer === 'True' }"
                  >
                    <div class="tf-indicator"></div>
                    <span class="tf-label">Benar (True)</span>
                  </div>
                  <div 
                    class="tf-option"
                    @click="selectAnswer('False')"
                    :class="{ 'tf-selected': currentAnswer === 'False' }"
                  >
                    <div class="tf-indicator"></div>
                    <span class="tf-label">Salah (False)</span>
                  </div>
                </div>
              </div>

              <!-- Short Answer -->
              <div v-else-if="currentQuestion.type.toLowerCase() === 'short_answer'" class="short-answer">
                <div class="input-wrapper">
                  <input
                    type="text"
                    v-model="currentAnswer"
                    class="short-answer-input"
                    placeholder="Ketik jawaban singkat..."
                  >
                </div>
              </div>

              <!-- Personality -->
              <div v-else-if="currentQuestion.type.toLowerCase() === 'personality'" class="personality">
                <div class="personality-options">
                  <div 
                    v-for="(option, index) in (getQuestionOptions(currentQuestion))"
                    :key="index"
                    class="personality-option"
                    @click="selectAnswer(getOptionKey(option))"
                    :class="{ 'personality-selected': currentAnswer === getOptionKey(option) }"
                  >
                    <div class="personality-content">
                      <div class="personality-scale">{{ getOptionKey(option) }}</div>
                      <div class="personality-text">{{ getOptionText(option) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="no-question">
            <p>Tidak ada soal yang tersedia</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'

export default {
  name: 'TakeExam',
  data() {
    return {
      loading: true,
      submitting: false,
      questions: [],
      answers: {},
      currentQuestionIndex: 0,
      timeRemaining: 0,
      timer: null,
      assessment: null,
      candidate: null
    }
  },
  computed: {
    currentQuestion() {
      return this.questions[this.currentQuestionIndex] || null
    },
    currentAnswer: {
      get() {
        return this.answers[this.currentQuestion?.id] || ''
      },
      set(value) {
        if (this.currentQuestion) {
          this.answers[this.currentQuestion.id] = value
          this.saveAnswer()
        }
      }
    }
  },
  async mounted() {
    await this.loadQuestions()
    this.startTimer()
    
    // Auto-save every 30 seconds
    this.autoSaveInterval = setInterval(() => {
      this.saveAllAnswers()
    }, 30000)
  },
  beforeUnmount() {
    if (this.timer) {
      clearInterval(this.timer)
    }
    if (this.autoSaveInterval) {
      clearInterval(this.autoSaveInterval)
    }
  },
  methods: {
    async loadQuestions() {
      try {
        const token = this.$route.params.token
        const response = await axios.get(`/api/exam/${token}/questions`)
        
        console.log('API Response:', response.data)
        
        if (!response.data || response.data.error) {
          throw new Error(response.data?.error || 'Failed to load questions')
        }
        
        this.questions = response.data.questions || []
        this.assessment = response.data.assessment || {}
        this.candidate = response.data.candidate || null
        
        // Safe time_remaining access - FIXED
        if (response.data.assessment && response.data.assessment.time_remaining !== undefined) {
          this.timeRemaining = response.data.assessment.time_remaining
          console.log('Time remaining set to:', this.timeRemaining)
        } else if (response.data.time_remaining !== undefined) {
          this.timeRemaining = response.data.time_remaining
          console.log('Time remaining set to (alt):', this.timeRemaining)
        } else {
          this.timeRemaining = 900 // Default 15 minutes
          console.log('Time remaining set to default:', this.timeRemaining)
        }
        
        // Load existing answers
        this.questions.forEach(question => {
          if (question.user_answer) {
            this.answers[question.id] = question.user_answer
          }
        })
      } catch (error) {
        console.error('Error loading questions:', error)
        this.$router.push({ name: 'result', params: { token } })
      } finally {
        this.loading = false
      }
    },
    
    parseOption(opt) {
      if (typeof opt === 'string' && opt.trim().startsWith('{')) {
        try {
          return JSON.parse(opt)
        } catch (e) {
          return opt
        }
      }
      return opt
    },
    
    getOptionKey(opt) {
      const parsed = this.parseOption(opt)
      if (typeof parsed === 'object' && parsed !== null) {
        return parsed.option || parsed.label || parsed.value || ''
      }
      return parsed
    },
    
    getOptionText(opt) {
      const parsed = this.parseOption(opt)
      if (typeof parsed === 'object' && parsed !== null) {
        return parsed.text || parsed.option_text || ''
      }
      return parsed
    },

    getQuestionOptions(question) {
      if (question.options && question.options.length > 0) {
        return question.options
      }
      
      // Default Likert Scale for Personality questions if no options provided
      if (question.type.toLowerCase() === 'personality') {
        return [
          { option: '1', text: 'Sangat Tidak Setuju', value: 1 },
          { option: '2', text: 'Tidak Setuju', value: 2 },
          { option: '3', text: 'Ragu-ragu', value: 3 },
          { option: '4', text: 'Setuju', value: 4 },
          { option: '5', text: 'Sangat Setuju', value: 5 }
        ]
      }
      
      return []
    },
    
    selectAnswer(value) {
      this.currentAnswer = value
    },
    
    async saveAnswer() {
      try {
        const token = this.$route.params.token
        await axios.post(`/api/exam/${token}/save-answer`, {
          question_id: this.currentQuestion.id,
          answer: this.currentAnswer
        })
      } catch (error) {
        console.error('Error saving answer:', error)
      }
    },
    
    async saveAllAnswers() {
      try {
        const token = this.$route.params.token
        await axios.post(`/api/exam/${token}/save-answers`, {
          answers: this.answers
        })
      } catch (error) {
        console.error('Error saving answers:', error)
      }
    },
    
    nextQuestion() {
      if (this.currentQuestionIndex < this.questions.length - 1) {
        this.currentQuestionIndex++
      }
    },
    
    previousQuestion() {
      if (this.currentQuestionIndex > 0) {
        this.currentQuestionIndex--
      }
    },
    
    getQuestionStatus(questionId) {
      if (this.currentQuestion?.id === questionId) return 'current'
      return this.answers[questionId] ? 'answered' : 'unanswered'
    },
    
    getUnansweredQuestions() {
      return this.questions.filter(question => !this.answers[question.id])
    },
    
    formatQuestionType(type) {
      if (!type) return ''
      type = type.toLowerCase()
      const types = {
        'multiple_choice': 'Pilihan Ganda',
        'essay': 'Esai',
        'personality': 'Kepribadian',
        'true_false': 'Benar/Salah',
        'short_answer': 'Jawaban Singkat'
      }
      return types[type] || type
    },
    
    formatTime(seconds) {
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = seconds % 60
      
      if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
      }
      return `${minutes}:${secs.toString().padStart(2, '0')}`
    },
    
    startTimer() {
      if (this.timeRemaining > 0) {
        console.log('Starting timer with', this.timeRemaining, 'seconds')
        this.timer = setInterval(() => {
          if (this.timeRemaining > 0) {
            this.timeRemaining--
            if (this.timeRemaining % 10 === 0) {
              console.log('Timer tick:', this.timeRemaining, 'seconds remaining')
            }
          } else {
            console.log('Time expired, submitting exam')
            clearInterval(this.timer)
            this.submitExam(true) // Auto submit without prompt
          }
        }, 1000)
      } else {
        console.log('No time remaining, not starting timer')
      }
    },
    
    async submitExam(isAuto = false) {
      // Check if all questions are answered
      const unansweredQuestions = this.getUnansweredQuestions()
      
      if (unansweredQuestions.length > 0) {
        const questionNumbers = unansweredQuestions.map(q => {
          const index = this.questions.findIndex(que => que.id === q.id)
          return `Soal ${index + 1}`
        }).join(', ')
        
        Swal.fire({
          title: 'Soal Belum Lengkap!',
          html: `<p>Berikut soal yang masih belum terjawab:</p><p style="font-weight: bold; color: #f87171;">${questionNumbers}</p><p>Silakan lengkapi semua soal sebelum mengirim.</p>`,
          icon: 'warning',
          confirmButtonColor: '#ef4444',
          background: '#1e293b',
          color: '#f8fafc'
        })
        return
      }
      
      if (!isAuto) {
        const result = await Swal.fire({
          title: 'Selesaikan Assessment?',
          text: "Pastikan semua soal sudah terjawab dengan benar.",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#334155',
          confirmButtonText: 'Ya, Selesai!',
          cancelButtonText: 'Batal',
          background: '#1e293b',
          color: '#f8fafc'
        })

        if (!result.isConfirmed) return
      }
      
      this.submitting = true
      try {
        const token = this.$route.params.token
        await axios.post(`/api/exam/${token}/submit`, {
          answers: this.answers
        })
        
        await Swal.fire({
          title: 'Berhasil!',
          text: 'Assessment telah dikirim.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          background: '#1e293b',
          color: '#f8fafc'
        })

        this.$router.push({ name: 'result', params: { token } })
      } catch (error) {
        console.error('Error submitting exam:', error)
        Swal.fire({
          title: 'Error!',
          text: 'Terjadi kesalahan saat mengirim jawaban.',
          icon: 'error',
          confirmButtonColor: '#ef4444',
          background: '#1e293b',
          color: '#f8fafc'
        })
      } finally {
        this.submitting = false
      }
    }
  }
}
</script>

<style scoped>
/* Main Background & Base */
.exam-container {
  min-height: 100vh;
  background: radial-gradient(circle at top right, #1e293b, #0f172a);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  color: #f8fafc;
  padding-bottom: 3rem;
}

/* Header Section */
.exam-header {
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(51, 65, 85, 0.8);
  padding: 1rem 2rem;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

.exam-title {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.025em;
  color: #FFC20E; /* UTB Yellow accent */
  margin: 0;
  text-transform: uppercase;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.candidate-info {
  color: #94a3b8;
  font-size: 0.875rem;
}

.candidate-name {
  font-weight: 600;
  color: #f1f5f9;
}

.timer-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(0, 84, 164, 0.1);
  border-radius: 0.75rem;
  border: 1px solid rgba(0, 84, 164, 0.3);
}

.timer-icon {
  width: 1.1rem;
  height: 1.1rem;
  color: #FFC20E;
}

.timer-text {
  font-weight: 700;
  color: #FFC20E;
  font-family: 'JetBrains Mono', monospace;
}

.timer-warning {
  color: #f87171;
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.02); }
}

/* Layout */
.exam-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.exam-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 2rem;
}

/* Sidebar Styling */
.sidebar-card {
  background: #1e293b;
  border-radius: 1.25rem;
  border: 1px solid rgba(51, 65, 85, 0.5);
  padding: 1.5rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}

.sidebar-title {
  font-size: 1rem;
  font-weight: 700;
  color: #f1f5f9;
  margin-bottom: 1.25rem;
}

.question-nav {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.6rem;
  margin-bottom: 1.5rem;
}

.question-btn {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.6rem;
  border: 1px solid #334155;
  background: #0f172a;
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.question-btn:hover {
  border-color: #0054A4;
  color: #0054A4;
  background: rgba(0, 84, 164, 0.05);
}

.question-btn-current {
  background: #0054A4 !important;
  border-color: #FFC20E !important;
  color: white !important;
  box-shadow: 0 0 15px rgba(0, 84, 164, 0.4);
}

.question-btn-answered {
  background: rgba(0, 166, 81, 0.1) !important;
  border-color: #00A651 !important;
  color: #00A651 !important;
}

/* Sidebar Info & Legend */
.sidebar-info {
  background: #0f172a;
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  padding: 0.25rem 0;
}

.info-label { color: #64748b; }
.info-value { color: #f1f5f9; font-weight: 600; }

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.5rem;
}

.legend-color {
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 2px;
}

.legend-answered { background: #00A651; border: 1px solid #00A651; }
.legend-current { background: #0054A4; border: 1px solid #FFC20E; }
.legend-unanswered { background: #0f172a; border: 1px solid #334155; }

/* Buttons */
.sidebar-actions {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.nav-btn {
  flex: 1;
  padding: 0.6rem;
  border-radius: 0.75rem;
  background: #334155;
  border: 1px solid transparent;
  color: #f1f5f9;
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
  transition: 0.2s;
}

.nav-btn:hover:not(:disabled) {
  background: #475569;
}

.submit-btn {
  width: 100%;
  padding: 1rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.025em;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
  transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

/* Main Question Card */
.main-content {
  background: #1e293b;
  border-radius: 1.5rem;
  border: 1px solid rgba(51, 65, 85, 0.5);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  min-height: 500px;
}

.question-card {
  padding: 2.5rem;
}

.question-header {
  margin-bottom: 2rem;
}

.question-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.question-number {
  font-size: 1.5rem;
  color: #FFC20E;
  margin: 0;
}

.question-type {
  padding: 0.4rem 1rem;
  background: rgba(0, 84, 164, 0.1);
  color: #0054A4;
  border: 1px solid rgba(0, 84, 164, 0.2);
  border-radius: 2rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: 700;
}

.question-text {
  font-size: 1.25rem;
  line-height: 1.7;
  color: #f1f5f9;
  font-weight: 500;
}

/* Options Styling */
.option-item, .tf-option, .personality-option {
  padding: 1.25rem;
  background: #0f172a;
  border: 2px solid #334155;
  border-radius: 1rem;
  margin-bottom: 1rem;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.option-item:hover, .tf-option:hover, .personality-option:hover {
  border-color: #38bdf8;
  background: rgba(56, 189, 248, 0.05);
}

.option-selected, .tf-selected, .personality-selected {
  border-color: #0ea5e9 !important;
  background: rgba(14, 165, 233, 0.1) !important;
}

.option-text, .tf-label, .personality-text, .option-letter {
  color: #cbd5e1;
}

.option-selected .option-text, 
.option-selected .option-letter,
.tf-selected .tf-label {
  color: #38bdf8;
  font-weight: 600;
}

/* Inputs */
.essay-textarea, .short-answer-input {
  width: 100%;
  background: #0f172a;
  border: 2px solid #334155;
  border-radius: 1rem;
  padding: 1.25rem;
  color: #f1f5f9;
  font-size: 1rem;
  transition: 0.2s;
}

.essay-textarea:focus, .short-answer-input:focus {
  outline: none;
  border-color: #0ea5e9;
  box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
}

/* Debug Box */
.question-card > div[style*="background"] {
  background: #0f172a !important;
  border: 1px solid #334155 !important;
  color: #64748b !important;
}

/* Loading */
.loading-spinner {
  border: 4px solid #1e293b;
  border-top: 4px solid #38bdf8;
}

/* Responsive */
@media (max-width: 768px) {
  .exam-layout { grid-template-columns: 1fr; }
  .sidebar { position: relative; top: 0; }
  .exam-content { padding: 1rem; }
  .question-card { padding: 1.5rem; }
}
</style>
