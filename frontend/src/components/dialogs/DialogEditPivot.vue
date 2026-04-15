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
              <v-col cols="11">
                <v-row>
                  <v-col :cols="smAndDown ? 2 : 1">
                    <v-img v-if="img" width="32" :src="'media/icons/' + img" />
                    <v-icon v-else :color="color">{{ icon }}</v-icon>
                  </v-col>
                  <v-col :cols="smAndDown ? 10 : 11">
                    <span :style="{ color }">{{ loading ? 'Editando Pivô' : 'Editar Pivô' }}</span>
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="1">
                <v-img width="32" src="media/icons/logo.png" />
              </v-col>
            </v-row>
          </v-card-title>
          <v-card-subtitle class="mb-3">
            <span v-if="loading">Aguarde...</span>
            <span v-else>Edição de pivô</span>
          </v-card-subtitle>
          <v-divider />
        </div>
        <v-card-text>
          <v-form ref="form" @submit.prevent="editPivot">
            <v-row>
              <v-col cols="12">
                <v-text-field @keyup.enter="editPivot()" :disabled="loading" v-model="item.name" label="Nome" clearable
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
            <v-btn :loading="loading" :disabled="loading" :color="color" @click="editPivot">
              Confirmar
            </v-btn>
          </v-card-actions>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
// Imports
import { ref, reactive, watch, computed } from 'vue'
import api from '@/plugins/axios.js'
import { useTheme, useDisplay } from 'vuetify'
import { useSnackbarStore } from '@/stores/snackbar'

// Variables
const snackbar = useSnackbarStore()
const emit = defineEmits(['close', 'edited_register'])
const loading = ref(false)
const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const props = defineProps({
  color: { type: String },
  model: { type: Boolean, required: true },
  icon: { type: String, required: true },
  img: { type: String },
  data: { type: Object, required: true },
})
const item = reactive({
  id: '',
  name: ''
})
const form = ref(null)

// Created


// Computeds
const model_computed = computed(() => props.model)

// Watchers
watch(model_computed, (v) => {
  if (v) {
    resetForm()
    for (const key in item) {
      if (props.data.hasOwnProperty(key)) {
        item[key] = props.data[key]
      }
    }
  }
})

// Methods
async function editPivot() {
  const { valid } = await form.value.validate()
  if (!valid) return
  loading.value = true
  api.put('edit_pivot/' + item.id, item).then((response) => {
    emit('edited_register', response.data)
    closeDialog()
  }).catch((error) => {
    console.log(error)
    snackbar.open({ preset: 'error' })
    closeDialog()
  })
}

function resetForm() {
  item.id = ''
  item.name = ''
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm, 300)
}

</script>
