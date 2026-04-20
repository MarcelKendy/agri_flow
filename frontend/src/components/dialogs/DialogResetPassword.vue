<template>
  <div>
    <v-dialog v-model="props.model" persistent width="480px" @keydown.enter="addData()">
      <v-card :loading="loading" min-height="">
        <template v-slot:loader="{ isActive }">
          <v-progress-linear :active="isActive" :color="color" height="7" indeterminate></v-progress-linear>
        </template>
        <v-card-title style="position: relative" v-if="!loading">
          <v-row>
            <v-col cols="10" class="mt-1">
              <v-row>
                <v-col cols="2">
                  <v-icon :color="color">mdi-lock-reset</v-icon>
                </v-col>
                <v-col cols="8" class="pl-0">
                  <span :style="'color: ' + color">Redefinir Senha</span>
                </v-col>
              </v-row>
            </v-col>
            <v-col cols="2">
              <v-img style="width: 32px; position:absolute; top: 20px" src="media/icons/logo.png" />
            </v-col>
          </v-row>
        </v-card-title>
        <v-card-title class="pa-3" v-else>
          <v-row>
            <v-col cols="1">
              <v-icon :color="color">mdi-lock-reset</v-icon>
            </v-col>
            <v-col cols="11">
              <span :style="'color:' + color">Enviando</span>
            </v-col>
          </v-row>
          <v-img width="50" class="loading-gif" src="media/images/loading.gif" />
        </v-card-title>
        <v-card-subtitle>
          <span v-if="loading">Enviando e-mail</span>
          <span v-else>Um link será enviado para o seu e-mail</span>
        </v-card-subtitle>
        <v-divider class="mt-2"></v-divider>
        <v-card-text>
          <v-form ref="form">
            <v-row>
              <v-col cols="12">
                <v-text-field :color="color" v-model="reset_password.email" :disabled="loading || sending_error"
                  :rules="getRules({ required: true, email: true, maxlen: { val: 60 } })" clearable label="E-mail"
                  @keyup.enter="sendPasswordResetMail()"></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-3">
          <v-slide-x-reverse-transition>
            <v-alert height="35" v-show="sending_error" style="position: absolute;" color="red" border="start"
              elevation="3" dark class="mx-2">
              <v-icon size="small" class="mr-2 ml-0 pl-0">mdi-alert-circle</v-icon>
              {{ error_text }}
            </v-alert>
          </v-slide-x-reverse-transition>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-if="!sending_error" :disabled="loading" v-bind="props" color="red"
                :variant="isHovering ? 'tonal' : 'outlined'" @click="closeDialog()">
                Cancelar
              </v-btn>
            </template>
          </v-hover>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-if="!sending_error" :disabled="loading" :loading="loading" v-bind="props"
                append-icon="mdi-email-arrow-right" :color="color" :variant="isHovering ? 'tonal' : 'elevated'"
                @click="sendPasswordResetMail()">
                Enviar
              </v-btn>
            </template>
          </v-hover>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
// Imports
import { ref, watch, reactive, computed } from 'vue'
import api from '@/plugins/axios.js'

// Variables
const emit = defineEmits(['close', 'done'])
const loading = ref(false)
const props = defineProps({
  color: { type: String, default: '#2196F3' },
  model: { type: Boolean, required: true },
})
let reset_password = reactive({ email: '' })
const form = ref(null)
const sending_error = ref(false)
const error_text = ref('')

// Computeds
const model_computed = computed(() => props.model)

// Watchers
watch(model_computed, (v) => {
  if (v) reset_password = reactive({ email: '' })
})

// Methods
async function sendPasswordResetMail(attempts = 1) {
  if (attempts == 1) {
    const { valid } = await form.value.validate()
    if (!valid) return false
  }
  loading.value = true
  api.post('send_password_reset_mail', reset_password).then(() => {
    emit('done')
    loading.value = false
  }).catch((error) => {
    console.log(error)
    if (attempts < 5 && error.response?.status != 400) {
      setTimeout(() => sendPasswordResetMail(attempts + 1), 1000)
    } else {
      error_text.value = error?.response?.data?.message || 'Ocorreu um erro, tente novamente'
      loading.value = false
      sending_error.value = true
      setTimeout(() => {
        sending_error.value = false
      }, 3000)
    }
  })
}

function closeDialog() {
  sending_error.value = false
  emit('close')
  setTimeout(() => { loading.value = false }, 300)
}

</script>
