<template>
  <v-app>
    <v-app-bar style="transition: 1.5s !important" v-if="$route.name != 'login' && auth.is_auth && auth.hasData()" :color="dark_theme ? 'rgba(0, 0, 0, 0.5)' : 'rgba(200, 200, 200, 0.5)'"
      dark :elevation="5" scroll-threshold="200" scroll-behavior="fade-image elevate"
      :height="appbar_density">
      <template v-slot:image>
        <v-img :gradient="dark_theme ? 'to bottom right, rgba(8, 100, 100, 0.3), rgba(80, 160, 50, 0.4)' : 'to bottom right, rgba(80, 130, 10, 0.8), rgba(50, 80, 75, 0.8)'"></v-img>
      </template>
      <v-app-bar-nav-icon class="white" @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-img class="ma-1" style="max-width: 64px" src="media/icons/logo.png" />
      <strong style="font-size: 20px" class="mx-2 mt-1 white">{{ 'Agri Flow'
      }}</strong>
      <v-tooltip location="bottom" content-class="tooltip-orange">
        <template v-slot:activator="{ props }">
          <v-btn v-if="password_expire_time['days'] <= 5" v-bind="props" @click="openDialogEditProfile(true)"
            class="password-expire-button ml-5" size="small" elevation="20" prepend-icon="mdi-alert"
            append-icon="mdi-lock-reset">
            <strong>{{ password_expire_time['text'] }}</strong>
          </v-btn>
        </template>
        Clique aqui e a renove. (Deve ser diferente das 5 últimas)
      </v-tooltip>
      <v-spacer></v-spacer>
      <v-tooltip text="Mudar tema" :content-class="dark_theme_model ? 'tooltip-default-dark' : 'tooltip-default-light'">
        <template v-slot:activator="{ props }">
          <div v-bind="props">
            <v-switch v-model="dark_theme_model" @click="update_user_theme = true" :loading="loading_theme" ripple
              :disabled="loading_theme"
              :class="(dark_theme_model ? 'custom-switch-dark' : 'custom-switch-light') + ' mt-6 mr-2'"
              false-icon="mdi-white-balance-sunny" true-icon="mdi-weather-night">
              <template v-slot:prepend>
                <v-icon :color="dark_theme_model ? 'black' : 'white'">mdi-theme-light-dark</v-icon>
              </template>
            </v-switch>
          </div>
        </template>
      </v-tooltip>

    </v-app-bar>
    <v-navigation-drawer temporary v-model="drawer" v-if="$route.name != 'login' && auth.is_auth && auth.hasData()">
      <v-hover>
        <template v-slot:default="{ isHovering, props }">
          <div class="list-container" style="color: white !important;">
            <v-img cover :class="dark_theme ? 'background-image-dark' : 'background-image'"
              :style="isHovering ? (dark_theme ? 'opacity: 0.2' : 'opacity: 0.7') : ''"
              src="media/images/backgrounds/circuitsbg2.png" />
            <v-list v-bind="props">
              <v-tooltip :content-class="dark_theme ? 'tooltip-default-dark' : 'tooltip-default-light'">
                <template v-slot:activator="{ props }">
                  <v-fade-transition>
                    <v-btn v-if="isHovering" v-bind="props" @click.stop="openDialogEditProfile()"
                      style="position: absolute; right: 5px; top: 5px;" variant="elevated" color="orange"
                      icon="mdi-account-edit" size="x-small"></v-btn>
                  </v-fade-transition>
                </template>
                <span>Editar Perfil</span>
              </v-tooltip>
              <v-tooltip :content-class="dark_theme ? 'tooltip-default-dark' : 'tooltip-default-light'"
                text="Meu Perfil">
                <template v-slot:activator="{ props }">
                  <router-link :to="{ name: 'profile' }">
                    <v-col align="center" v-bind="props" class="py-1">
                      <v-avatar :size="isHovering ? '92' : '84'" style="transition: 0.4s ease-in-out;"
                        :style="isHovering ? 'border: solid 2px white' : ''">
                        <v-img v-if="auth.user.photo" :src="backend_storage + auth.user.photo" cover
                          :style="isHovering ? 'opacity: 0.6;' : ''" lazy-src="media/images/man.png">
                          <template v-slot:placeholder>
                            <div class="d-flex align-center justify-center fill-height">
                              <v-progress-circular :width="(84 * 4) / 84" :size="84" color="grey-lighten-4"
                                indeterminate></v-progress-circular>
                            </div>
                          </template>
                        </v-img>
                        <v-img v-else src="media/images/man.png" style="transition: 0.4s ease-in-out"
                          :style="'object-fit: cover; width: ' + (isHovering ? '92px' : '84px;')" />
                        <v-scale-transition>
                          <v-icon v-if="isHovering" style="position: absolute;" class="text-shadow-black-2"
                            color="white">mdi-account-eye</v-icon>
                        </v-scale-transition>
                      </v-avatar>
                    </v-col>
                  </router-link>
                </template>
              </v-tooltip>
              <v-list-item>
                <v-list-item-title align="center">
                  <strong :class="dark_theme ? 'text-shadow-black-2' : 'white text-shadow-black-2'">{{
                    auth.user.full_name }}</strong>
                </v-list-item-title>
                <v-slide-y-reverse-transition>
                  <v-list-item-subtitle v-show="!isHovering"
                    :class="dark_theme ? 'text-shadow-black-2' : 'white text-shadow-black-2'"
                    :style="dark_theme ? '' : 'opacity: 0.8'" align="center">
                    {{ auth.user.email }}
                  </v-list-item-subtitle>
                </v-slide-y-reverse-transition>
              </v-list-item>
            </v-list>
          </div>
        </template>
      </v-hover>

      <v-divider></v-divider>
      <v-list density="compact" nav class="flex-grow-1">
        <v-hover v-for="(route, index) in basic_routes" :key="index">
          <template v-slot:default="{ isHovering, props }">
            <v-list-item v-bind="props" :to="route.to" :title="route.title" :value="route.value"
              :style="dark_theme ? '' : 'color: rgb(0,0,0)'">
              <template v-slot:prepend>
                <div :style="isHovering ? 'transform: translateX(5px)' : ''" style="transition: 0.2s">
                  <v-img v-if="route.img" :src="'media/icons/' + route.img" width="26" class="mr-7"></v-img>
                  <v-icon v-else size="26">{{ route.icon }}</v-icon>
                </div>
              </template>
            </v-list-item>
          </template>
        </v-hover>
        <div v-if="auth.user.level >= 1 && adm_routes.length">
          <v-divider></v-divider>
          <v-list-subheader>Gestão do Sistema</v-list-subheader>
          <v-hover v-for="(route, index) in adm_routes" :key="index">
            <template v-slot:default="{ isHovering, props }">
              <v-list-item v-bind="props" :to="route.to" :title="route.title" :value="route.value"
                :style="dark_theme ? '' : 'color: rgb(0,0,0)'">
                <template v-slot:prepend>
                  <div :style="isHovering ? 'transform: translateX(5px)' : ''" style="transition: 0.2s">
                    <v-img v-if="route.img" :src="'media/icons/' + route.img" width="26" class="mr-7"></v-img>
                    <v-icon v-else size="26">{{ route.icon }}</v-icon>
                  </div>
                </template>
              </v-list-item>
            </template>
          </v-hover>
        </div>
        <div v-if="auth.user.level >= 2 && supervisor_routes.length">
          <v-divider></v-divider>
          <v-list-subheader>Supervisão</v-list-subheader>
          <v-hover v-for="(route, index) in supervisor_routes" :key="index">
            <template v-slot:default="{ isHovering, props }">
              <v-list-item v-bind="props" :to="route.to" :title="route.title" :value="route.value"
                :style="dark_theme ? '' : 'color: rgb(0,0,0)'">
                <template v-slot:prepend>
                  <div :style="isHovering ? 'transform: translateX(5px)' : ''" style="transition: 0.2s">
                    <v-img v-if="route.img" :src="'media/icons/' + route.img" width="26" class="mr-7"></v-img>
                    <v-icon v-else size="26">{{ route.icon }}</v-icon>
                  </div>
                </template>
              </v-list-item>
            </template>
          </v-hover>
        </div>
      </v-list>
      <template v-slot:append>
        <v-divider></v-divider>
        <v-list nav class="flex-grow-1">
          <v-list-item to="/about" prepend-icon="mdi-information" title="Sobre" value="sobre"></v-list-item>
          <v-list-item align="center">
            <v-hover>
              <template v-slot:default="{ isHovering, props }">
                <v-btn width="220" v-bind="props" color="indigo" :variant="isHovering ? 'outlined' : 'elevated'"
                  prepend-icon="mdi-logout" @click="logout()">Sair</v-btn>
              </template>
            </v-hover>
          </v-list-item>
        </v-list>
      </template>
    </v-navigation-drawer>
    <v-layout v-if="$route.name != 'login' && auth.is_auth && auth.hasData()">
      <v-main v-if="$route.name == 'plantings'" :class="dark_theme ? 'background-dark' : 'background-light'">                  
        <div style="padding-right: 100px; padding-left: 100px; padding-bottom: 100px; padding-top: 100px;" :style="smAndDown ? 'padding-right: 2px; padding-left: 2px;' : ''">
          <router-view />
        </div>
      </v-main>
      <v-main v-else :class="dark_theme ? 'background-dark' : 'background-light'">
        <v-container class="main-container">
          <div class="page-title-section">
            <span class="page-title">
              <v-row>
                <v-col cols="4" class="pl-0">
                  <v-img v-if="$route.meta.img" :src="'media/icons/' + $route.meta.img" width="32"></v-img>
                  <v-icon v-else size="28px" :class="dark_theme ? '' : 'dark-gray'">{{ $route.meta.icon }}</v-icon>
                </v-col>
                <v-col cols="8" class="pl-0">
                  <span :class="dark_theme ? 'text-shadow-black-2-2' : 'dark-gray'">{{ $route.meta.view }}</span>
                </v-col>
              </v-row>
            </span>
            <v-breadcrumbs :items="breadcrumbs_items" class="breadcrumbs-container"
              :class="dark_theme ? 'white' : 'black'">
              <template v-slot:prepend>
                <v-icon size="small" icon="mdi-home-analytics"></v-icon>
              </template>
            </v-breadcrumbs>
          </div>
          <v-divider class="pb-7" :class="dark_theme ? 'white' : 'black'"></v-divider>
          <router-view />
        </v-container>
      </v-main>
    </v-layout>
    <v-main v-else class="login-background">
      <v-container>
        <router-view />
      </v-container>
    </v-main>
    <DialogEditProfile
      @close="profile_dialog = false; profile_form = reactive({ id: auth.user.id, name: auth.user.full_name, cpf: auth.user.cpf, email: auth.user.email, password: '', password_confirmation: '' })"
      :model="profile_dialog" :data="profile_form" :change_password_card="change_password_card_prop" color="orange" />
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="snackbar.timeout"
      :location="snackbar.location" :variant="snackbar.variant" :timer="snackbar.timer"
      :timer-color="snackbar.timerColor" :prepend-icon="snackbar.prependIcon">      
      {{ snackbar.text }}
      <template #actions>
        <v-btn @click="snackbar.close()" size="small" icon="mdi-close"></v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
// Imports
import { RouterView, RouterLink, useRoute } from 'vue-router'
import api from '@/plugins/axios.js';
import { useAuthStore } from '@/stores/auth.js'
import { useSnackbarStore } from '@/stores/snackbar'
import { ref, reactive, watch, computed, onMounted } from 'vue'
import DialogEditProfile from '@/components/dialogs/DialogEditProfile.vue'
import { useTheme, useDisplay } from 'vuetify'

// Variables
const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const dark_theme_model = ref(dark_theme.value)
const update_user_theme = ref(false)
const loading_theme = ref(false)
const route = useRoute()
const path = computed(() => route.meta.view)
const auth = useAuthStore()
const snackbar = useSnackbarStore()
const drawer = ref(false)
const profile_dialog = ref(false)
let profile_form = ''
const appbar_density = ref('55')
const basic_routes = reactive([
  { to: '/', title: 'Dashboard', value: '/', img: 'dashboard.png' },
  { to: '/plantings', title: 'Plantios', value: 'plantings', icon: 'mdi-sprout' }
])
const adm_routes = reactive([
  { to: '/users', title: 'Usuários', value: 'users', img: 'users.png' }
])
const supervisor_routes = reactive([

])
const breadcrumbs_items = reactive([])
const password_expire_time = reactive([])
const change_password_card_prop = ref(true)

// Computeds
const auth_computed = computed(() => auth?.user?.configs)

// Watchers
watch(auth_computed, (v) => {
  if (v == undefined || !v.hasOwnProperty('theme')) return
  use_theme.change(v.theme == 0 ? 'customLight' : 'customDark')
  dark_theme_model.value = v.theme != 0
  checkPasswordResetExpire()
})

watch(dark_theme_model, (v) => {
  if (!update_user_theme.value) return
  if (!auth.user.id) return
  update_user_theme.value = false
  loading_theme.value = true
  use_theme.change(v ? 'customLight' : 'customDark')
  api.put('edit_user/' + auth.user.id, { configs: { theme: (v ? 1 : 0) } }).then((response) => {
    auth.setUser(response.data)
    loading_theme.value = false
  }).catch((error) => {
    console.log(error)
    snackbar.open({ preset: 'error' })
    loading_theme.value = false
  })
})

watch(path, () => {
  //appbar_density.value != '55' && path.value != 'Login' ? setTimeout(() => { appbar_density.value = '55' }, 2000) : null
  Object.assign(breadcrumbs_items, [
    {
      title: 'Dashboard',
      disabled: false,
      to: '/'
    },
    {
      title: path,
      disabled: true,
      to: ''
    }
  ])
  if (path.value == 'Dashboard') {
    breadcrumbs_items.pop()
    breadcrumbs_items[0].disabled = true
  }
})

// Mounted
onMounted(() => {
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      drawer.value = !drawer.value
    }
  })
  const main_container = document.querySelector('.main-container')
  if (main_container) {
    window.addEventListener('load', () => main_container.style.minWidth = `${window.innerWidth - 192}px`)
    window.addEventListener('resize', () => main_container.style.minWidth = `${window.innerWidth - 192}px`)
  }
})

// Created
setTheme()
checkPasswordResetExpire()

// Methods
function checkPasswordResetExpire(attempt = 1) {
  if (auth_computed.value == undefined || !auth_computed.value.hasOwnProperty('theme')) return
  api.get('get_user_password_expire', { params: { user_id: auth.user.id } }).then((response) => {
    Object.assign(password_expire_time, response.data)
    if (password_expire_time?.expired && password_expire_time.expired) {
      auth.clear()
    }
  }).catch((error) => {
    console.log(error)
    if (error.response?.status != 404 && attempt <= 5) {
      setTimeout(() => checkPasswordResetExpire(attempt + 1), 1000)
    }
  })
}

function openDialogEditProfile(change_password_card = false) {
  change_password_card_prop.value = change_password_card
  profile_form = auth.user ? reactive({ id: auth.user.id, name: auth.user.full_name, cpf: auth.user.cpf, email: auth.user.email, password: '', password_confirmation: '' }) : ''
  profile_dialog.value = true
}

function logout() {
  auth.clear()
}

function setTheme() {
  if (auth_computed.value == undefined || !auth_computed.value.hasOwnProperty('theme')) return
  dark_theme_model.value = auth_computed.value.theme != 0
  use_theme.change(auth_computed.value.theme == 0 ? 'customLight' : 'customDark')
}

</script>
<style>
.custom-switch-light .v-switch__thumb {
  width: 40px !important;
  height: 40px !important;
  font-size: 25px !important;
  background-color: rgba(255, 255, 255, 0) !important;
  box-shadow: none !important;
}

.custom-switch-dark .v-switch__thumb {
  width: 40px !important;
  height: 40px !important;
  font-size: 25px !important;
  background-color: rgba(255, 255, 255, 0) !important;
  box-shadow: none !important;
}

.custom-switch-light .v-switch__track {
  opacity: 1 !important;
  background-color: rgb(253, 157, 67) !important;
}

.custom-switch-dark .v-switch__track {
  opacity: 1 !important;
  background-color: rgba(47, 113, 255, 0.548) !important;
}

.custom-switch-light i {
  opacity: 1 !important;
  color: rgb(229, 255, 0) !important;
}

.custom-switch-dark i {
  opacity: 1 !important;
  color: rgb(212, 179, 254) !important;
}
</style>
<style scoped>
.main-container {
  padding: 0px;
  padding-bottom: 100px;
}

.background-dark {
  background: linear-gradient(45deg, rgb(35, 35, 35) 0%, rgb(22, 29, 32) 100%);
}

.background-light {
  background: linear-gradient(280deg, rgb(238, 253, 225) 0%, rgb(206, 225, 236) 100%);
}

.page-title-section {
  padding: 25px;
  margin: 5px;
  margin-top: 80px;
  position: relative;
}

.page-title {
  font-size: 24px;
  font-weight: 900;
  top: 50%;
  transform: translateY(-50%);
  position: absolute;
}

.page-icon {
  height: 5px;
}

.breadcrumbs-container {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  right: 0;
}

.login-background {
  background: linear-gradient(to right, #171922, #353535);
  /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
}

.list-container {
  cursor: pointer;
  position: relative;
}

.background-image {
  transition: 0.3s;
  position: absolute;
  object-fit: cover;
  width: 100%;
  height: 100%;
  opacity: 0.9;
}

.background-image-dark {
  transition: 0.3s;
  position: absolute;
  object-fit: cover;
  width: 100%;
  height: 100%;
  opacity: 0.5;
}

@keyframes pulseBackground {
  0% {
    background-position: left center;
  }

  50% {
    background-position: right center;
  }

  100% {
    background-position: left center;
  }
}

.password-expire-button {
  transition: 0.3s;
  background: linear-gradient(to right, rgba(255, 73, 1, 0.822), rgba(255, 217, 0, 0.842), rgba(255, 73, 1, 0.822));
  background-size: 200% auto;
  color: black !important;
  text-shadow: 1px 1px rgba(255, 255, 255, 0.411);
  animation: pulseBackground 3s infinite linear;
  border: rgba(0, 0, 0, 0.774) solid 1px;
}
</style>