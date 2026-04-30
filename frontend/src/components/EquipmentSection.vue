<template>
  <v-card flat style="background-color: rgba(0,0,0,0)" :loading="loading">
    <template #loader="{ isActive }">
      <v-progress-linear :active="isActive" height="5" indeterminate color="green" />
    </template>

    <v-card-title style="position: relative;" class="bold" :class="dark_theme ? 'text-shadow-black-2' : ''" :style="smAndDown
      ? (dark_theme
        ? 'background-color: rgba(90, 90, 90, 0.2)'
        : 'background-color: rgba(150, 150, 150, 0.2)')
      : ''">
      <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
      <span class="mr-10">{{ title }}</span>
      <v-btn @click="add_dialog = true" color="green" :size="smAndDown ? 'small' : 'default'" append-icon="mdi-plus"
        class="bold new-button">
        NOVO
      </v-btn>
    </v-card-title>

    <v-divider class="mb-4" thickness="7" color="green" />

    <v-card-text>
      <v-row v-if="items.length">
        <v-col cols="12">
          <v-text-field v-model="search_field" :label="'Busca avançada em ' + items.length + ' registro(s)'"
            prepend-inner-icon="mdi-magnify" density="compact" clearable color="green" />
        </v-col>
      </v-row>

      <!-- MOBILE -->
      <v-list v-if="smAndDown" style="background-color: rgba(0,0,0,0)">
        <v-alert v-if="!items.length || !paginated_items.length" icon="mdi-information" border="bottom" class="mb-2"
          :color="dark_theme ? 'rgba(50,50,50,0.6)' : 'rgba(250,250,250,0.8)'">
          {{
            !items.length
              ? (loading
                ? 'Carregando, aguarde...'
                : 'Lista vazia, inclua um item clicando no botão "Novo" acima')
              : 'Não há dados para o filtro "' + search_field + '"'
          }}
        </v-alert>

        <v-list-item v-for="item in paginated_items" :key="item.id" :class="dark_theme ? 'list-item-dark' : 'list-item'"
          @click="auth.user.level < 1 ? null : openEditDialog(item)">
          <v-list-item-title>
            <v-row class="align-center">
              <v-col :cols="auth.user.level > 1 && !getCount(item) ? 9 : 12">
                <strong>{{ item.name }}</strong>
              </v-col>
              <v-col v-if="auth.user.level > 1 && !getCount(item)" cols="3" class="align-end">
                <v-btn class="mx-1 hover-buttons" color="red" variant="elevated" icon size="28"
                  :disabled="auth.user.level < 2" @click.stop="openDeleteDialog(item)">
                  <v-icon size="x-small">mdi-delete</v-icon>
                </v-btn>
              </v-col>
            </v-row>
          </v-list-item-title>
        </v-list-item>

        <v-row class="align-center pt-3" v-if="!loading && paginated_items.length">
          <v-col cols="12">
            <v-pagination v-model="current_page" :length="total_pages" rounded="circle" />
          </v-col>
          <v-col cols="12" class="align-end">
            <v-select label="Itens por página:" color="green" density="compact" :items="[5, 10, 15, 50, 'Todos']"
              style="max-width: 130px;" variant="outlined" v-model="items_per_page" />
          </v-col>
        </v-row>
      </v-list>

      <!-- DESKTOP -->
      <v-data-table items-per-page-text="Itens por página" :items-per-page-options="[
        { value: 10, title: '10' },
        { value: 25, title: '25' },
        { value: 50, title: '50' },
        { value: 100, title: '100' },
        { value: -1, title: 'Todos' }
      ]" v-else class="mb-2 clickable-table" :headers="headers" :items="filtered_items" fixed-header
        no-data-text="Nenhum registro encontrado" loading-text="Carregando, aguarde...">
        <template #item="{ item }">
          <tr :class="dark_theme ? 'table-row' : 'table-row-light'"
            @click="auth.user.level < 1 ? null : openEditDialog(item)">
            <td>{{ item.name }}</td>
            <td>
              <v-btn class="mx-1 hover-buttons" color="red" variant="elevated" icon size="x-small"
                :disabled="auth.user.level < 2 || getCount(item)" @click.stop="openDeleteDialog(item)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>

      <component :is="add_dialog_component" :model="add_dialog" :icon="icon" @new_register="pushNewItem"
        @close="add_dialog = false" />

      <component :is="edit_dialog_component" :model="edit_dialog" color="orange" :data="edit_dialog_data" :icon="icon"
        @edited_register="editItem" @close="edit_dialog = false" />

      <DialogDelete :model="delete_dialog" :data="delete_dialog_data" :icon="icon" :data_name="api_name"
        @deleted="popItem" @close="delete_dialog = false" />
    </v-card-text>
  </v-card>
</template>

<script setup>
// Imports
import api from '@/plugins/axios'
import { ref, computed, reactive, watch } from 'vue'
import { useTheme, useDisplay } from 'vuetify'
import { useAuthStore } from '@/stores/auth'
import { useSnackbarStore } from '@/stores/snackbar'
import DialogDelete from '@/components/dialogs/DialogDelete.vue'

// Variables
const props = defineProps({
  title: String,
  icon: String,
  color: { default: 'green' },
  api_name: String,
  count_field: String,
  add_dialog_component: Object,
  edit_dialog_component: Object,
  active_tab: Boolean
})

const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value === 'customDark')
const auth = useAuthStore()
const snackbar = useSnackbarStore()

const items = ref([])
const loading = ref(false)
const search_field = ref('')
const current_page = ref(1)
const items_per_page = ref(10)

const add_dialog = ref(false)
const edit_dialog = ref(false)
const delete_dialog = ref(false)

const edit_dialog_data = reactive({})
const delete_dialog_data = reactive({})

const headers = [
  { title: 'Nome', key: 'name', width: '90%' },
  { title: 'Ações', key: 'actions', sortable: false, width: '10%' }
]

// Computeds
const filtered_items = computed(() => {
  current_page.value = 1
  if (!search_field.value) return items.value
  const search = search_field.value.toLowerCase().trim()
  return items.value.filter(item =>
    String(item.name).toLowerCase().includes(search)
  )
})

const paginated_items = computed(() => {
  if (items_per_page.value === 'Todos') return filtered_items.value
  const start = (current_page.value - 1) * items_per_page.value
  return filtered_items.value.slice(start, start + items_per_page.value)
})

const total_pages = computed(() => {
  if (items_per_page.value === 'Todos') return 1
  return Math.ceil(filtered_items.value.length / items_per_page.value)
})

// Created
getItems()

// Watchers
watch(() => props.active_tab, (active) => {
  if (active) {
    getItems()
  }
})

watch(items_per_page, () => {
  current_page.value = 1
})

// Methods
function getItems(attempt = 1) {
  loading.value = true
  api.get(`get_${props.api_name}s`).then(response => {
    items.value = response.data
    loading.value = false
  }).catch(error => {
    console.log(error)
    if (attempt <= 5) {
      setTimeout(() => getItems(attempt + 1), 1000)
    } else {
      snackbar.open({ preset: 'error' })
      loading.value = false
    }
  })
}

function getCount(item) {
  return item[props.count_field] > 0
}

function pushNewItem(item) {
  items.value.unshift(item)
}

function editItem(edited_item) {
  items.value.splice(items.value.findIndex(item => item.id == edited_item.id), 1, edited_item)
}

function popItem(id) {
  items.value = items.value.filter(item => item.id != id)
}

function openEditDialog(item) {
  Object.assign(edit_dialog_data, item)
  edit_dialog.value = true
}

function openDeleteDialog(item) {
  Object.assign(delete_dialog_data, item)
  delete_dialog.value = true
}
</script>
