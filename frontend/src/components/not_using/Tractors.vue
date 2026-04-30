<template>
    <div>
        <v-card flat style="background-color: rgba(0, 0, 0, 0)" :loading="loading">
            <template v-slot:loader="{ isActive }">
                <v-progress-linear :active="isActive" color="green" height="5" indeterminate></v-progress-linear>
            </template>
            <v-card-title style="position: relative;" class="bold" :class="dark_theme ? 'text-shadow-black-2' : ''" :style="smAndDown ? (dark_theme ? 'background-color: rgba(90, 90, 90, 0.2)' : 'background-color: rgba(150, 150, 150, 0.2)') : ''">
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                <span class="mr-10">{{ title }}</span>
                <v-btn @click="add_dialog = true" color="green" :size="smAndDown ? 'small' : 'default'"
                    append-icon="mdi-plus" class="bold new-button">
                    NOVO
                </v-btn>
            </v-card-title>
            <v-divider :thickness="7" class="border-opacity-25 mb-4" color="green"></v-divider>
            <v-card-text>
                <v-row v-if="items.length" class="align-center">
                    <v-col cols="12">
                        <v-text-field v-model="search_field" :label="'Busca avançada em ' + items.length + ' registro(s)'" prepend-inner-icon="mdi-magnify"
                            density="compact" clearable color="green" />
                    </v-col>
                </v-row>
                <v-list v-if="smAndDown" style="background-color: rgba(0, 0, 0, 0);">
                    <v-alert v-if="!items.length || !paginated_items.length" icon="mdi-information" border="bottom"
                        class="mb-2" :color="dark_theme ? 'rgba(50, 50, 50, 0.6)' : 'rgba(250, 250, 250, 0.8)'">
                        {{ !items.length ? (loading ? 'Carregando, aguarde...' : 'Lista vazia, inclua um item clicando no botão "Novo" acima') : ('Não há dados para o filtro "' + search_field + '"') }}
                    </v-alert>
                    <v-list-item v-for="item in paginated_items" :key="item.id"
                        :class="dark_theme ? 'list-item-dark' : 'list-item'" @click="auth.user.level < 1 ? null : openEditDialog(item)">
                        <v-list-item-title :class="dark_theme ? 'text-shadow-black-2' : ''">
                            <v-row class="align-center">
                                <v-col :cols="auth.user.level > 1 && !item.field_records_count ? 9 : 12">
                                    <strong>{{ item.name }}</strong>
                                </v-col>
                                <v-col v-if="auth.user.level > 1 && !item.field_records_count" cols="3" class="align-end">
                                    <v-btn class="mx-1 hover-buttons" color="red" variant="elevated" icon :disabled="auth.user.level < 2 || item.field_records_count"
                                        size="28" @click.stop="openDeleteDialog(item)">
                                        <v-icon size="x-small">mdi-delete</v-icon>
                                    </v-btn>
                                </v-col>                                
                            </v-row>
                        </v-list-item-title>                        
                    </v-list-item>
                    <v-row class="align-center pt-3" v-if="smAndDown && !loading && paginated_items.length > 0">
                        <v-col cols="12">
                            <v-pagination v-model="current_page" :length="total_pages" :total-visible="5"
                                rounded="circle"></v-pagination>
                        </v-col>
                        <v-col cols="12" class="align-end">
                            <v-select label="Itens por página:" color="green" density="compact" :items="[5, 10, 15, 50, 'Todos']" style="max-width: 130px;"
                                variant="outlined" v-model="items_per_page"></v-select>
                        </v-col>
                    </v-row>
                </v-list>
                <v-data-table v-else class="mb-2 clickable-table" :headers="headers" :items="paginated_items" :loading="loading"
                    fixed-header no-data-text="Nenhum registro encontrado" loading-text="Carregando, aguarde...">
                    <template #item="{ item }">
                        <tr :class="dark_theme ? 'table-row' : 'table-row-light'" @click="auth.user.level < 1 ? null : openEditDialog(item)">
                            <td>
                                <span :class="dark_theme ? 'text-shadow-black-1' : ''">
                                    {{ item.name }}
                                </span>
                            </td>
                            <td>
                                <v-menu open-on-hover location="start">
                                    <template #activator="{ props }">
                                        <v-btn v-bind="props" variant="text" size="small" icon="mdi-dots-vertical"
                                            @click.stop />
                                    </template>
                                    <div class="my-1 ml-1">
                                        <v-btn class="mx-1 hover-buttons" color="orange" variant="elevated" icon :disabled="auth.user.level < 1"
                                            size="x-small" @click="openEditDialog(item)">
                                            <v-icon>mdi-pencil</v-icon>
                                        </v-btn>
                                        <v-btn class="mx-1 hover-buttons" color="red" variant="elevated" icon :disabled="auth.user.level < 2 || item.field_records_count"
                                            size="x-small" @click="openDeleteDialog(item)">
                                            <v-icon>mdi-delete</v-icon>
                                        </v-btn>
                                    </div>
                                </v-menu>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
                <DialogAddTractor @new_register="pushNewItem" @close="add_dialog = false" :icon="icon"
                    :model="add_dialog" color="rgb(90, 180, 80)" />
                <DialogEditTractor @edited_register="editItem" @close="edit_dialog = false" :icon="icon"
                    :data="edit_dialog_data" :model="edit_dialog" color="orange" />
                <DialogDelete @deleted="popItem" @close="delete_dialog = false" :icon="icon" :data="delete_dialog_data"
                    data_name="tractor" :model="delete_dialog" color="rgb(230, 60, 60)" />
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup>
// Imports
import api from '@/plugins/axios.js'
import { useAuthStore } from '@/stores/auth.js'
import { useSnackbarStore } from '@/stores/snackbar'
import { ref, computed, reactive, watch } from 'vue'
import { useTheme, useDisplay } from 'vuetify'
import DialogAddTractor from '@/components/dialogs/DialogAddTractor.vue'
import DialogEditTractor from '@/components/dialogs/DialogEditTractor.vue'
import DialogDelete from '@/components/dialogs/DialogDelete.vue'

// Variables
const props = defineProps({
    title: { type: String, required: true },
    icon: { type: String, required: true },
    color: { type: String, default: 'green' },
    active_tab: { type: Boolean, default: false }
})
const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
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
const searchable_fields = [
    {
        key: 'name'
    }
]

// Computeds
const filtered_items = computed(() => {
    current_page.value = 1
    if (!search_field.value || search_field.value.length === 0) {
        return items.value
    }
    const search = search_field.value.toLowerCase().trim()
    return items.value.filter(item => {
        return searchable_fields.some(field => {
            const raw_value = getNestedValue(item, field.key)
            if (raw_value === null || raw_value === undefined) {
                return false
            }
            if (field.map) {
                const mapped_value = field.map[raw_value]
                if (!mapped_value) return false
                return mapped_value
                    .toLowerCase()
                    .split(' ')
                    .some(word => word.startsWith(search))
            }
            return String(raw_value)
                .toLowerCase()
                .includes(search)
        })
    })
})

const paginated_items = computed(() => {
    if (items_per_page.value === 'Todos') {
        return filtered_items.value
    }
    const start = (current_page.value - 1) * items_per_page.value
    const end = start + items_per_page.value
    return filtered_items.value.slice(start, end)
})

const total_pages = computed(() => {
    if (items_per_page.value === 'Todos') {
        return 1
    }
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
function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, key) => acc?.[key], obj)
}

function getItems(attempt = 1) {
    loading.value = true
    api.get('get_tractors').then(response => {
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

function editItem(edited_item) {
    items.value.splice(
        items.value.findIndex(item => item.id == edited_item.id),
        1,
        edited_item
    )
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

function pushNewItem(item) {
    items.value.unshift(item)
}
</script>
