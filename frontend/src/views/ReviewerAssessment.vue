<template>
  <div class="reviewer-test">
    <h1>Reviewer Interface Test</h1>
    
    <div v-if="loading">Loading...</div>
    
    <div v-else>
      <h2>Assessment Data</h2>
      <p><strong>Title:</strong> {{ assessmentData.assessment?.title }}</p>
      <p><strong>Candidate:</strong> {{ assessmentData.candidate?.name }}</p>
      <p><strong>Status:</strong> {{ assessmentData.status }}</p>
      <p><strong>Total Score:</strong> {{ assessmentData.total_score }}</p>
      <p><strong>Max Score:</strong> {{ assessmentData.max_score }}</p>
      <p><strong>Percentage:</strong> {{ getPercentage() }}%</p>
      <p><strong>Result:</strong> <span :class="getResultClass()">{{ getResultText() }}</span></p>
      
      <h3>Questions</h3>
      <div v-for="question in assessmentData.questions" :key="question.id">
        <p><strong>Type:</strong> {{ question.type }}</p>
        <p><strong>Question:</strong> {{ question.question_text }}</p>
        <p><strong>User Answer:</strong> {{ question.user_answer }}</p>
        <p><strong>Current Score:</strong> {{ question.current_score }}</p>
        <p><strong>Max Score:</strong> {{ question.score }}</p>
        <p><strong>Is Graded:</strong> {{ question.is_graded ? 'YES' : 'NO' }}</p>
        
        <!-- Essay grading -->
        <div v-if="question.type === 'essay'">
          <h4>Essay Grading</h4>
          <label :for="'score-' + question.id">Score:</label>
          <input 
            :id="'score-' + question.id"
            v-model.number="gradedScores[question.id]"
            type="number"
            min="0"
            :max="question.score"
            @input="updateScore(question.id, $event.target.value)"
          >
          <span>/ {{ question.score }}</span>
          
          <br><br>
          
          <label :for="'feedback-' + question.id">Feedback:</label>
          <textarea 
            :id="'feedback-' + question.id"
            v-model="feedbacks[question.id]"
            rows="3"
            @input="updateFeedback(question.id, $event.target.value)"
          ></textarea>
          
          <br><br>
          
          <button @click="saveGrades">Save Grades</button>
          <button @click="forceRefresh">Refresh</button>
          
          <div v-if="assessmentData.status === 'reviewed'" class="send-results-section">
            <hr>
            <h3>Finalize Assessment</h3>
            <p v-if="assessmentData.result_sent_at">
              Results already sent on {{ formatDate(assessmentData.result_sent_at) }}
            </p>
            <button 
              v-else
              class="btn-send-results"
              @click="sendResults"
              :disabled="saving"
            >
              {{ saving ? 'Sending...' : 'Send Results to Candidate' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ReviewerAssessmentTest',
  data() {
    return {
      loading: true,
      saving: false,
      assessmentData: {
        assessment: null,
        candidate: null,
        questions: []
      },
      gradedScores: {},
      feedbacks: {}
    }
  },
  computed: {
    hasEssayQuestions() {
      return this.assessmentData.questions.some(q => q.type === 'essay')
    }
  },
  methods: {
    async loadAssessmentDetails() {
      try {
        this.loading = true
        const token = 'ebfefa53497fbdac50bca7a7f18152ba'
        const response = await axios.get(`/api/reviewer/assessment/${token}/details`)
        this.assessmentData = response.data
        
        // Initialize graded scores and feedbacks for essay questions
        this.assessmentData.questions.forEach(question => {
          if (question.type === 'essay') {
            this.$set(this.gradedScores, question.id, question.current_score || 0)
            this.$set(this.feedbacks, question.id, question.feedback || '')
          }
        })
        
        console.log('Assessment details loaded:', this.assessmentData)
        console.log('Graded scores:', this.gradedScores)
        console.log('Feedbacks:', this.feedbacks)
      } catch (error) {
        console.error('Error loading assessment details:', error)
        alert('Failed to load assessment details')
      } finally {
        this.loading = false
      }
    },
    
    updateScore(questionId, value) {
      console.log('Updating score:', questionId, value)
      this.$set(this.gradedScores, questionId, parseInt(value) || 0)
      console.log('Graded scores now:', this.gradedScores)
    },
    
    updateFeedback(questionId, value) {
      console.log('Updating feedback:', questionId, value)
      this.$set(this.feedbacks, questionId, value)
      console.log('Feedbacks now:', this.feedbacks)
    },
    
    async saveGrades() {
      try {
        this.saving = true
        
        const token = 'ebfefa53497fbdac50bca7a7f18152ba'
        
        // Prepare answers data for essay questions only
        const essayAnswers = this.assessmentData.questions
          .filter(q => q.type === 'essay')
          .map(q => ({
            answer_id: q.answer_id,
            score: this.gradedScores[q.id] || 0,
            feedback: this.feedbacks[q.id] || ''
          }))
        
        if (essayAnswers.length === 0) {
          alert('No essay questions to grade')
          return
        }
        
        console.log('Saving grades:', essayAnswers)
        
        const response = await axios.post(`/api/reviewer/assessment/${token}/grade`, {
          answers: essayAnswers
        })
        
        console.log('Grades saved:', response.data)
        alert('Grades saved successfully!')
        
        // Reload data to get updated scores
        await this.loadAssessmentDetails()
        
      } catch (error) {
        console.error('Error saving grades:', error)
        alert('Failed to save grades: ' + (error.response?.data?.message || error.message))
      } finally {
        this.saving = false
      }
    },
    
    forceRefresh() {
      console.log('Force refreshing assessment data...')
      this.loading = true
      this.loadAssessmentDetails()
      this.loading = false
    },
    
    async sendResults() {
      if (!confirm('Are you sure you want to send the results to the candidate?')) return
      
      try {
        this.saving = true
        // We'll add an API endpoint for this if it doesn't exist, 
        // or just re-use the Filament action logic.
        // For now, let's assume we need a new API route for this specifically for the Vue UI.
        const response = await axios.post(`/api/reviewer/assessment/${this.assessmentData.access_token}/send-results`)
        alert('Results sent successfully!')
        await this.loadAssessmentDetails()
      } catch (error) {
        console.error('Error sending results:', error)
        alert('Failed to send results: ' + (error.response?.data?.message || error.message))
      } finally {
        this.saving = false
      }
    },
    
    // Helper methods
    getMaxScore() {
      if (!this.assessmentData.questions) return 0
      return this.assessmentData.questions.reduce((total, q) => total + (q.score || 0), 0)
    },
    
    getPercentage() {
      const maxScore = this.getMaxScore()
      if (maxScore === 0) return 0
      return Math.round(((this.assessmentData.total_score || 0) / maxScore) * 100)
    },
    
    getResultText() {
      const percentage = this.getPercentage()
      const passingScore = this.assessmentData.assessment?.passing_score || 70
      return percentage >= passingScore ? 'PASS' : 'FAIL'
    },
    
    getResultClass() {
      const result = this.getResultText()
      return {
        'result-pass': result === 'PASS',
        'result-fail': result === 'FAIL'
      }
    }
  }
}
</script>

<style scoped>
.reviewer-test {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  font-family: Arial, sans-serif;
}

.reviewer-test h1 {
  color: #2563eb;
  text-align: center;
}

.reviewer-test h2 {
  color: #333;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
}

.reviewer-test p {
  margin-bottom: 0.5rem;
  line-height: 1.5;
}

.reviewer-test h3 {
  color: #2563eb;
  margin-bottom: 1rem;
}

.reviewer-test strong {
  color: #333;
}

.result-pass {
  color: #27ae60;
  background-color: #d4edda;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.result-fail {
  color: #e74c3c;
  background-color: #f8d7da;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

input, textarea {
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  padding: 0.5rem;
  font-size: 1rem;
  margin: 0.5rem 0;
}

button {
  background-color: #2563eb;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.25rem;
  cursor: pointer;
  margin-right: 0.5rem;
}

button:hover {
  background-color: #1d4ed8;
}

label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: bold;
}
.btn-send-results {
  background-color: #059669;
  margin-top: 1rem;
}
.btn-send-results:hover {
  background-color: #047857;
}
.send-results-section {
  margin-top: 2rem;
  padding-top: 1rem;
}
</style>
