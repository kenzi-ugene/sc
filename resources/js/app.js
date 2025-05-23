import './bootstrap'
import '../css/app.css';
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './axios' // Import our configured axios

const app = createApp(App)
app.use(router)
app.mount('#app')