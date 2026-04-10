<template>
  <div>
    <v-dialog v-model="model_computed" width="700px" persistent>
      <v-card :loading="loading_profile">
        <template v-slot:loader="{ isActive }">
          <v-progress-linear :active="isActive" color="orange-lighten-1" height="7" indeterminate></v-progress-linear>
        </template>
        <v-card-title style="position: relative" v-if="!loading_profile">
          <v-row>
            <v-col cols="11" class="mt-1">
              <v-row>
                <v-col cols="1" style="margin-right: -5px">
                  <v-img v-if="$route.meta.img" width="32" :src="'media/icons/profile.png'"></v-img>
                  <v-icon v-else class="mr-0" :color="color">{{ $route.meta.icon }}</v-icon>
                </v-col>
                <v-col cols="11" class="pl-0">
                  <span :style="'color:' + color">Editar Perfil</span>
                </v-col>
              </v-row>
            </v-col>
            <v-col cols="1">
              <v-img style="width: 32px; position:absolute; top: 20px" src="media/icons/logo.png" />
            </v-col>
          </v-row>
        </v-card-title>
        <v-card-title class="pa-3" v-else>
          <strong :style="'color:' + color">Salvando</strong>
          <v-img max-width="50" class="loading-gif" src="media/images/loading.gif" />
        </v-card-title>
        <v-card-subtitle>
          <span v-if="loading_profile">Editando perfil...</span>
          <span v-else>Alterar meus dados pessoais</span>
        </v-card-subtitle>
        <v-divider class="mt-2"></v-divider>
        <v-scroll-x-transition>
          <v-card-text v-if="loading_profile">
            <v-row align="center" justify="center" class="ma-1 mt-0">
              <v-col cols="12">
                <v-alert :color="color" border="start" elevation="3" dark icon="mdi-content-save">
                  <span>Espera só um pouquinho, carregando...</span>
                </v-alert>
              </v-col>
            </v-row>
          </v-card-text>
        </v-scroll-x-transition>
        <v-card-text v-if="!loading_profile">
          <v-form ref="form">
            <v-row>
              <v-col cols="12">
                <v-text-field :color="color" v-model="profile_form.name" :readonly="loading_profile" density="compact"
                  @keyup="nameMask(profile_form)" :rules="getRules({ required: true, maxlen: { val: 100 }, name: true })"
                  class="" clearable label="Nome Completo" @keyup.enter="!loading_profile && editUser()"
                  placeholder="Nome Sobrenome"></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" lg="4">
                <v-text-field :color="color" v-model="profile_form.cpf" v-mask="'###.###.###-##'"
                  :readonly="loading_profile" density="compact" :rules="getRules({ required: true, cpf: true })" class=""
                  clearable label="CPF" placeholder="Informe seu CPF" @keyup.enter="!loading_profile && editUser()"></v-text-field>
              </v-col>
              <v-col cols="12" lg="8">
                <v-text-field :color="color" v-model="profile_form.email" :readonly="loading_profile" density="compact"
                  :rules="getRules({ required: true, email: true, maxlen: { val: 60 } })" clearable label="E-mail"
                  @keyup.enter="!loading_profile && editUser()"></v-text-field>
              </v-col>
              <v-col cols="12" lg="6">
                <v-select :color="color" v-model="profile_form.group_id" label="Grupo"
                  :rules="getRules({ required: true })" :disabled="loading_groups" :loading="loading_groups"
                  :items="groups" item-title="name" item-value="id">
                  <template v-slot:item="{ props, item }">
                    <v-list-item color="orange" v-bind="props" :title="item.raw.name"></v-list-item>
                  </template>
                </v-select>
              </v-col>
              <v-col cols="12" lg="6">
                <v-select :color="color" v-model="profile_form.sp" label="PA" :rules="getRules({ required: true })"
                  :items="[{ title: 'Matriz', value: 0, icon: 'home-city' }, { title: 'PA-01', value: 1, icon: 'home-floor-1' }, { title: 'PA-02', value: 2, icon: 'home-floor-2' }, { title: 'UAD', value: 88, icon: 'account-tie' }, { title: 'UPS', value: 89, icon: 'human-male-board-poll' }, { title: 'PA-97', value: 97, icon: 'laptop-account' }]">
                  <template v-slot:item="{ props, item }">
                    <v-list-item color="orange" v-bind="props" :title="item.raw.title"
                      :prepend-icon="'mdi-' + item.raw.icon"></v-list-item>
                  </template>
                  <template v-slot:selection="{ props, item }">
                    <v-icon class="ml-1 mr-3 pb-1" v-bind="props" color="orange">{{ 'mdi-' + item.raw.icon }}</v-icon> {{ item.raw.title }}
                  </template>
                </v-select>
              </v-col>
              <v-col cols="12">
                <v-file-input :color="color" accept="image/png, image/jpeg, image/jpg, image/bmp"
                  prepend-icon="mdi-camera" density="compact" label="Atualizar foto de perfil" show-size
                  variant="solo-filled" v-model="profile_form.photo"
                  :rules="getRules({ file_format: { val: 'image', message: 'Apenas imagens, porfavor...' } })">
                </v-file-input>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-btn variant="tonal" :prepend-icon="password_card ? 'mdi-lock-open-remove' : 'mdi-lock-open'"
                  color="orange-darken-1"
                  @click="password_card = !password_card; profile_form.password = profile_form.password_confirmation = ''">{{
                    password_card
                    ? 'Cancelar' : 'Minha senha' }}</v-btn>
              </v-col>
            </v-row>
            <v-slide-y-transition>
              <v-row v-if="password_card">
                <v-col cols="12">
                  <v-card :color="dark_theme ? 'orange-lighten-1' : 'orange-lighten-2'" elevation="10"
                    class="mx-auto pa-1" :variant="dark_theme ? 'outlined' : 'elevated'" :disabled="loading_profile">
                    <v-card-title :color="color" style="font-weight: bold">
                      Alterar Senha
                      <v-tooltip :content-class="dark_theme ? 'tooltip-default-dark' : 'tooltip-default-light'"
                        id="tooltip" right color="grey darken-3">
                        <template v-slot:activator="{ props }">
                          <v-icon class="mx-2" v-bind="props">mdi-alert-octagon-outline</v-icon>
                        </template>
                        <span>Essa ação é irreversível</span>
                      </v-tooltip>
                    </v-card-title>
                    <v-card-subtitle>
                      A nova senha entrará em vigor no seu próximo Login. <strong>(Deve ser diferente das 5 últimas)</strong>
                    </v-card-subtitle>
                    <v-card-text>
                      <v-row>
                        <v-col cols="12">
                          <v-text-field color="orange-darken-4" v-model="profile_form.password" density="compact"
                            :readonly="loading_profile"
                            :rules="getRules({ required: true, minlen: { val: 5 }, maxlen: { val: 30 }, hasLowercase: true, hasUppercase: true, hasNumber: true, hasSpecialchar: true })"
                            clearable label="Senha" placeholder="Digite sua senha"
                            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'" :type="visible ? 'text' : 'password'"
                            prepend-inner-icon="mdi-lock-outline" @click:append-inner="visible = !visible"
                            @keyup.enter="!loading_profile && editUser()"></v-text-field>
                        </v-col>
                        <v-col cols="12">
                          <v-text-field color="orange-darken-4" v-model="profile_form.password_confirmation"
                            density="compact" :readonly="loading_profile"
                            :rules="getRules({ required: true, equals: { val: profile_form.password, message: 'As senhas não se correspondem' } })"
                            clearable label="Confirme sua senha" placeholder="Repita sua senha"
                            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'" :type="visible ? 'text' : 'password'"
                            prepend-inner-icon="mdi-lock-outline" @click:append-inner="visible = !visible"
                            @keyup.enter="!loading_profile && editUser()"></v-text-field>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-slide-y-transition>
          </v-form>
        </v-card-text>
        <v-divider v-if="!loading_profile"></v-divider>
        <v-card-actions v-if="!loading_profile">
          <v-spacer></v-spacer>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" color="red" :variant="isHovering ? 'tonal' : 'outlined'" @click="closeProfile()">
                Cancelar
              </v-btn>
            </template>
          </v-hover>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" :color="color" :variant="isHovering ? 'tonal' : 'elevated'" @click="editUser()">
                <span>Confirmar</span>
              </v-btn>
            </template>
          </v-hover>
        </v-card-actions>
      </v-card>
      <v-snackbar v-model="snackbar" color="orange-darken-3">
        <v-icon color="pink" class="mr-2">mdi-alert</v-icon>
        <span class="text-shadow-black-1">{{ snackbar_text }}</span>
        <template v-slot:actions>
            <v-btn color="pink" variant="text" icon="mdi-close" @click="snackbar = false"></v-btn>
        </template>
      </v-snackbar>
    </v-dialog>
  </div>
</template>

<script setup>
//Imports
import { ref, watch, reactive, computed } from 'vue'
import api from '@/plugins/axios.js'
import { useAuthStore } from '@/stores/auth.js'
import { useTheme } from 'vuetify'

// Variables
const emit = defineEmits(['close'])
const props = defineProps({
  color: { type: String, default: 'green' },
  model: { type: Boolean, required: true },
  data: { required: true },
  change_password_card: { type: Boolean, required: false, default: false }
})
const use_theme = useTheme()
let profile_form = reactive({})
const form = ref(null)
const auth = useAuthStore()
const visible = ref(false)
const groups = reactive([])
const password_card = ref(props.change_password_card)
const loading_profile = ref(false)
const loading_groups = ref(false)
const snackbar_text = ref('')
const snackbar = ref(false)

//Computeds
const model_computed = computed(() => props.model)
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')

//Created
getGroups()

//Watchers
watch(model_computed, (v) => {
  if (v) {
    getGroups()
    profile_form = reactive({})
    password_card.value = props.change_password_card
    Object.assign(profile_form, props.data)
  }
})

//Methods
function getGroups(attempt = 1) {
  loading_groups.value = true
  api.get('get_groups', { params: { manage: true } }).then((response) => {
    Object.assign(groups, response.data)
    loading_groups.value = false
  }).catch((error) => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getGroups(attempt + 1), 1000)
    } else {
      loading_groups.value = false
    }        
  })
}

async function editUser() {
  const { valid } = await form.value.validate()
  if (!valid) return false
  loading_profile.value = true
  profile_form.cpf = profile_form.cpf.replace(/\D/g, '')
  const form_data = new FormData()
  form_data.append('id', profile_form.id)
  form_data.append('cpf', profile_form.cpf)
  form_data.append('name', profile_form.name)
  form_data.append('email', profile_form.email)
  form_data.append('group_id', profile_form.group_id)
  form_data.append('sp', profile_form.sp)
  form_data.append('attachment', profile_form.photo ? profile_form.photo : null)
  form_data.append('password', profile_form.password)
  form_data.append('password_confirmation', profile_form.password_confirmation)
  api.post('edit_profile', form_data, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }).then(response => {
    if (response.data) {
      auth.setUser(response.data)
    }   
    closeProfile()
  }).catch(error => {
    if (error.response?.status === 400 && error.response?.data?.message) { 
      loading_profile.value = false 
      snackbar_text.value = error.response.data.message                 
      snackbar.value = true
    } else {
      console.log(error)
      closeProfile()
    }
  })
}

function closeProfile() {
  emit('close')
  setTimeout(() => { loading_profile.value = false }, 300)
}

</script>
