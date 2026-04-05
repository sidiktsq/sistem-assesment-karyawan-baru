<template>
  <div class="reviewer-container">
    <!-- Header -->
    <header class="reviewer-header">
      <div class="header-content">
        <h1 class="header-title">Reviewer Dashboard</h1>
        <div class="header-info">
          <span class="assessment-count">{{ assessments.length }} assessments</span>
        </div>
      </div>
    </header>

    <div class="reviewer-content">
      <!-- Loading -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading assessments...</p>
      </div>

      <!-- Assessment List -->
      <div v-else class="assessment-list">
        <div v-if="assessments.length === 0" class="empty-state">
          <div class="empty-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <h3>No Assessments Found</h3>
          <p>No completed assessments available for review.</p>
        </div>

        <div v-else class="assessment-grid">
          <div 
            v-for="assessment in assessments" 
            :key="assessment.id"
            class="assessment-card"
            @click="openAssessment(assessment)"
          >
            <div class="card-header">
              <h3 class="candidate-name">{{ assessment.candidate.name }}</h3>
              <span class="status-badge" :class="assessment.status">
                {{ assessment.status }}
              </span>
            </div>
            
            <div class="card-content">
              <div class="assessment-info">
                <p class="assessment-title">{{ assessment.assessment.title }}</p>
                <p class="position">{{ assessment.candidate.position_applied }}</p>
              </div>
              
              <div class="score-info">
                <div class="score-display">
                  <span class="current-score">{{ assessment.total_score || 0 }}</span>
                  <span class="max-score">/ {{ assessment.max_score || 0 }}</span>
                </div>
                <div class="percentage">
                  {{ assessment.percentage || 0 }}%
                </div>
              </div>
            </div>
            
            <div class="card-footer">
              <div class="completion-info">
                <span v-if="assessment.has_essay_questions" class="essay-indicator">
                  📝 Has Essay
                </span>
                <span class="completion-date">
                  {{ formatDate(assessment.completed_at) }}
                </span>
              </div>
              <button class="review-btn">
                {{ assessment.status === 'reviewed' ? 'Review' : 'Grade' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ReviewerDashboard',
  data() {
    return {
      loading: true,
      assessments: []
    }
  },
  async mounted() {
    await this.loadAssessments()
  },
  methods: {
    async loadAssessments() {
      try {
        const response = await axios.get('/api/reviewer/assessments')
        this.assessments = response.data
        console.log('Assessments loaded:', this.assessments)
      } catch (error) {
        console.error('Error loading assessments:', error)
        alert('Failed to load assessments')
      } finally {
        this.loading = false
      }
    },
    
    openAssessment(assessment) {
      this.$router.push({
        name: 'reviewer-assessment',
        params: { token: assessment.access_token }
      })
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.reviewer-container {
  min-height: 100vh;
  background-color: #f8fafc;
}

.reviewer-header {
  background: white;
  border-bottom: 1px solid #e2e8f0;
  padding: 1.5rem 0;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.assessment-count {
  background: #3b82f6;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.reviewer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
}

.loading-spinner {
  width: 2rem;
  height: 2rem;
  border: 2px solid #e2e8f0;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 4rem;
  color: #64748b;
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1rem;
  color: #cbd5e1;
}

.empty-state h3 {
  margin: 0 0 0.5rem;
  color: #475569;
}

.assessment-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

.assessment-card {
  background: white;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.assessment-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.candidate-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
}

.status-badge.completed {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.reviewed {
  background: #d1fae5;
  color: #065f46;
}

.card-content {
  margin-bottom: 1rem;
}

.assessment-info {
  margin-bottom: 1rem;
}

.assessment-title {
  font-weight: 500;
  color: #374151;
  margin: 0 0 0.25rem;
}

.position {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.score-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 0.5rem;
}

.score-display {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
}

.current-score {
  color: #3b82f6;
}

.max-score {
  color: #9ca3af;
}

.percentage {
  font-size: 1.125rem;
  font-weight: 500;
  color: #059669;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.completion-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.essay-indicator {
  font-size: 0.75rem;
  color: #7c3aed;
  font-weight: 500;
}

.completion-date {
  font-size: 0.75rem;
  color: #9ca3af;
}

.review-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.review-btn:hover {
  background: #2563eb;
}
</style>
