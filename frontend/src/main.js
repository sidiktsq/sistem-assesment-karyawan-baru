import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'
import './style.css'
import App from './App.vue'
import router from './router'

// Configure axios base URL
axios.defaults.baseURL = 'http://localhost:8000'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

app.mount('#app')
