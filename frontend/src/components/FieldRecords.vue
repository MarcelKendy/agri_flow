<template>
    <div>
        <v-card flat style="background-color:rgba(0,0,0,0)" :loading="loading">
            <template v-slot:loader="{ isActive }">
                <v-progress-linear :active="isActive" color="green" height="5" indeterminate></v-progress-linear>
            </template>
            <v-card-title class="text-h5 bold" :class="dark_theme ? 'text-shadow-black-2' : ''" :style="smAndDown ? (dark_theme ? 'background-color:rgba(90,90,90,0.2)' : 'background-color:rgba(150,150,150,0.2)') : ''"> 
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                <span class="mr-10">{{ title }}</span>
                <v-btn @click="add_dialog = true" color="green" :size="smAndDown ? 'x-small' : 'default'"
                    append-icon="mdi-plus" class="bold new-button">NOVO</v-btn>
            </v-card-title>

            <v-divider :thickness="7" class="border-opacity-25 mb-4" color="green" />

            <v-card-text>

                <v-row v-if="items_length">
                    <v-col cols="12" class="d-flex flex-wrap ga-2">
                        <v-btn @click="filter_active = !filter_active" color="teal" prepend-icon="mdi-filter" :disabled="loading || !items_length"
                            :append-icon="filter_active ? 'mdi-eye-off' : 'mdi-eye'" size="small">
                            {{ filter_active ? 'Esconder filtros' : 'Exibir filtros' }}
                        </v-btn>

                        <v-btn v-if="has_active_filters" @click="clearFilters" color="warning" size="small"
                            prepend-icon="mdi-filter-remove">
                            Limpar filtros
                        </v-btn>

                        <v-expand-transition>
                            <div v-if="items_length && filter_active"
                                :class="dark_theme ? 'filter-section-dark' : 'filter-section-light'" class="mt-3 w-100">
                                <div class="bold mb-5" style="font-size: 15px;">
                                    <v-icon>mdi-filter</v-icon>
                                    Filtros
                                </div>

                                <v-row>
                                    <v-col cols="12" md="6" lg="4">
                                        <v-select variant="solo" v-model="filters.crop" label="Cultura" :items="crops"
                                            item-title="name" item-value="name" multiple chips closable-chips clearable
                                            color="teal" prepend-icon="mdi-sprout" :loading="loading_crops" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select variant="solo" v-model="filters.planting" label="Plantio"
                                            :items="plantings" item-title="name" item-value="name" multiple chips
                                            closable-chips clearable color="teal" prepend-icon="mdi-leaf"
                                            :loading="loading_plantings" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select variant="solo" v-model="filters.status" label="Status"
                                            :items="['Pendente', 'Atrasado', 'Concluído']" multiple chips closable-chips
                                            clearable color="teal" prepend-icon="mdi-check-decagram" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select variant="solo" v-model="filters.service" label="Serviço"
                                            :items="services" multiple chips closable-chips clearable color="teal"
                                            prepend-icon="mdi-briefcase" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select variant="solo" v-model="filters.tractor" label="Trator"
                                            :items="tractors" item-title="name" item-value="name" multiple chips
                                            closable-chips clearable color="teal" prepend-icon="mdi-tractor"
                                            :loading="loading_tractors" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select variant="solo" v-model="filters.implement" label="Implemento"
                                            :items="implements_items" item-title="name" item-value="name" multiple chips
                                            closable-chips clearable color="teal" prepend-icon="mdi-hammer-wrench"
                                            :loading="loading_implements" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select variant="solo" v-model="filters.product" label="Produto"
                                            :items="products" item-title="name" item-value="name" multiple chips
                                            closable-chips clearable color="teal" prepend-icon="mdi-flask"
                                            :loading="loading_products" />
                                    </v-col>
                                </v-row>
                            </div>
                        </v-expand-transition>
                    </v-col>
                </v-row>

                <v-row v-if="items_length" class="align-center">
                    <v-col cols="12">
                        <v-text-field v-model="search_field"
                            :label="'Busca avançada em ' + filtered_items.length + ' registro(s)'"
                            prepend-icon="mdi-magnify" density="compact" clearable color="green" />
                    </v-col>
                </v-row>

                <v-alert v-if="!items_length || !paginated_items.length" icon="mdi-information" border="bottom"
                    class="mb-2" :color="dark_theme ? 'rgba(50, 50, 50, 0.6)' : 'rgba(250, 250, 250, 0.8)'">
                    {{ !items_length ? (loading ? 'Carregando, aguarde...' : 'Lista vazia, inclua um item clicando no botão "Novo" acima') : ('Não há dados para o filtro ' + (search_field.length ? ('"' + search_field + '"') : '')) }}
                </v-alert>

                <v-row v-else>
                    <v-col cols="12" lg="6" v-for="item in paginated_items" :key="item.id"
                        :class="dark_theme ? 'text-shadow-black-2' : ''">
                        <v-card class="pa-3 items-card" :class="dark_theme ? 'list-item-dark' : 'list-item'"
                            @click="auth.user.level < 1 ? null : openEditDialog(item)">
                            <v-row class="align-center">
                                <v-col cols="8" class="align-start">
                                    <div class="d-flex align-center">
                                        <v-icon :color="getServiceColor(item.service)" size="40" class="mr-2">
                                            {{ getServiceIcon(item.service) }}
                                        </v-icon>

                                        <div>
                                            <div class="bold" style="font-size:17px;">{{ item.service }}</div>

                                            <div class="d-flex align-center mt-1">
                                                <v-chip color="teal" prepend-icon="mdi-calendar" size="small">
                                                    {{ formatDate(item.date) }}
                                                </v-chip>
                                            </div>
                                        </div>
                                    </div>
                                </v-col>

                                <v-col cols="4" class="align-end">
                                    <v-chip size="small" variant="elevated" :color="getStatus(item).color"
                                        style="text-shadow:none;">
                                        {{ getStatus(item).label }}
                                    </v-chip>
                                </v-col>
                            </v-row>

                            <v-divider class="my-3" />

                            <v-row density="comfortable">
                                <v-col cols="12" md="4">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-sprout</v-icon>Plantio
                                        </div>
                                        <div class="dashboard-value">{{ item.planting?.name }}</div>
                                    </div>
                                </v-col>

                                <v-col cols="12" md="4">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-tractor</v-icon>Trator
                                        </div>
                                        <div class="dashboard-value">{{ item.tractor?.name || 'Não definido' }}</div>
                                    </div>
                                </v-col>

                                <v-col cols="12" md="4">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-hammer-wrench</v-icon>Implemento
                                        </div>
                                        <div class="dashboard-value">{{ item.implement?.name || 'Não definido' }}</div>
                                    </div>
                                </v-col>

                                <v-col cols="12" md="4">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-flask</v-icon>Produto
                                        </div>
                                        <div class="dashboard-value">{{ item.product?.name }}</div>
                                    </div>
                                </v-col>

                                <v-col cols="12" md="4">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14">mdi-scale-balance</v-icon>Dosagem
                                        </div>
                                        <div class="dashboard-value">{{ formatDosage(item) }}</div>
                                    </div>
                                </v-col>

                                <v-col cols="12" md="4">
                                    <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                        <div class="dashboard-label">
                                            <v-icon size="14" :color="getDeadlineInfo(item).color">
                                                {{ getDeadlineInfo(item).icon }}
                                            </v-icon>
                                            Prazo
                                        </div>

                                        <div class="dashboard-value" :class="'text-' + getDeadlineInfo(item).color">
                                            {{ getDeadlineInfo(item).text }}
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>

                            <div class="mt-3">
                                <v-btn v-if="item.notes" size="small" color="blue" variant="tonal"
                                    prepend-icon="mdi-note-text-outline"
                                    :append-icon="opened_notes.includes(item.id) ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                                    @click.stop="toggleNote(item.id)">
                                    Observações
                                </v-btn>

                                <v-expand-transition>
                                    <div v-show="opened_notes.includes(item.id)" class="mt-2">
                                        <v-alert type="info" variant="outlined" border="start" density="compact">
                                            {{ item.notes }}
                                        </v-alert>
                                    </div>
                                </v-expand-transition>
                            </div>

                            <v-row>
                                <v-col cols="9" class="align-start">
                                    <v-switch @click.stop :disabled="loading_status[item.id] || auth.user.level < 1"
                                        :loading="loading_status[item.id]" v-model="item.status"
                                        @change="editFieldRecordStatus(item.id, item.status)" label="Concluído"
                                        color="green"  
                                        style="margin-bottom: -35px !important; margin-right: 30px;"></v-switch>
                                </v-col>
                                <v-col cols="3" class="align-end">
                                    <v-tooltip text="Deletar Ficha" content-class="tooltip-red" location="left">
                                        <template v-slot:activator="{ props }">
                                            <v-btn v-bind="props" icon size="x-small" :disabled="auth.user.level < 2"
                                                color="red" @click.stop="openDeleteDialog(item)">
                                                <v-icon>mdi-delete</v-icon>
                                            </v-btn>
                                        </template>
                                    </v-tooltip>
                                </v-col>
                            </v-row>
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
                            :items="[5, 10, 15, 50, 'Todos']" style="max-width: 130px" variant="outlined"
                            v-model="items_per_page" />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <DialogAddFieldRecord @new_register="pushNewItem" @close="add_dialog = false" :icon="icon" :model="add_dialog"
            color="rgb(90,180,80)" />

        <DialogEditFieldRecord @edited_register="editItem" @close="edit_dialog = false" :icon="icon"
            :data="edit_dialog_data" :model="edit_dialog" color="orange" />

        <DialogDelete v-if="auth.user.level > 1" @deleted="popItem" @close="delete_dialog = false" :icon="icon"
            :info="[{ title: 'Serviço', key: 'service' }, { title: 'Plantio', key: 'planting.name' }]"
            :data="delete_dialog_data" data_name="field_record" :model="delete_dialog" color="rgb(230,60,60)" />
    </div>
</template>

<script setup>
// Imports
import api from '@/plugins/axios.js'
import { useAuthStore } from '@/stores/auth.js'
import { ref, computed, reactive, watch } from 'vue'
import { useTheme, useDisplay } from 'vuetify'
import DialogAddFieldRecord from '@/components/dialogs/DialogAddFieldRecord.vue'
import DialogEditFieldRecord from '@/components/dialogs/DialogEditFieldRecord.vue'
import DialogDelete from '@/components/dialogs/DialogDelete.vue'
import { useSnackbarStore } from '@/stores/snackbar'

// Variables
const props = defineProps({
    title: { type: String, required: true },
    icon: { type: String, required: true },
    color: { type: String, default: 'green' }
})

const snackbar = useSnackbarStore()
const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const auth = useAuthStore()

const items = ref([])
const crops = ref([])
const plantings = ref([])
const tractors = ref([])
const implements_items = ref([])
const products = ref([])

const loading = ref(false)
const loading_crops = ref(false)
const loading_plantings = ref(false)
const loading_tractors = ref(false)
const loading_implements = ref(false)
const loading_products = ref(false)
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
const opened_notes = ref([])

const services = ['Ferti Irrigação', 'Puverização', 'Adubação', 'Colheita', 'Plantio']

const filters = reactive({
    crop: [],
    planting: [],
    status: [],
    service: [],
    implement: [],
    product: [],
    tractor: []
})

const searchable_fields = [
    { key: 'service' },
    { key: 'date' },
    { key: 'notes' },
    { key: 'dosage' },
    { key: 'planting.name' },
    { key: 'tractor.name' },
    { key: 'implement.name' },
    { key: 'product.name' }
]

// Computeds
const has_active_filters = computed(() => {
    return Object.values(filters).some(value => value.length > 0)
})

const filtered_items = computed(() => {
    current_page.value = 1

    let data = [...items.value]

    if (filters.crop.length) data = data.filter(item => filters.crop.includes(item.planting?.crop?.name))
    if (filters.planting.length) data = data.filter(item => filters.planting.includes(item.planting?.name))
    if (filters.service.length) data = data.filter(item => filters.service.includes(item.service))
    if (filters.tractor.length) data = data.filter(item => filters.tractor.includes(item.tractor?.name))
    if (filters.implement.length) data = data.filter(item => filters.implement.includes(item.implement?.name))
    if (filters.product.length) data = data.filter(item => filters.product.includes(item.product?.name))
    if (filters.status.length) data = data.filter(item => filters.status.includes(getStatus(item).label))

    if (!search_field.value) return data

    const search = search_field.value.toLowerCase().trim()

    return data.filter(item => {
        return searchable_fields.some(field => {
            const raw_value = getNestedValue(item, field.key)
            if (raw_value === null || raw_value === undefined) return false
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

const items_length = computed(() => {
    return items.value.length
})

// Created
getItems()
getCrops()
getPlantings()
getTractors()
getImplements()
getProducts()

// Watchers
watch(items_per_page, () => {
    current_page.value = 1
})

watch(items_length, (value) => {
    if (value == 0) {
        clearFilters()
        filter_active.value = false
    }
})

// Methods
function clearFilters() {
    Object.keys(filters).forEach(key => {
        filters[key] = []
    })
}

function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, key) => acc?.[key], obj)
}

function editFieldRecordStatus(id, status) {
    loading_status[id] = true
    api.put('edit_field_record/' + id, { status: Number(status) }).then(response => {
        loading_status[id] = false
    }).catch(error => {
        console.log(error)
        const item = items.value.find(item => item.id === id)
        item.status = !item.status
        snackbar.open({ preset: 'error' })
        loading_status[id] = false
    })
}

function getItems(attempt = 1) {
    loading.value = true
    api.get('get_field_records').then(response => {
        items.value = response.data.map(item => ({
            ...item,
            status: Boolean(item.status)
        }))
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

function formatDate(date) {
    if (!date) return '-'
    const [year, month, day] = date.split('-')
    return `${day}/${month}/${year}`
}

function formatDosage(item) {
    const unit = item.product?.unit == 0 ? 'KG' : 'L'
    return `${Number(item.dosage).toFixed(2)} ${unit}`
}

function getStatus(item) {
    if (item.status == 1) return { label: 'Concluído', color: 'green' }

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const itemDate = new Date(item.date + 'T00:00:00')

    if (itemDate > today) return { label: 'Pendente', color: 'blue' }

    return { label: 'Atrasado', color: 'warning' }
}

function getServiceIcon(service) {
    const icons = {
        'Ferti Irrigação': 'mdi-water-sync',
        'Puverização': 'mdi-spray',
        'Adubação': 'mdi-shovel',
        'Colheita': 'mdi-food-apple',
        'Plantio': 'mdi-seed'
    }

    return icons[service] || 'mdi-tractor'
}

function getServiceColor(service) {
    const colors = {
        'Ferti Irrigação': 'blue',
        'Puverização': 'deep-orange',
        'Adubação': 'brown',
        'Colheita': 'green',
        'Plantio': 'teal'
    }

    return colors[service] || 'grey'
}

function getDeadlineInfo(item) {
    if (item.status == 1) {
        return { text: 'Concluído', color: 'green', icon: 'mdi-check-circle' }
    }

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const target_date = new Date(item.date + 'T00:00:00')
    const diff_days = Math.ceil((target_date.getTime() - today.getTime()) / 86400000)

    if (diff_days > 0) {
        return { text: 'Em ' + diff_days + ' dia(s)', color: 'blue', icon: 'mdi-clock-outline' }
    }

    if (diff_days === 0) {
        return { text: 'Hoje!', color: 'orange', icon: 'mdi-alert-circle' }
    }

    return {
        text: Math.abs(diff_days) + ' dia(s) de atraso',
        color: 'red',
        icon: 'mdi-calendar-alert'
    }
}

function toggleNote(id) {
    if (opened_notes.value.includes(id)) {
        opened_notes.value = opened_notes.value.filter(item => item != id)
    } else {
        opened_notes.value.push(id)
    }
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

function pushNewItem(item) {
    items.value.unshift(item)
}
</script>
