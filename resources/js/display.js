import './bootstrap'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import DisplayApp from './Display/DisplayApp.vue'

const app = createApp(DisplayApp)
app.use(createPinia())
app.mount('#display-app')
