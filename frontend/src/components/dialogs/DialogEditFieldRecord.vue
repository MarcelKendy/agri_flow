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
                  append-icon="mdi-calendar" :disabled="loading" :color="color" @keyup="dateMask(item, 'date')"
                  :rules="getRules({ required: true, date_br: true })" />
              </v-col>

              <v-col cols="12" md="6">
                <v-select v-model="item.planting_id" label="Plantio" :items="plantings" item-title="name"  append-icon="mdi-sprout" :readonly="planting_id && planting_id > 0" no-data-text="Nenhum dado cadastrado..."
                  item-value="id" :clearable="!planting_id" :loading="loading_plantings" :disabled="loading || loading_plantings"
                  :color="color" :rules="getRules({ required: true })" />
              </v-col>

              <v-col cols="12" md="6">
                <v-select v-model="item.tractor_id" label="Trator" :items="tractors" item-title="name" item-value="id" no-data-text="Nenhum dado cadastrado..." append-icon="mdi-tractor"
                  clearable :loading="loading_tractors" :disabled="loading || loading_tractors" :color="color" />
              </v-col>

              <v-col cols="12" md="6">
                <v-select v-model="item.implement_id" label="Implemento" :items="implements_items" item-title="name" no-data-text="Nenhum dado cadastrado..." append-icon="mdi-cog-outline"
                  item-value="id" clearable :loading="loading_implements" :disabled="loading || loading_implements"
                  :color="color" />
              </v-col>

              <v-col cols="12" style="border: solid 1px grey; border-radius: 10px; padding: 20px;" :style="dark_theme ? 'background-color: rgba(0, 0, 0, 0.1);' : 'background-color: rgba(160, 160, 160, 0.1); border-color: rgba(150, 150, 150)'">
                <div class="mb-3">
                  <strong style="font-size: 18px">- Produtos</strong>
                  <v-icon class="ml-2">mdi-flask-outline</v-icon>
                </div>                
                <div
                  v-for="(product_item, index) in item.products"
                  :key="index"
                  class="mb-4"
                >
                  <v-row class="align-center">
                    <v-col cols="12" md="6">
                      <v-select
                        v-model="product_item.product_id"
                        label="Produto"
                        :items="products"
                        item-title="name"
                        item-value="id"
                        clearable
                        :loading="loading_products"
                        :disabled="loading || loading_products"
                        :color="color"
                        no-data-text="Nenhum dado cadastrado..."
                        :rules="getRules({ required: true })"                        
                        :item-props="item => ({
                          disabled: isProductDisabled(item.id, index)
                        })"
                      />
                    </v-col>

                    <v-col cols="12" md="5" :style="smAndDown ? 'margin-bottom: -15px !important' : ''">
                      <v-text-field
                        v-model="product_item.dosage"
                        label="Dosagem ha"
                        :placeholder="getProductLastDosage(product_item.product_id)"
                        type="number"
                        step="0.01"
                        min="0.01"
                        clearable
                        :disabled="loading || !product_item.product_id"
                        :color="color"
                        :rules="product_item.product_id ? getRules({ required: true }) : []"
                      />
                    </v-col>

                    <v-col cols="12" md="1" class="d-flex" :class="smAndDown ? 'align-start mb-2' : 'align-center pb-4'">
                      <v-btn
                        :disabled="item.products.length <= 1"
                        icon="mdi-delete"
                        color="red"
                        size="x-small"
                        :variant="smAndDown ? 'elevated' : 'outlined'"
                        @click="removeProduct(index)"
                      ></v-btn>
                    </v-col>
                  </v-row>
                  <v-divider class="mt-1"></v-divider>
                </div>

                <v-btn
                  variant="outlined"
                  :color="color"
                  prepend-icon="mdi-plus"
                  size="small"
                  @click="addProduct"
                >
                  Adicionar produto
                </v-btn>
              </v-col>

              <v-col cols="12">
                <v-textarea v-model="item.notes" label="Observações" rows="3" auto-grow clearable :disabled="loading" maxLength="500" counter append-icon="mdi-note-text-outline"
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
              :color="color" @click="editItem">Confirmar</v-btn>
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
const emit = defineEmits(['close', 'edited_register'])

const props = defineProps({
  color: { type: String },
  model: { type: Boolean, required: true },
  icon: { type: String, required: true },
  planting_id : { type: Number, required: false },
  img: { type: String },
  data: { type: Object, required: true }
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

const services = [
  'Ferti Irrigação',
  'Pulverização',
  'Adubação',
  'Colheita',
  'Plantio'
]

const item = reactive({
  id: '',
  service: '',
  date: '',
  planting_id: props.planting_id ?? '',
  tractor_id: '',
  implement_id: '',
  products: [ { product_id: '', dosage: '' } ],
  notes: '',
  status: ''
})

// Watchers
watch(() => props.model, open => {
  if (open) {
    resetForm()
    getPlantings()
    getTractors()
    getImplements()
    getProducts()
    for (const key in item) {
      if (props.data.hasOwnProperty(key) && key !== 'products') {
        item[key] = props.data[key]
      }
    }
    item.products = props.data.products?.map(p => ({
      product_id: p.product_id,
      dosage: p.dosage
    })) || [ { product_id: '', dosage: '' } ]
    item.date = convertISOToBR(item.date)
    item.status =
      typeof item.status === 'boolean'
        ? item.status
        : Number(item.status) === 1
  }
})

watch(() => item.product_id, value => {
  if (!value) {
    item.dosage = ''
  }
})

// Methods
function isProductDisabled(product_id, current_index) {
  return item.products.some((p, index) =>
    index !== current_index && p.product_id == product_id
  )
}

function getProductLastDosage(product_id) {
  if (!product_id) return '0.00'
  const product = products.value.find(p => p.id == product_id)
  return String(product?.last_dosage ?? '0.00')
}

function addProduct() {
  item.products.push({ product_id: '', dosage: '' })
}

function removeProduct(index) {
  item.products.splice(index, 1)
}

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
  loading_plantings.value = true
  api.get('get_plantings').then(response => {
    plantings.value = response.data
    loading_plantings.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getPlantings(attempt + 1), 1000)
    } else {
      snackbar.open({ preset: 'error' })
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
      snackbar.open({ preset: 'error' })
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
      snackbar.open({ preset: 'error' })
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
      snackbar.open({ preset: 'error' })
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
  item.planting_id = props.planting_id ?? ''
  item.tractor_id = ''
  item.implement_id = ''
  item.products = [ { product_id: '', dosage: '' } ]
  item.notes = ''
  item.status = ''
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm(), 300)
}
</script>