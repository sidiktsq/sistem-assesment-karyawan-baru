import { createRouter, createWebHistory } from 'vue-router'
import Start from './views/Start.vue'
import TakeExam from './views/TakeExam.vue'
import Result from './views/Result.vue'
import ReviewerDashboard from './views/ReviewerDashboard.vue'
import ReviewerAssessment from './views/ReviewerAssessment.vue'

const routes = [
  {
    path: '/start/:token',
    name: 'start',
    component: Start,
    props: true
  },
  {
    path: '/exam/:token/take',
    name: 'take-exam',
    component: TakeExam,
    props: true
  },
  {
    path: '/exam/:token/result',
    name: 'result',
    component: Result,
    props: true
  },
  {
    path: '/reviewer',
    name: 'reviewer-dashboard',
    component: ReviewerDashboard
  },
  {
    path: '/reviewer/:token',
    name: 'reviewer-assessment',
    component: ReviewerAssessment,
    props: true
  },
  {
    path: '/',
    name: 'home',
    component: Start
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
