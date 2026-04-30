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
                <v-img v-if="img" width="32" class="mr-1" :src="'media/icons/' + img" />
                <v-icon v-else :color="color" class="mr-1">{{ icon }}</v-icon>
                <span :style="{ color }">{{ (loading ? 'Editando ' : 'Editar ') + translation.pt_upper }}</span>                                
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
              <v-col cols="12" md="6">
                <v-text-field v-model="item.name" label="Nome" clearable :disabled="loading" :color="color"
                  :rules="getRules({ required: true, minlen: { val: 3 } })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.category" label="Categoria" :items="categories" clearable :color="color"
                  :disabled="loading" :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.active_ingredient" label="Princípio Ativo" :items="active_ingredients" clearable
                  :disabled="loading" :color="color" hint="Opcional" persistent-hint />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.unit" label="Unidade"
                  :items="[{ title: 'KG', value: 0 }, { title: 'L', value: 1 }]" clearable :color="color"
                  :disabled="loading" persistent-hint :rules="getRules({ required: true })" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="item.action_mode" label="Modo de Ação" :items="action_modes" clearable :color="color"
                  :disabled="loading" hint="Opcional" persistent-hint />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="item.compatibility_restrictions" label="Restrições / Compatibilidade" clearable
                  :disabled="loading" :color="color" hint="Opcional" persistent-hint />
              </v-col>
              <v-divider :thickness="3" class="border-opacity-50 my-2"
                :color="dark_theme ? 'grey-lighten-4' : 'grey-darken-3'"></v-divider>
              <v-col cols="12">
                <strong>- Composição (%)</strong>
              </v-col>
              <v-col v-for="field in nutrient_fields" :key="field.key" cols="12" md="4">
                <v-text-field v-model="item[field.key]" :disabled="loading" clearable :label="field.label"
                  :color="color" variant="outlined" @keyup="percentageMask(item, field.key)"
                  :rules="getRules({ float_range: { val: [0.01, 100] } })" hint="Em porcentagem (%)" persistent-hint />
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
const translation = { pt_upper: 'Produto', pt_lower: 'produto', table: 'products', model: 'Product', api: 'product' }
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
  category: '',
  active_ingredient: '',
  unit: '',
  action_mode: '',
  compatibility_restrictions: '',
  nitrogen: '',
  phosphorus: '',
  potassium: '',
  calcium: '',
  magnesium: '',
  sulfur: '',
  boron: '',
  copper: '',
  manganese: '',
  zinc: '',
  iron: '',
  molybdenum: '',
  silicon: ''
})
const categories = [
  'Fertilizante',
  'Herbicida',
  'Fungicida',
  'Inseticida',
  'Aditivo'
]
const active_ingredients = [
  'Nitrogênio',
  'Fósforo',
  'Potássio',
  'Glifosato',
  'Ureia',
  'MAP',
  'DAP'
]
const action_modes = [
  'Sistêmico',
  'Contato'
]
const nutrient_fields = [
  { key: 'nitrogen', label: 'Nitrogênio (N) %' },
  { key: 'phosphorus', label: 'Fósforo (P) %' },
  { key: 'potassium', label: 'Potássio (K) %' },
  { key: 'calcium', label: 'Cálcio (Ca) %' },
  { key: 'magnesium', label: 'Magnésio (Mg) %' },
  { key: 'sulfur', label: 'Enxofre (S) %' },
  { key: 'boron', label: 'Boro (B) %' },
  { key: 'copper', label: 'Cobre (Cu) %' },
  { key: 'manganese', label: 'Manganês (Mn) %' },
  { key: 'zinc', label: 'Zinco (Zn) %' },
  { key: 'iron', label: 'Ferro (Fe) %' },
  { key: 'molybdenum', label: 'Molibdênio (Mo) %' },
  { key: 'silicon', label: 'Silício (Si) %' }
]
const form = ref(null)

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
    item['unit'] = Number(item['unit'])
  }
})

// Methods
async function editItem() {
  const { valid } = await form.value.validate()
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
  item.category = ''
  item.active_ingredient = ''
  item.unit = ''
  item.action_mode = ''
  item.compatibility_restrictions = ''
  item.nitrogen = ''
  item.phosphorus = ''
  item.potassium = ''
  item.calcium = ''
  item.magnesium = ''
  item.sulfur = ''
  item.boron = ''
  item.copper = ''
  item.manganese = ''
  item.zinc = ''
  item.iron = ''
  item.molybdenum = ''
  item.silicon = ''
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(() => resetForm, 300)
}

</script>
