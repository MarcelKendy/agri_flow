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
              <v-col cols="10" md="11">
                <v-text-field v-model="item.name" label="Nome" clearable :disabled="loading" :color="color"
                  :rules="getRules({ required: true, minlen: { val: 3 } })" />
              </v-col>
              <v-col cols="2" md="1" style="margin-left: -10px; position: relative;">
                <span class="font-italic" :class="dark_theme ? 'text-shadow-black-1' : ''"
                  style="position: absolute; top: 5px;">Ativo</span>
                <v-switch v-model="item.status" :disabled="loading" style="position: absolute; top: 10px;"
                  :color="color" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.crop_id" :loading="loading_crops" :disabled="loading_crops || loading"
                  no-data-text="Nenhum dado cadastrado..." label="Cultura" :items="crops" item-title="name"
                  item-value="id" clearable :color="color" :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.variety" label="Variedade" hint="Opcional" persistent-hint :items="varieties"
                  no-data-text="Nenhum dado cadastrado..." clearable :loading="loading_crops"
                  :disabled="loading || loading_crops || !item.crop_id || !varieties" :color="color" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.pivot_id" :loading="loading_pivots" :disabled="loading_pivots || loading"
                  no-data-text="Nenhum dado cadastrado..." label="Pivô" :items="pivots" item-title="name"
                  item-value="id" clearable :color="color" :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="item.size_ha" label="Área (ha)" placeholder="0.00" type="number" step="0.01"
                  min="0.01" clearable :disabled="loading" :color="color" :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="item.date" label="Data" placeholder="dd/mm/aaaa" maxlength="10" clearable
                  append-icon="mdi-calendar" :disabled="loading" :color="color" @keyup="dateMask(item)"
                  :rules="getRules({ required: true, date_br: true })" />
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
            <v-btn :loading="loading" :disabled="loading || loading_crops || loading_pivots" :color="color" @click="editItem">
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
const translation = { pt_upper: 'Plantio', pt_lower: 'plantio', table: 'plantings', model: 'Planting', api: 'planting' }
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
const loading_crops = ref(false)
const loading_pivots = ref(false)
const crops = ref([])
const pivots = ref([])
const item = reactive({
  id: '',
  name: '',
  crop_id: '',
  pivot_id: '',
  size_ha: '',
  date: '',
  variety: '',
  status: true
})

const form = ref(null)

// Computeds
const model_computed = computed(() => props.model)

const varieties = computed(() => {
  const crop = crops.value.find(c => c.id === item.crop_id)
  if (!crop?.varieties) return []
  return crop.varieties
    .split(';')
    .map(v => v.trim())
    .filter(v => v)
})

// Watchers
watch(model_computed, (v) => {
  if (v) {
    resetForm()
    getCrops()
    getPivots()
    for (const key in item) {
      if (props.data.hasOwnProperty(key)) {
        item[key] = props.data[key]
      }
    }
    item['status'] = Boolean(item['status'])
    item['date'] = convertISOToBR(item['date'])
  }
})

watch(() => item.crop_id, (value, old_value) => {
  if (old_value) {
    item.variety = ''
  }
})

// Methods
function convertBRToISO(date) {
  if (!date) return null
  const [day, month, year] = date.split('/')
  return `${year}-${month}-${day}`
}

function convertISOToBR(date) {
  if (!date) return ''
  const [year, month, day] = date.split('-')
  return `${day}/${month}/${year}`
}

function getCrops(attempt = 1) {
  loading_crops.value = true
  api.get('get_crops')
    .then(response => {
      crops.value = response.data
      loading_crops.value = false
    })
    .catch(error => {
      console.log(error)
      if (attempt <= 5) {
        setTimeout(() => getCrops(attempt + 1), 1000)
      } else {
        loading_crops.value = false
      }
    })
}

function getPivots(attempt = 1) {
  loading_pivots.value = true
  api.get('get_pivots')
    .then(response => {
      pivots.value = response.data
      loading_pivots.value = false
    })
    .catch(error => {
      console.log(error)
      if (attempt <= 5) {
        setTimeout(() => getPivots(attempt + 1), 1000)
      } else {
        loading_pivots.value = false
      }
    })
}

function resetForm() {
  item.id = ''
  item.name = ''
  item.crop_id = ''
  item.pivot_id = ''
  item.size_ha = ''
  item.date = ''
  item.variety = ''
  item.status = true
  loading.value = false
}

async function editItem() {
  const { valid } = await form.value.validate()
  if (!valid) return
  loading.value = true
  item.date = convertBRToISO(item.date)
  item.status = Number(item.status)
  api.put('edit_' + translation.api + '/' + item.id, item).then((response) => {
    emit('edited_register', response.data)
    closeDialog()
  }).catch((error) => {
    console.log(error)
    snackbar.open({ preset: 'error' })
    closeDialog()
  })
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm, 300)
}

</script>
