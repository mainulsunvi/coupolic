import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.config.globalProperties.coupolic = window.coupolic || {}

app.use(router)

app.mount('#coupolic-app')

// Handle initial routing based on current admin page
if (window.coupolic && window.coupolic.current_page) {
  const currentPage = window.coupolic.current_page

  // Route to the appropriate page based on current admin page
  switch (currentPage) {
    case 'logs':
      router.push('/logs')
      break
    case 'settings':
      router.push('/settings')
      break
    case 'coupon-generator':
    default:
      router.push('/coupon-wizard')
      break
  }
}
