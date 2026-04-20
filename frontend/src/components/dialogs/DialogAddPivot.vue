<template>
  <div>
    <v-dialog v-model="props.model" persistent width="800px" min-height="100px">
      <v-card :loading="loading">
        <template v-slot:loader="{ isActive }">
          <v-progress-linear :active="isActive" :color="color" height="7" indeterminate />
        </template>
        <div class="card-header-sticky">
          <v-card-title class="mt-1">
            <v-row>
              <v-col :cols="smAndDown ? 12 : 10">
                <v-row>
                  <v-col v-if="!smAndDown" cols="1">
                    <v-img v-if="img" width="32" :src="'media/icons/' + img" />
                    <v-icon v-else :color="color">{{ icon }}</v-icon>
                  </v-col>
                  <v-col :cols="smAndDown ? 12 : 11">
                    <span :style="{ color }">{{ (loading ? 'Adicionando ' : 'Adicionar ') + translation.pt_upper }}</span>
                  </v-col>
                </v-row>
              </v-col>
              <v-col v-if="!smAndDown" cols="2" class="align-center">
                <v-img height="48" src="media/icons/logo.png" />
              </v-col>
            </v-row>
          </v-card-title>
          <v-card-subtitle class="mb-3">
            <span v-if="loading">Aguarde...</span>
            <span v-else>Preencha os campos atentamente</span>
          </v-card-subtitle>
          <v-divider />
        </div>
        <v-card-text>
          <v-form ref="form" @submit.prevent="addItem">
            <v-row>
              <v-col cols="12">
                <v-text-field @keyup.enter="addItem()" :disabled="loading" v-model="item.name" label="Nome" clearable
                  :color="color" :rules="getRules({ required: true, minlen: { val: 3 } })" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <div class="card-actions-sticky">
          <v-divider />
          <v-card-actions>
            <v-spacer />
            <v-btn :disabled="loading" color="red" variant="outlined" @click="closeDialog">
              Cancelar
            </v-btn>
            <v-btn :loading="loading" :disabled="loading" :color="color" @click="addItem">
              Confirmar
            </v-btn>
          </v-card-actions>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue'
import api from '@/plugins/axios.js'
import { useTheme, useDisplay } from 'vuetify'
import { useSnackbarStore } from '@/stores/snackbar'

// Variables
const translation = { pt_upper: 'Pivô', pt_lower: 'pivô', table: 'pivots', model: 'Pivot', api: 'pivot' }
const snackbar = useSnackbarStore()
const emit = defineEmits(['close', 'new_register'])
const props = defineProps({
  color: { type: String, default: 'green' },
  icon: { type: String, required: true },
  img: { type: String },
  model: { type: Boolean, required: true },
})
const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const loading = ref(false)
const form = ref(null)
const item = reactive({
  name: ''
})

// Watch dialog open
watch(() => props.model, (open) => {
  if (open) resetForm()
})

// Methods
async function addItem() {
  const { valid } = await form.value.validate()
  if (!valid) return
  loading.value = true
  api.post('add_' + translation.api, item).then(res => {
    emit('new_register', res.data)
    closeDialog()
  }).catch((error) => {
    console.log(error)
    snackbar.open({ preset: 'error' })
    closeDialog()
  })
}

function resetForm() {
  item.name = ''
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm, 300)
}
</script>
