<template>
  <div>
    <v-dialog v-model="props.model" persistent width="800px">
      <v-card :loading="loading" min-height="">
        <template v-slot:loader="{ isActive }">
          <v-progress-linear :active="isActive" :color="color" height="7" indeterminate></v-progress-linear>
        </template>
        <v-card-title style="position: relative" v-if="!loading">
          <v-row>
            <v-col cols="11" class="mt-1">
              <v-row>
                <v-col cols="1" style="margin-right: -5px">
                  <v-img v-if="$route.meta.img" width="32" :src="'media/icons/' + $route.meta.img"></v-img>
                  <v-icon v-else class="mr-0" :color="color">{{ $route.meta.icon }}</v-icon>
                </v-col>
                <v-col cols="11" class="pl-0">
                  <span :style="'color:' + color">Adicionar Usuário</span>
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
          <span v-if="loading">Inserindo usuário</span>
          <span v-else>Defina o novo usuário</span>
        </v-card-subtitle>
        <v-divider class="mt-2"></v-divider>
        <v-scroll-x-transition :duration="{ enter: 300, leave: 0 }">
          <v-card-text v-if="loading">
            <v-row align="center" justify="center" class="ma-1 mt-0">
              <v-col cols="12">
                <v-alert color="success" border="start" elevation="3" dark icon="mdi-content-save">
                  <span>Espera só um pouquinho, carregando...</span>
                </v-alert>
              </v-col>
            </v-row>
          </v-card-text>
        </v-scroll-x-transition>
        <v-card-text class="mb-5" v-if="!loading">
          <v-form ref="form">
            <v-row>
              <v-col cols="12">
                <v-text-field color="teal" v-model="item.name" :readonly="loading || alert"
                  :rules="getRules({ required: true, maxlen: { val: 100 }, name: true })" @keyup="nameMask(item)"
                  clearable label="Nome Completo" placeholder="Nome Sobrenome"
                  @change="setEmail(item.name)"></v-text-field>
              </v-col>
              <v-col cols="8">
                <v-text-field color="teal" v-model="item.email" :readonly="loading || alert"
                  :rules="getRules({ required: true, email: true, maxlen: { val: 60 } })" clearable
                  label="E-mail"></v-text-field>
              </v-col>
              <v-col cols="4">
                <v-text-field color="teal" v-model="item.cpf" v-mask="'###.###.###-##'" :readonly="loading || alert"
                  :rules="getRules({ required: true, cpf: true })" clearable label="CPF"
                  placeholder="Informe seu CPF"></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field color="teal" v-model="item.password" :readonly="loading || alert"
                  :rules="getRules({ required: true, minlen: { val: 5 }, maxlen: { val: 30 }, hasLowercase: true, hasUppercase: true, hasNumber: true, hasSpecialchar: true })"
                  clearable label="Senha" placeholder="Digite sua senha" counter
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'" :type="visible ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock-outline" @click:append-inner="visible = !visible"
                  @keyup.enter="(!loading && !alert) && addUser()"></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field color="teal" v-model="item.password_confirmation" :readonly="loading || alert"
                  :rules="getRules({ required: true, equals: { val: item.password, message: 'As senhas não se correspondem' } })"
                  clearable label="Confirme sua senha" placeholder="Repita sua senha" counter
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'" :type="visible ? 'text' : 'password'"
                  prepend-inner-icon="mdi-lock-outline" @click:append-inner="visible = !visible"
                  @keyup.enter="(!loading && !alert) && addUser()"></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-divider v-if="!loading"></v-divider>
        <v-card-actions class="pa-3" v-if="!loading">
          <v-slide-x-reverse-transition>
            <v-alert height="35" v-show="alert" :color="alert_fail ? 'red' : 'green'" border="start" elevation="3" dark
              class="mx-2"><v-icon size="small" class="mr-2 ml-0 pl-0">{{ alert_fail ? 'mdi-alert-circle' :
                'mdi-emoticon-wink' }}</v-icon>{{ alert_message
                }}</v-alert>
          </v-slide-x-reverse-transition>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" color="red" :variant="isHovering ? 'tonal' : 'outlined'" @click="closeDialog()">
                Cancelar
              </v-btn>
            </template>
          </v-hover>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" :color="color" :variant="isHovering ? 'tonal' : 'elevated'" @click="addUser()">
                Confirmar
              </v-btn>
            </template>
          </v-hover>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
//Imports
import { ref, watch, reactive, computed } from 'vue'
import api from '@/plugins/axios.js'
import CryptoJS from 'crypto-js'

// Variables
const emit = defineEmits(['close', 'new_register'])
const loading = ref(false)
const props = defineProps({
  color: { type: String, default: 'green' },
  model: { type: Boolean, required: true }
})
let item = reactive({ name: '', cpf: '', email: '', password: '', password_confirmation: '' })
const form = ref(null)
const visible = ref(false)
const alert_message = ref('')
const alert = ref(false)
const alert_fail = ref(true)

//Computeds
const model_computed = computed(() => props.model)

//Watchers
watch(model_computed, (v) => {
  if (v) {
    item = reactive({ name: '', cpf: '', email: '', password: '', password_confirmation: '' })
  }
})

//Methods
function setEmail(full_name) {
  if (!/^[A-Z][\p{L}\s´~]* [A-Z][\p{L}\s´~]*$/u.test(full_name)) return
  let full_name_array = full_name.trim().toLowerCase().split(' ')
  item.email = full_name_array[0] + '.' + full_name_array[full_name_array.length - 1]
}

function encryptPassword(password) {
  const key = CryptoJS.enc.Utf8.parse('My$uperSecretKey3434567890AyCjEF')
  const iv = CryptoJS.enc.Utf8.parse('MyInitVector3434')
  const encrypted = CryptoJS.AES.encrypt(password, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 })
  return encrypted.toString()
}

async function addUser() {
  const { valid } = await form.value.validate()
  if (!valid) return false
  loading.value = true
  item.cpf = item.cpf.replace(/\D/g, '')
  let item_copy = { ...item }
  item_copy.password = encryptPassword(item_copy.password)
  delete item_copy['password_confirmation']
  api.post('add_user', item_copy)
    .then(response => {
      emit('new_register', response.data)
      closeDialog()
      setTimeout(() => { loading.value = false }, 300)
    })
    .catch(err => {
      console.log(err)
      loading.value = false
      alert_message.value = err?.response?.data?.message || 'Ocorreu um erro, tente novamente'
      alert.value = true
      setTimeout(() => { alert.value = false }, 4000)
    })
}

function closeDialog() {
  emit('close')
  setTimeout(() => { loading.value = false }, 300)
}

</script>
