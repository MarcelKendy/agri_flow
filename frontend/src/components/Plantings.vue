<template>
    <div>
        <v-card flat style="background-color: rgba(0, 0, 0, 0)">
            <v-card-title class="text-h5 font-weight-bold" :class="dark_theme ? 'text-shadow-black-2' : ''"
                :style="smAndDown ? (dark_theme ? 'background-color: rgba(90, 90, 90, 0.2)' : 'background-color: rgba(150, 150, 150, 0.2)') : ''">
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                <span class="mr-10">{{ title }}</span>
                <v-btn @click="add_dialog = true" color="green" :size="smAndDown ? 'small' : 'default'"
                    append-icon="mdi-plus" class="font-weight-bold new-button">
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
                <v-alert v-if="!items.length || !paginated_items.length" icon="mdi-information" border="bottom"
                    class="mb-2" :color="dark_theme ? 'rgba(50, 50, 50, 0.6)' : 'rgba(250, 250, 250, 0.8)'">
                    {{
                        !items.length
                            ? 'Lista vazia, inclua um item clicando no botão "Novo" acima'
                            : ('Não há dados para o filtro "' + search_field + '"')
                    }}
                </v-alert>
                <v-row v-else>
                    <v-col v-for="item in paginated_items" :key="item.id" cols="12" md="6" :class="dark_theme ? 'text-shadow-black-2' : ''">
                        <v-card class="pa-3 items-card" :class="dark_theme ? 'list-item-dark' : 'list-item'"
                            @click="console.log(0)">
                            <div class="d-flex justify-space-between align-start mb-3">
                                <div style="font-size: 34px;">
                                    {{ getCropEmoji(item.crop?.name) }}
                                </div>
                                <div class="flex-grow-1 px-2">
                                    <div class="font-weight-bold" style="font-size: 16px;">
                                        {{ item.name.length > 40 ? item.name.substring(0, 40) + '...' : item.name }}
                                    </div>
                                    <div class="mt-1 d-flex flex-wrap ga-1">
                                        <v-chip size="x-small" :color="item.status == 1 ? 'green' : 'red'"
                                            variant="flat" style="text-shadow: none;">
                                            {{ item.status == 1 ? 'Ativo' : 'Inativo' }}
                                        </v-chip>
                                    </div>
                                </div>
                                <div class="text-center" style="height: 70px;">
                                    <template v-if="getDaysAfterPlanting(item.date) >= 0">
                                        <div class="font-weight-bold text-warning" style="font-size: 25px;">
                                            {{ getDaysAfterPlanting(item.date) }}
                                        </div>
                                        <div class="font-weight-light">DAP</div>
                                    </template>
                                    <template v-else>
                                        <div class="font-weight-bold text-green" style="font-size: 25px;">
                                            {{ Math.abs(getDaysAfterPlanting(item.date)) }}
                                        </div>
                                        <div class="font-weight-light" style="font-size: 12px;">dias até o plantio</div>
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
                                <v-btn prepend-icon="mdi-pencil" variant="flat" size="x-small" color="orange" @click.stop="openEditDialog(item)">Editar</v-btn>
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
const searchable_fields = [
    {
        key: 'name'
    },
    {
        key: 'variety'
    },
    {
        key: 'crop.name'
    },
    {
        key: 'pivot.name'
    },
    {
        key: 'date'
    },
    {
        key: 'size_ha'
    },
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

// Watchers
watch(items_per_page, () => {
    current_page.value = 1
})

// Methods
function formatDateBR(date) {
    if (!date) return 'Não definido'
    const [year, month, day] = date.split('-')
    return `${day}/${month}/${year}`
}

function getDaysAfterPlanting(date) {
    if (!date) return 0

    const planting = new Date(date + 'T00:00:00')
    const today = new Date()

    planting.setHours(0, 0, 0, 0)
    today.setHours(0, 0, 0, 0)

    const diff = today - planting
    return Math.floor(diff / 86400000)
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
        const regex = new RegExp(`\\b${key}\\b`, 'i')
        if (regex.test(crop)) {
            return dictionary[key]
        }
    }

    return '🌱'
}

function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, key) => acc?.[key], obj)
}

function getItems(attempt = 1) {
    loading.value = true
    api.get('get_plantings')
        .then(response => {
            items.value = response.data
            loading.value = false
        })
        .catch(error => {
            console.log(error)
            if (attempt <= 5) {
                setTimeout(() => getItems(attempt + 1), 1000)
            } else {
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
