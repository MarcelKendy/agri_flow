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
                    <span :style="{ color }">{{ (loading ? 'Editando ' : 'Editar ') + translation.pt_upper }}</span>
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
          <v-form ref="form" @submit.prevent="editItem">
            <v-row>
              <v-col cols="12">
                  <v-switch v-model="item.status" :color="color" label="Concluído"></v-switch>
              </v-col>
              <v-col cols="12">
                <v-select style="margin-top: -40px !important" v-model="item.service" label="Serviço" :items="services" clearable :disabled="loading"
                  :color="color" :rules="getRules({ required: true })" />
              </v-col>             
              <v-col cols="12" md="6">
                <v-text-field v-model="item.date" label="Data" placeholder="dd/mm/aaaa" maxlength="10" clearable
                  append-inner-icon="mdi-calendar" :disabled="loading" :color="color" @keyup="dateMask(item, 'date')"
                  :rules="getRules({ required: true, date_br: true })" />
              </v-col>

              <v-col cols="12" md="6">
                <v-select v-model="item.planting_id" label="Plantio" :items="plantings" item-title="name"
                  item-value="id" clearable :loading="loadingPlantings" :disabled="loading || loadingPlantings"
                  :color="color" :rules="getRules({ required: true })" />
              </v-col>

              <v-col cols="12" md="6">
                <v-select v-model="item.tractor_id" label="Trator" :items="tractors" item-title="name" item-value="id"
                  clearable :loading="loadingTractors" :disabled="loading || loadingTractors" :color="color" />
              </v-col>

              <v-col cols="12" md="6">
                <v-select v-model="item.implement_id" label="Implemento" :items="implementsItems" item-title="name"
                  item-value="id" clearable :loading="loadingImplements" :disabled="loading || loadingImplements"
                  :color="color" />
              </v-col>

              <v-col cols="12" md="6">
                <v-select v-model="item.product_id" label="Produto" :items="products" item-title="name" item-value="id"
                  clearable :loading="loadingProducts" :disabled="loading || loadingProducts" :color="color"
                  :rules="getRules({ required: true })" />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field v-model="item.dosage" label="Dosagem ha" placeholder="0.00" type="number" step="0.01"
                  min="0.01" clearable :disabled="loading || !item.product_id" :color="color"
                  :rules="item.product_id ? getRules({ required: true }) : []">
                  <template #append-inner>
                    <v-chip v-if="item.product_id" :color="selectedProductUnit == 0 ? 'teal' : 'blue'"
                      :prepend-icon="selectedProductUnit == 0 ? 'mdi-weight-kilogram' : 'mdi-bottle-tonic'">
                      <span class="bold mr-1">{{ selectedProductUnitLabel }}</span>
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
              :disabled="loading || loadingImplements || loadingPlantings || loadingProducts || loadingTractors"
              :color="color" @click="editItem">Confirmar</v-btn>
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

const translation = { pt_upper: 'Ficha de Campo', pt_lower: 'ficha de campo', table: 'field_records', model: 'FieldRecord', api: 'field_record' }

const snackbar = useSnackbarStore()
const emit = defineEmits(['close', 'edited_register'])

const props = defineProps({
  color: { type: String },
  model: { type: Boolean, required: true },
  icon: { type: String, required: true },
  img: { type: String },
  data: { type: Object, required: true }
})

const { smAndDown } = useDisplay()
const useThemeRef = useTheme()
const darkTheme = computed(() => useThemeRef.global.name.value == 'customDark')

const loading = ref(false)
const form = ref(null)

const plantings = ref([])
const tractors = ref([])
const implementsItems = ref([])
const products = ref([])

const loadingPlantings = ref(false)
const loadingTractors = ref(false)
const loadingImplements = ref(false)
const loadingProducts = ref(false)

const services = [
  'Ferti Irrigação',
  'Puverização',
  'Adubação',
  'Colheita',
  'Plantio'
]

const item = reactive({
  id: '',
  service: '',
  date: '',
  planting_id: '',
  tractor_id: '',
  implement_id: '',
  product_id: '',
  dosage: '',
  notes: '',
  status: ''
})

const selectedProduct = computed(() => {
  return products.value.find(product => product.id == item.product_id)
})

const selectedProductUnit = computed(() => {
  return selectedProduct.value?.unit
})

const selectedProductUnitLabel = computed(() => {
  if (!item.product_id) return ''
  return selectedProductUnit.value == 0 ? 'KG' : 'L'
})

watch(() => props.model, open => {
  if (open) {
    resetForm()
    getPlantings()
    getTractors()
    getImplements()
    getProducts()
    for (const key in item) {
      if (props.data.hasOwnProperty(key)) {
        item[key] = props.data[key]
      }
    }
    item.date = convertISOToBR(item.date)
  }
})

watch(() => item.product_id, value => {
  if (!value) {
    item.dosage = ''
  }
})

async function editItem() {
  const { valid } = await form.value.validate()
  if (!valid) return

  loading.value = true
  item.date = convertBRToISO(item.date)
  item.status = Number(item.status)
  api.put('edit_' + translation.api + '/' + item.id, item).then(response => {
    response.data.status = Boolean(response.data.status)
    emit('edited_register', response.data)
    closeDialog()
  }).catch(error => {
    console.log(error)
    snackbar.open({ preset: 'error' })
    closeDialog()
  })
}

function getPlantings(attempt = 1) {
  loadingPlantings.value = true
  api.get('get_plantings').then(response => {
    plantings.value = response.data
    loadingPlantings.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getPlantings(attempt + 1), 1000)
    } else {
      loadingPlantings.value = false
    }
  })
}

function getTractors(attempt = 1) {
  loadingTractors.value = true
  api.get('get_tractors').then(response => {
    tractors.value = response.data
    loadingTractors.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getTractors(attempt + 1), 1000)
    } else {
      loadingTractors.value = false
    }
  })
}

function getImplements(attempt = 1) {
  loadingImplements.value = true
  api.get('get_implements').then(response => {
    implementsItems.value = response.data
    loadingImplements.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getImplements(attempt + 1), 1000)
    } else {
      loadingImplements.value = false
    }
  })
}

function getProducts(attempt = 1) {
  loadingProducts.value = true
  api.get('get_products').then(response => {
    products.value = response.data
    loadingProducts.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getProducts(attempt + 1), 1000)
    } else {
      loadingProducts.value = false
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
}

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

function resetForm() {
  item.id = ''
  item.service = ''
  item.date = ''
  item.planting_id = ''
  item.tractor_id = ''
  item.implement_id = ''
  item.product_id = ''
  item.dosage = ''
  item.notes = ''
  item.status = ''
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm(), 300)
}
</script>