import './assets/main.css'
import axios from 'axios'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import '@/assets/landing.css' 
const app = createApp(App)

app.use(router)

app.mount('#app')
