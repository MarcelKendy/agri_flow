import { createApp } from 'vue'
import '@/style.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'
import { createPinia } from 'pinia'
import router from '@/plugins/router.js'
import { useAuthStore } from '@/stores/auth.js'
import VueTheMask from 'vue-the-mask'
import App from '@/App.vue'
import Particles from "vue3-particles"
import JsonExcel from "vue-json-excel3"
import { Money3Component } from 'v-money3'
import * as masks from '@/utils/masks.js'
import { getRules } from '@/utils/rules.js'

const customDark = {
  dark: true,
  colors: {
    //background: 'linear-gradient(90deg, rgba(49,49,49,1) 0%, rgba(11,0,30,1) 100%)',
    //surface: '#FFFFFF',
    //primary: '#6200EE',
    //'primary-darken-1': '#3700B3',
    //secondary: '#03DAC6',
    //'secondary-darken-1': '#018786',
    //error: '#B00020',
    //info: '#2196F3',
    //success: '#4CAF50',
    //warning: '#FB8C00',
  },
}
const customLight = {
  dark: false, // Set to false for a light theme
  colors: {
    background: 'rgb(20,20,20)',           // Light background color
    surface: 'rgb(240,240,240)',             // Slightly darker surface color
    primary: '#6200EE',             // Primary color (e.g., for buttons)
    'primary-darken-1': '#3700B3',  // Darker shade of primary
    secondary: '#03DAC6',           // Secondary color (e.g., for accents)
    'secondary-darken-1': '#018786',// Darker shade of secondary
    error: '#B00020',               // Error color
    info: '#2196F3',                // Information color
    success: '#4CAF50',            // Success color
    warning: '#FB8C00',            // Warning color
  },
}

const vuetify = createVuetify({
  theme: {
    defaultTheme: 'customDark',
    themes: {
      customDark,
      customLight
    },
    fonts: {
      family: 'Open Sans',
    },
  },
  components,
  directives,
  iconfont: 'mdi'
})

const pinia = createPinia()
const app = createApp(App)

app.use(VueTheMask)
app.use(pinia)
app.use(router)
app.use(Particles)
app.use(vuetify)
app.component("money3", Money3Component)
app.component("downloadExcel", JsonExcel)

const auth = useAuthStore()
if (auth.hasData()) {
  (async () => {
      auth.setIsAuth()
      const response = await auth.validateSession()
  })()
} 

app.config.globalProperties.backend_storage = 'http://localhost/template_project/backend/public/storage/'
app.config.globalProperties.getRules = getRules
for (const mask in masks) {
  app.config.globalProperties[mask] = masks[mask]
}

app.mount('#app')