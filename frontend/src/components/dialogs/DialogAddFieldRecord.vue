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
                    <span :style="{ color }">{{ (loading ? 'Adicionando ' : 'Adicionar ') + translation.pt_upper }}</span>
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
          <v-form ref="form" @submit.prevent="addItem">
            <v-row>
              <v-col cols="12">
                <v-select v-model="item.service" label="Serviço" :items="services" clearable :disabled="loading"
                  :color="color" :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="item.date" label="Data" placeholder="dd/mm/aaaa" maxlength="10" clearable
                  append-icon="mdi-calendar" :disabled="loading" :color="color" @keyup="dateMask(item, 'date')"
                  :rules="getRules({ required: true, date_br: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.planting_id" label="Plantio" :items="plantings" item-title="name"
                  item-value="id" clearable :loading="loading_plantings" :disabled="loading || loading_plantings"
                  :color="color" :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.tractor_id" label="Trator" :items="tractors" item-title="name" item-value="id"
                  clearable :loading="loading_tractors" :disabled="loading || loading_tractors" :color="color" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.implement_id" label="Implemento" :items="implements_items" item-title="name"
                  item-value="id" clearable :loading="loading_implements"
                  :disabled="loading || loading_implements" :color="color" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.product_id" label="Produto" :items="products" item-title="name" item-value="id"
                  clearable :loading="loading_products" :disabled="loading || loading_products" :color="color"
                  :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="item.dosage" label="Dosagem ha" placeholder="0.00" type="number" step="0.01"
                  min="0.01" clearable :disabled="loading || !item.product_id" :color="color"
                  :rules="item.product_id ? getRules({ required: true }) : []">
                  <template #append-inner>
                   <v-chip v-if="item.product_id" :color="selected_product_unit == 0 ? 'teal' : 'blue'" :prepend-icon="selected_product_unit == 0 ? 'mdi-weight-kilogram' : 'mdi-bottle-tonic'">
                    <span class="text-caption font-weight-bold mr-1">{{ selected_product_unit_label }}</span>
                   </v-chip> 
                  </template>
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="item.notes" label="Observações" rows="3" auto-grow clearable :disabled="loading" maxLength="500" counter
                  :color="color" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <div class="card-actions-sticky">
          <v-divider />
          <v-card-actions>
            <v-spacer />
            <v-btn :disabled="loading" color="red" variant="outlined" @click="closeDialog">Cancelar</v-btn>
            <v-btn :loading="loading"
              :disabled="loading || loading_implements || loading_plantings || loading_products || loading_tractors"
              :color="color" @click="addItem">Confirmar</v-btn>
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
const translation = { pt_upper: 'Ficha de Campo', pt_lower: 'ficha de campo', table: 'field_records', model: 'FieldRecord', api: 'field_record' }
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

const plantings = ref([])
const tractors = ref([])
const implements_items = ref([])
const products = ref([])

const loading_plantings = ref(false)
const loading_tractors = ref(false)
const loading_implements = ref(false)
const loading_products = ref(false)

const item = reactive({
  service: '',
  date: '',
  planting_id: null,
  tractor_id: null,
  implement_id: null,
  product_id: null,
  dosage: null,
  notes: ''
})

const services = [
  'Ferti Irrigação',
  'Puverização',
  'Adubação',
  'Colheita',
  'Plantio'
]

// Computeds
const selected_product = computed(() => {
  return products.value.find(product => product.id == item.product_id)
})

const selected_product_unit = computed(() => {
  return selected_product.value?.unit
})

const selected_product_unit_label = computed(() => {
  if (item.product_id == null) return ''
  return selected_product_unit.value == 0 ? 'KG' : 'L'
})

// Watchers
watch(() => props.model, (open) => {
  if (open) {
    resetForm()
    getPlantings()
    getTractors()
    getImplements()
    getProducts()
  }
})

watch(() => item.product_id, (value, old_value) => {
  if (!value) {
    item.dosage = ''
  }
})

// Methods
async function addItem() {
  const { valid } = await form.value.validate()
  if (!valid) return
  loading.value = true
  item.date = convertBRToISO(item.date)
  api.post('add_' + translation.api, item).then(res => {
    emit('new_register', res.data)
    closeDialog()
  }).catch(error => {
    console.log(error)
    snackbar.open({ preset: 'error' })
    closeDialog()
  })
}

function getPlantings(attempt = 1) {
  loading_plantings.value = true
  api.get('get_plantings').then(response => {
    plantings.value = response.data
    loading_plantings.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getPlantings(attempt + 1), 1000)
    } else {
      loading_plantings.value = false
    }
  })
}

function getTractors(attempt = 1) {
  loading_tractors.value = true
  api.get('get_tractors').then(response => {
    tractors.value = response.data
    loading_tractors.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getTractors(attempt + 1), 1000)
    } else {
      loading_tractors.value = false
    }
  })
}

function getImplements(attempt = 1) {
  loading_implements.value = true
  api.get('get_implements').then(response => {
    implements_items.value = response.data
    loading_implements.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getImplements(attempt + 1), 1000)
    } else {
      loading_implements.value = false
    }
  })
}

function getProducts(attempt = 1) {
  loading_products.value = true
  api.get('get_products').then(response => {
    products.value = response.data
    loading_products.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getProducts(attempt + 1), 1000)
    } else {
      loading_products.value = false
    }
  })
}

function dateMask(object, key) {
  let value = object[key]
  if (value === null || value === undefined) {
    object[key] = ''
    return
  }
  value = value.toString().replace(/[^\d]/g, '')
  if (!value) {
    object[key] = ''
    return
  }
  if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2)
  if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5)
  if (value.length > 10) value = value.slice(0, 10)
  object[key] = value
  return object[key]
}

function convertBRToISO(date) {
  if (!date) return null
  const [day, month, year] = date.split('/')
  return `${year}-${month}-${day}`
}

function resetForm() {
  item.service = ''
  item.date = ''
  item.planting_id = ''
  item.tractor_id = ''
  item.implement_id = ''
  item.product_id = ''
  item.dosage = ''
  item.notes = ''
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm(), 300)
}
</script>