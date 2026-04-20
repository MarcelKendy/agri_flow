<template>
    <div>
        <v-card flat style="background-color: rgba(0, 0, 0, 0)">
            <v-card-title style="position: relative;" class="bold" :class="dark_theme ? 'text-shadow-black-2' : ''"
                :style="smAndDown
                    ? (dark_theme
                        ? 'background-color: rgba(90, 90, 90, 0.2)'
                        : 'background-color: rgba(150, 150, 150, 0.2)')
                    : ''">
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                <span class="mr-10">{{ title }}</span>

                <v-btn @click="add_dialog = true" color="green" :size="smAndDown ? 'small' : 'default'"
                    append-icon="mdi-plus" class="bold new-button">
                    NOVO
                </v-btn>
            </v-card-title>

            <v-divider :thickness="7" class="border-opacity-25 mb-4" color="green"></v-divider>

            <v-card-text>
                <v-row v-if="items_length" class="align-center">
                    <v-col cols="12" class="d-flex ga-2">
                        <v-btn @click="filter_active = !filter_active" color="teal" prepend-icon="mdi-filter" :disabled="loading || !items_length"
                            :append-icon="filter_active ? 'mdi-eye-off' : 'mdi-eye'" size="small">
                            {{ filter_active ? 'Esconder filtros' : 'Exibir filtros' }}
                        </v-btn>
                        <v-btn v-if="has_active_filters" @click="clearFilters" color="warning" size="small"
                            prepend-icon="mdi-filter-remove">
                            Limpar filtros
                        </v-btn>
                    </v-col>
                    <v-expand-transition>
                        <v-col cols="12" v-if="items_length && filter_active">                        
                            <div
                                :class="dark_theme ? 'filter-section-dark' : 'filter-section-light'">
                                <div class="bold mb-5" style="font-size: 15px;">
                                    <v-icon>mdi-filter</v-icon>
                                    Filtros
                                </div>

                                <v-row>
                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.crop" label="Cultura" :items="crops" multiple chips item-title="name" item-value="id"
                                            closable-chips clearable variant="solo" color="teal" :loading="loading_crops" :disabled="loading_crops"
                                            no-data-text="Nenhum dado cadastrado..." prepend-icon="mdi-seed" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.variety" label="Variedade" :items="varieties"
                                            multiple chips closable-chips clearable variant="solo" color="teal" :loading="loading_crops" :disabled="loading_crops"
                                            no-data-text="Nenhum dado cadastrado..." prepend-icon="mdi-flower-tulip"/>
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.pivot" label="Pivô" :items="pivots" multiple chips :loading="loading_pivots" :disabled="loading_pivots"
                                            closable-chips clearable variant="solo" color="teal"  prepend-icon="mdi-water-pump" item-title="name" item-value="id"
                                            no-data-text="Nenhum dado cadastrado..." />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.status" label="Status" :items="['Ativo', 'Inativo']"
                                            multiple chips closable-chips clearable variant="solo" color="teal" prepend-icon="mdi-list-status" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.date" label="Data"
                                            :items="['Antigos', 'Hoje!', 'Futuros']" multiple chips closable-chips
                                            clearable variant="solo" color="teal" prepend-icon="mdi-calendar" />
                                    </v-col>
                                </v-row>
                            </div>                        
                        </v-col>
                    </v-expand-transition>    
                </v-row>

                <v-row v-if="items_length" class="align-center">
                    <v-col cols="12">
                        <v-text-field v-model="search_field"
                            :label="'Busca avançada em ' + filtered_items.length + ' registro(s)'"
                            prepend-icon="mdi-magnify" density="compact" clearable color="green" />
                    </v-col>
                </v-row>

                <v-row>
                    <v-alert v-if="!items_length || !paginated_items.length" icon="mdi-information" border="bottom"
                        class="mb-2" :color="dark_theme ? 'rgba(50, 50, 50, 0.6)' : 'rgba(250, 250, 250, 0.8)'">
                        {{ !items_length ? (loading ? 'Carregando, aguarde...' : 'Lista vazia, inclua um item clicando no botão "Novo" acima') : ('Não há dados para o filtro ' + (search_field.length ? ('"' + search_field + '"') : '')) }}
                    </v-alert>
                    <v-col v-else v-for="item in paginated_items" :key="item.id" cols="12" md="6"
                        :class="dark_theme ? 'text-shadow-black-2' : ''">
                        <v-card class="pa-3 items-card" :class="dark_theme ? 'list-item-dark' : 'list-item'">
                            <div class="d-flex justify-space-between align-start mb-3">
                                <div style="font-size: 34px;">
                                    {{ getCropEmoji(item.crop?.name) }}
                                </div>

                                <div class="flex-grow-1 px-2">
                                    <div class="bold" style="font-size: 16px;">
                                        {{
                                            item.name.length > 40
                                                ? item.name.substring(0, 40) + '...'
                                                : item.name
                                        }}
                                    </div>

                                    <div class="mt-1 d-flex flex-wrap ga-1">
                                        <v-chip size="x-small" :color="item.status == 1 ? 'green' : 'red'"
                                            variant="elevated" style="text-shadow: none;">
                                            {{ item.status == 1 ? 'Ativo' : 'Inativo' }}
                                        </v-chip>
                                    </div>
                                </div>

                                <div class="text-center" style="height: 70px;">
                                    <template v-if="getDaysAfterPlanting(item.date) > 0">
                                        <div class="bold text-warning" style="font-size: 25px;">
                                            {{ getDaysAfterPlanting(item.date) }}
                                        </div>
                                        <div class="font-weight-light">DAP</div>
                                    </template>

                                    <template v-else-if="getDaysAfterPlanting(item.date) == 0">
                                        <div class="bold" style="font-size: 22px;">
                                            <v-chip color="info" size="large">Hoje!</v-chip>  
                                        </div>
                                    </template>

                                    <template v-else>
                                        <div class="bold text-green" style="font-size: 25px;">
                                            {{ Math.abs(getDaysAfterPlanting(item.date)) }}
                                        </div>
                                        <div class="font-weight-light" style="font-size: 12px;">
                                            dias até o plantio
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <v-divider class="mb-3"></v-divider>

                            <v-row density="comfortable">
                                <v-col cols="6">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-sprout</v-icon>
                                            Cultura
                                        </div>

                                        <div class="dashboard-value">
                                            {{ item.crop?.name || 'Não definido' }}
                                        </div>
                                    </div>
                                </v-col>

                                <v-col cols="6">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-water-pump</v-icon>
                                            Pivô
                                        </div>

                                        <div class="dashboard-value">
                                            {{ item.pivot?.name || 'Não definido' }}
                                        </div>
                                    </div>
                                </v-col>

                                <v-col cols="6">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-ruler-square</v-icon>
                                            Área
                                        </div>

                                        <div class="dashboard-value">
                                            {{ item.size_ha ? item.size_ha + ' ha' : 'Não definido' }}
                                        </div>
                                    </div>
                                </v-col>

                                <v-col cols="6">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-calendar</v-icon>
                                            Plantio
                                        </div>

                                        <div class="dashboard-value">
                                            {{ formatDateBR(item.date) }}
                                        </div>
                                    </div>
                                </v-col>

                                <v-col cols="12">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-seed</v-icon>
                                            Variedade
                                        </div>

                                        <div class="dashboard-value">
                                            {{ item.variety || 'Não definido' }}
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-card-actions class="align-end">
                                <v-avatar :color="item.status == 0 ? 'green' : 'red'" style="text-shadow: none;"
                                    size="x-small" @click="auth.user.level < 1 || loading_status[item.id]
                                        ? null
                                        : editPlanting(item.id, { status: item.status == 1 ? 0 : 1 })">
                                    <v-progress-circular v-if="loading_status[item.id]" indeterminate size="15"
                                        width="3" />

                                    <v-icon size="small" v-else>
                                        {{ item.status == 1 ? 'mdi-pause' : 'mdi-play' }}
                                    </v-icon>
                                </v-avatar>

                                <v-btn prepend-icon="mdi-pencil" variant="flat" size="x-small" color="orange"
                                    @click.stop="openEditDialog(item)">
                                    Editar
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>

                <v-row class="align-center pt-3" v-if="!loading && paginated_items.length > 0">
                    <v-col cols="12">
                        <v-pagination v-model="current_page" :length="total_pages" :total-visible="5"
                            rounded="circle" />
                    </v-col>

                    <v-col cols="12" class="align-end">
                        <v-select label="Itens por página:" color="green" density="compact"
                            :items="[5, 10, 15, 50, 'Todos']" style="max-width: 130px;" variant="outlined"
                            v-model="items_per_page" />
                    </v-col>
                </v-row>

                <DialogAddPlanting @new_register="pushNewItem" @close="add_dialog = false" :icon="icon"
                    :model="add_dialog" color="rgb(90, 180, 80)" />

                <DialogEditPlanting @edited_register="editItem" @close="edit_dialog = false" :icon="icon"
                    :data="edit_dialog_data" :model="edit_dialog" color="orange" />

                <DialogDelete @deleted="popItem" @close="delete_dialog = false" :icon="icon" :data="delete_dialog_data"
                    data_name="planting" :model="delete_dialog" color="rgb(230, 60, 60)" />
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
import DialogAddPlanting from '@/components/dialogs/DialogAddPlanting.vue'
import DialogEditPlanting from '@/components/dialogs/DialogEditPlanting.vue'
import DialogDelete from '@/components/dialogs/DialogDelete.vue'

// Variables
const props = defineProps({
    title: { type: String, required: true },
    icon: { type: String, required: true },
    color: { type: String, default: 'green' },
})


const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const auth = useAuthStore()
const snackbar = useSnackbarStore()

const items = ref([])
const crops = ref([])
const pivots = ref([])
const varieties = ref([])

const loading = ref(false)
const loading_crops = ref(false)
const loading_pivots = ref(false)
const loading_status = reactive({})

const search_field = ref('')
const current_page = ref(1)
const items_per_page = ref(10)

const filter_active = ref(false)

const add_dialog = ref(false)
const edit_dialog = ref(false)
const delete_dialog = ref(false)

const edit_dialog_data = reactive({})
const delete_dialog_data = reactive({})

const filters = reactive({
    crop: [],
    variety: [],
    pivot: [],
    status: [],
    date: []
})

const searchable_fields = [
    { key: 'name' },
    { key: 'variety' },
    { key: 'crop.name' },
    { key: 'pivot.name' },
    { key: 'date' },
    { key: 'size_ha' },
    {
        key: 'status',
        map: {
            '0': 'inativo inativa pausado pausada',
            '1': 'ativo ativa'
        }
    }
]

// Created
getItems()
getCrops()
getPivots()

// Computeds
const items_length = computed(() => items.value.length)

const has_active_filters = computed(() => {
    return Object.values(filters).some(value => value.length > 0)
})

const filtered_items = computed(() => {
    current_page.value = 1

    let data = [...items.value]

    if (filters.crop.length) data = data.filter(item => filters.crop.includes(item.crop?.id))
    if (filters.variety.length) data = data.filter(item => filters.variety.includes(item.variety))
    if (filters.pivot.length) data = data.filter(item => filters.pivot.includes(item.pivot?.id))
    if (filters.status.length) data = data.filter(item => filters.status.includes(item.status == 1 ? 'Ativo' : 'Inativo'))
    if (filters.date.length) data = data.filter(item => filters.date.includes(getDateType(item.date)))
    
    if (!search_field.value) return data

    const search = search_field.value.toLowerCase().trim()

    return data.filter(item => {
        return searchable_fields.some(field => {
            const raw_value = getNestedValue(item, field.key)

            if (raw_value === null || raw_value === undefined) return false

            if (field.map) {
                const mapped_value = field.map[raw_value]
                if (!mapped_value) return false

                return mapped_value
                    .toLowerCase()
                    .split(' ')
                    .some(word => word.startsWith(search))
            }

            return String(raw_value).toLowerCase().includes(search)
        })
    })
})

const paginated_items = computed(() => {
    if (items_per_page.value === 'Todos') return filtered_items.value

    const start = (current_page.value - 1) * items_per_page.value
    const end = start + items_per_page.value

    return filtered_items.value.slice(start, end)
})

const total_pages = computed(() => {
    if (items_per_page.value === 'Todos') return 1
    return Math.ceil(filtered_items.value.length / items_per_page.value)
})

// Watchers
watch(items_per_page, () => {
    current_page.value = 1
})

watch(items_length, value => {
    if (!value) {
        clearFilters()
        filter_active.value = false
    }
})

// Methods
function getItems(attempt = 1) {
    loading.value = true
    api.get('get_plantings').then(response => {
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

function getCrops(attempt = 1) {
    loading_crops.value = true
    api.get('get_crops').then(response => {
        crops.value = response.data
        const all_varieties = []
        crops.value.map(crop => {
            if (crop.varieties?.length) {
                all_varieties.push(...crop.varieties.split(';'))
            }
        })
        varieties.value = [...new Set(all_varieties)]
        loading_crops.value = false
    }).catch(error => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getCrops(attempt + 1), 1000)
        } else {
            snackbar.open({ preset: 'error' })
            loading_crops.value = false
        }
    })
}

function getPivots(attempt = 1) {
    loading_pivots.value = true
    api.get('get_pivots').then(response => {
        pivots.value = response.data
        loading_pivots.value = false
    }).catch(error => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getPivots(attempt + 1), 1000)
        } else {
            snackbar.open({ preset: 'error' })
            loading_pivots.value = false
        }
    })
}

function clearFilters() {
    Object.keys(filters).forEach(key => {
        filters[key] = []
    })
}

function editPlanting(id, values) {
    loading_status[id] = true
    api.put('edit_planting/' + id, values).then(response => {
        items.value.find(item => item.id == id).status = response.data.status
        loading_status[id] = false
    }).catch(error => {
        console.log(error)
        snackbar.open({ preset: 'error' })
        loading_status[id] = false
    })
}

function formatDateBR(date) {
    if (!date) return 'Não definido'

    const [year, month, day] = date.split('-')
    return `${day}/${month}/${year}`
}

function getDateType(date) {
    if (!date) return 'Antigos'

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const target = new Date(date + 'T00:00:00')

    if (target < today) return 'Antigos'
    if (target > today) return 'Futuros'

    return 'Hoje!'
}

function getDaysAfterPlanting(date) {
    if (!date) return 0

    const planting = new Date(date + 'T00:00:00')
    const today = new Date()

    planting.setHours(0, 0, 0, 0)
    today.setHours(0, 0, 0, 0)

    return Math.floor((today - planting) / 86400000)
}

function getCropEmoji(name) {
    if (!name) return '🌱'

    const crop = name.toLowerCase()

    const dictionary = {
        alho: '🧄',
        cenoura: '🥕',
        milho: '🌽',
        soja: '🫘',
        batata: '🥔',
        abacate: '🥑',
        tomate: '🍅',
        cebola: '🧅',
        cana: '🎋',
        café: '☕',
        cafe: '☕',
        trigo: '🌾',
        feijão: '🫘',
        feijao: '🫘',
        brachiaria: '🌿',
        braquiaria: '🌿'
    }

    for (const key in dictionary) {
        if (new RegExp(`\\b${key}\\b`, 'i').test(crop)) {
            return dictionary[key]
        }
    }

    return '🌱'
}

function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, key) => acc?.[key], obj)
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

function pushNewItem(item) {
    items.value.unshift(item)
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