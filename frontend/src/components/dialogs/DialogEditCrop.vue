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
              <v-col cols="10">
                <v-row>
                  <v-col :cols="smAndDown ? 2 : 1">
                    <v-img v-if="img" width="32" :src="'media/icons/' + img" />
                    <v-icon v-else :color="color">{{ icon }}</v-icon>
                  </v-col>
                  <v-col :cols="smAndDown ? 10 : 11">
                    <span :style="{ color }">{{ (loading ? 'Editando ' : 'Editar ') + translation.pt_upper }}</span>
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="2" class="align-center">
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
          <v-form ref="form" @submit.prevent="editItem">
            <v-row>
              <v-col cols="12">
                <v-text-field @keyup.enter="editItem()" :disabled="loading" v-model="item.name" label="Nome" clearable
                  :color="color" :rules="getRules({ required: true, minlen: { val: 3 } })" />
              </v-col>
              <v-col cols="12">
                <v-text-field :disabled="loading" v-model="variety_input" label="Variedades" clearable :color="color"
                  hint="Digite uma variedade e pressione Enter" persistent-hint @keydown.enter.prevent="addVariety" />
              </v-col>
              <v-col cols="12">
                <div :style="{
                  border: varieties_error
                    ? '1px solid rgba(200, 100, 100)'
                    : '1px solid rgba(255,255,255,0.6)',
                  backgroundColor: !dark_theme ? 'rgba(50, 50, 50, 0.1)' : ''
                }" class="chip-box">
                  <span v-if="!varieties_array.length"
                    :style="{ color: varieties_error ? 'rgba(210, 90, 100)' : 'inherit' }">
                    Insira uma variedade (obrigatório)
                  </span>
                  <v-chip-group v-else column :disabled="loading">
                    <v-chip v-for="variety in varieties_array" :key="variety" closable
                      @click:close="removeVariety(variety)">
                      {{ variety }}
                    </v-chip>
                  </v-chip-group>
                </div>
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
            <v-btn :loading="loading" :disabled="loading" :color="color" @click="editItem">
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
const translation = { pt_upper: 'Cultura', pt_lower: 'cultura', table: 'crops', model: 'Crop', api: 'crop' }
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
  name: '',
  varieties: ''
})
const form = ref(null)
const variety_input = ref('')
const varieties_array = ref([])
const varieties_error = ref(false)

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
    varieties_array.value = item['varieties'].split(';')
  }
})

// Methods
function addVariety() {
  const value = variety_input.value.trim()
  if (!value) return
  if (varieties_array.value.includes(value)) return

  const next = [...varieties_array.value, value].join(';')
  if (next.length > 240) {
    snackbar.open({ preset: 'alert', text: 'Tamanho máximo atingido, abrevie o nome das variedades.' })
    return
  }

  varieties_array.value.push(value)
  variety_input.value = ''
  syncModel()
  varieties_error.value = false
}

function removeVariety(value) {
  varieties_array.value = varieties_array.value.filter(v => v !== value)
  syncModel()
}

function syncModel() {
  item.varieties = varieties_array.value.join(';')
}

async function editItem() {
  const { valid } = await form.value.validate()
  if (!varieties_array.value.length) {
    varieties_error.value = true
    return
  }
  if (!valid) return
  loading.value = true
  api.put('edit_' + translation.api + '/' + item.id, item).then((response) => {
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
  item.varieties = ''
  variety_input.value = ''
  varieties_array.value = []
  varieties_error.value = false
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm, 300)
}

</script>
<style scoped>
.chip-box {
  min-height: 50px;
  border-radius: 10px;
  background-color: rgba(50, 50, 50, 0.5);
  padding: 12px;
  display: flex;
  align-items: center;
}
</style>