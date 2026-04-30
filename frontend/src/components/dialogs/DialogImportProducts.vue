<template>
  <div>
    <v-dialog v-model="props.model" persistent width="900px">

      <v-card :loading="loading">
        <template v-slot:loader="{ isActive }">
          <v-progress-linear :active="isActive" :color="color" height="7" indeterminate />
        </template>
        <div ref="scroll_container" class="dialog-scroll-container">
          <div class="card-header-sticky">
            <v-card-title class="mt-1">
              <v-row>
                <v-col :cols="smAndDown ? 12 : 10">
                  <v-icon class="mr-1" :color="color">{{ icon }}</v-icon>
                  <span :style="{ color }">
                    Importar Produtos
                  </span>
                </v-col>
                <v-col v-if="!smAndDown" cols="2" class="align-center">
                  <v-img height="48" src="media/icons/logo.png" />
                </v-col>
              </v-row>
            </v-card-title>

            <v-card-subtitle class="mb-3">
              <span v-if="loading">Aguarde...</span>
              <span v-else>Cole abaixo um JSON contendo um array de produtos</span>
            </v-card-subtitle>

            <v-divider />
          </div>

          <v-card-text>

            <v-expansion-panels class="mb-4" style="border: solid 1px rgba(150, 150, 150, 0.5);"
              :bg-color="dark_theme ? 'rgba(0, 0, 0, 0.206)' : 'rgba(100, 100, 120, 0.106)'">
              <v-expansion-panel>
                <v-expansion-panel-title>
                  <v-icon size="x-small" class="mr-2">mdi-arrow-right</v-icon>
                  Exemplo de JSON aceito
                  <v-btn :size="smAndDown ? 'x-small' : 'small'" variant="elevated" prepend-icon="mdi-content-copy"
                    class="ml-2" :color="color" @click="copyExampleJson">
                    Copiar
                  </v-btn>
                </v-expansion-panel-title>
                <v-expansion-panel-text :style="dark_theme ? '' : 'background-color: rgba(0, 0, 0, 0.806); color: white'">
                  <div class="json-wrapper">
                    <pre class="json-example" v-html="highlighted_example_json"></pre>
                  </div>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>

            <v-textarea v-model="raw_json" prepend-inner-icon="mdi-database-import" label="JSON dos produtos" rows="8"
              clearable :disabled="loading" :color="color" />

            <div v-if="validated" :class="dark_theme ? 'data-table-container-dark' : 'data-table-container-light'">
              <strong><v-icon color="green">mdi-check</v-icon> Produtos validados: {{ parsed_items.length }}</strong>
              <v-data-table items-per-page-text="Itens por página" :items-per-page-options="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'Todos' }
              ]" class="mt-2" style="border-radius: 10px !important" :headers="preview_headers" :items="parsed_items"
                density="compact" fixed-header no-data-text="Nenhum registro encontrado"
                loading-text="Carregando, aguarde..." item-key="name">
                <template #item.unit="{ item }">
                  <v-chip size="small" :color="item.unit == 0 ? 'teal' : 'blue'" :prepend-icon="item.unit == 0
                    ? 'mdi-weight-kilogram'
                    : 'mdi-bottle-tonic'">
                    <template #append>
                      <v-avatar end>
                        {{ item.unit == 0 ? 'KG' : 'L' }}
                      </v-avatar>
                    </template>
                  </v-chip>
                </template>
              </v-data-table>
            </div>

          </v-card-text>

          <div class="card-actions-sticky">
            <v-divider />
            <v-card-actions>
              <v-spacer />
              <v-btn color="red" variant="outlined" :disabled="loading" @click="closeDialog">
                Cancelar
              </v-btn>

              <v-btn v-if="!validated" :color="color" :disabled="loading" @click="showValidationError">
                Validar JSON
              </v-btn>

              <v-btn v-else :color="color" :loading="loading" @click="confirmImport">
                Confirmar
              </v-btn>
            </v-card-actions>
          </div>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
// Imports
import { ref, watch, computed, nextTick } from 'vue'
import api from '@/plugins/axios.js'
import { useTheme, useDisplay } from 'vuetify'
import { useSnackbarStore } from '@/stores/snackbar'

// Variables
const emit = defineEmits(['close', 'imported_products'])
const props = defineProps({
  model: { type: Boolean, required: true },
  icon: { type: String, required: true },
  color: { type: String, default: 'green' }
})

const { smAndDown } = useDisplay()
const snackbar = useSnackbarStore()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const card_text_ref = ref(null)
const scroll_container = ref(null)

const loading = ref(false)
const raw_json = ref('')
const validated = ref(false)
const parsed_items = ref([])

const example_json = `[
  {
    "name": "Ureia",
    "category": "Fertilizante",
    "unit": 0,
    "active_ingredient": "Nitrogênio",
    "action_mode": "Sistêmico",
    "compatibility_restrictions": "Não misturar com enxofre",
    "last_dosage": 120,
    "nitrogen": 45,
    "phosphorus": null,
    "potassium": 3.4,
    "calcium": null,
    "magnesium": 0.2,
    "sulfur": null,
    "boron": null,
    "copper": null,
    "manganese": null,
    "zinc": null,
    "iron": null,
    "molybdenum": null,
    "silicon": null
  }
]`

const preview_headers = [
  { title: 'Nome', key: 'name' },
  { title: 'Categoria', key: 'category' },
  { title: 'Unidade', key: 'unit' }
]

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

// Computeds
const highlighted_example_json = computed(() => {
  return example_json
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    // keys
    .replace(/"(.*?)":/g, '<span class="json-key">"$1"</span>:')
    // strings
    .replace(/:\s*"([^"]*)"/g, ': <span class="json-string">"$1"</span>')
    // numbers
    .replace(/:\s*(-?\d+(\.\d+)?)/g, ': <span class="json-number">$1</span>')
    // booleans
    .replace(/:\s*(true|false)/g, ': <span class="json-boolean">$1</span>')
    // null
    .replace(/:\s*(null)/g, ': <span class="json-null">$1</span>')
})


// Watchers
watch(() => props.model, open => {
  if (open) resetDialog()
})

watch(validated, async (is_valid, was_valid) => {
  if (is_valid && !was_valid) {
    await nextTick()

    const el = scroll_container.value
    if (!el) return

    el.scrollTop = el.scrollHeight
  }
})

watch(raw_json, () => {
  const result = runValidation(false)
  validated.value = result.valid
  parsed_items.value = result.valid ? result.items : []
})

// Methods
function copyExampleJson() {
  navigator.clipboard.writeText(example_json)
  snackbar.open({ preset: 'success', text: 'Exemplo de JSON copiado' })
}

function runValidation(show_error) {
  try {
    const parsed = JSON.parse(raw_json.value)

    if (!Array.isArray(parsed))
      throw new Error('Estrutura inválida: o JSON deve ser um array de produtos')

    if (!parsed.length)
      throw new Error('O array de produtos está vazio')

    parsed.forEach((item, index) => {
      if (!item || typeof item !== 'object' || Array.isArray(item))
        throw new Error(`Item ${index + 1}: cada produto deve ser um objeto`)

      if (!item.name || typeof item.name !== 'string' || item.name.length < 3)
        throw new Error(`Item ${index + 1} → campo "name" inválido`)

      if (!item.category || typeof item.category !== 'string') {
        throw new Error(`Item ${index + 1} → campo "category" é obrigatório`)
      }

      if (!categories.includes(item.category)) {
        throw new Error(
          `Item ${index + 1} → campo "category": valor inválido. Use um de: ${categories.join(', ')}`
        )
      }

      if (item.unit !== 0 && item.unit !== 1)
        throw new Error(`Item ${index + 1} → campo "unit" deve ser 0 ou 1`)

      if (item.active_ingredient !== undefined && item.active_ingredient !== null) {
        if (typeof item.active_ingredient !== 'string') {
          throw new Error(`Item ${index + 1} → campo "active_ingredient" deve ser texto ou null`)
        }

        if (!active_ingredients.includes(item.active_ingredient)) {
          throw new Error(
            `Item ${index + 1} → campo "active_ingredient": valor inválido. Use um de: ${active_ingredients.join(', ')}`
          )
        }
      }
      if (item.action_mode !== undefined && item.action_mode !== null) {
        if (typeof item.action_mode !== 'string') {
          throw new Error(`Item ${index + 1} → campo "action_mode" deve ser texto ou null`)
        }

        if (!action_modes.includes(item.action_mode)) {
          throw new Error(
            `Item ${index + 1} → campo "action_mode": valor inválido. Use um de: ${action_modes.join(', ')}`
          )
        }
      }
      validateOptionalString(item, 'compatibility_restrictions', index)

      if (item.last_dosage !== undefined && item.last_dosage !== null && !isValidNumber(item.last_dosage))
        throw new Error(`Item ${index + 1} → campo "last_dosage" inválido`)

      const nutrients = [
        'nitrogen', 'phosphorus', 'potassium', 'calcium', 'magnesium',
        'sulfur', 'boron', 'copper', 'manganese', 'zinc',
        'iron', 'molybdenum', 'silicon'
      ]

      nutrients.forEach(f => {
        if (item[f] === null) return
        if (item[f] !== undefined && !isValidPercentage(item[f]))
          throw new Error(`Item ${index + 1} → campo "${f}" inválido`)
      })
    })

    return { valid: true, items: parsed }
  } catch (error) {
    if (show_error) {
      snackbar.open({
        preset: 'error',
        text: error instanceof SyntaxError
          ? 'JSON inválido: erro de sintaxe'
          : error.message
      })
    }
    return { valid: false, items: [] }
  }
}

function showValidationError() {
  runValidation(true)
}

function validateOptionalString(item, field, index) {
  if (item[field] === null) return
  if (item[field] !== undefined && typeof item[field] !== 'string')
    throw new Error(`Item ${index + 1} → campo "${field}" inválido`)
}

function isValidNumber(value) {
  return typeof value === 'number' && !isNaN(value)
}

function isValidPercentage(value) {
  return isValidNumber(value) && value >= 0 && value <= 100
}

function confirmImport() {
  loading.value = true
  api.post('import_products', parsed_items.value).then(res => {
    emit('imported_products', res.data)
    closeDialog()
  }).catch(() => {
    snackbar.open({ preset: 'error' })
    loading.value = false
  })
}

function resetDialog() {
  raw_json.value = ''
  parsed_items.value = []
  validated.value = false
  loading.value = false
}

function closeDialog() {
  emit('close')
  setTimeout(resetDialog, 300)
}
</script>

<style scoped>
.json-wrapper {
  max-height: 300px;
  overflow: auto;
}

.json-example {
  white-space: pre;
  display: inline-block;
  min-width: max-content;
  padding: 12px;
  border-radius: 6px;
  font-size: 15px;
}

.json-example :deep(.json-key) {
  color: #6dd0f8;
}

.json-example :deep(.json-string) {
  color: #ce9178;
}

.json-example :deep(.json-number) {
  color: #83ed4a;
}

.json-example :deep(.json-boolean) {
  color: #3554e0;
}

.json-example :deep(.json-null) {
  color: #c586c0;
}

.data-table-container-dark {
  background-color: rgba(60, 60, 60, 0.9);
  border-radius: 10px;
  padding: 10px;
  text-shadow: black 1px 2px;
}

.data-table-container-light {
  background-color: rgba(184, 184, 184, 0.452);
  border-radius: 10px;
  padding: 10px;
}

.dialog-scroll-container {
  max-height: calc(100vh - 260px);
  /* ajustado para header + footer */
  overflow-y: auto;
  overflow-x: hidden;
}

:deep(.v-table__wrapper) {
  border-radius: 10px !important;
}
</style>