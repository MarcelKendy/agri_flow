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
                        <v-card
                            class="items-card"
                            :class="dark_theme ? 'list-item-dark' : 'list-item'" style="padding: 0;"                                                  
                        >
                            <div @click="toggleExpand(item.id)" style="padding: 20px;">
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
                                                <v-icon size="14">mdi-seed</v-icon>
                                                Variedade
                                            </div>

                                            <div class="dashboard-value">
                                                {{ item.variety || 'Não definido' }}
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

                                    <v-col cols="6">
                                        <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                            <div class="dashboard-label">
                                                <v-icon size="14">mdi-file-document-multiple</v-icon>
                                                Fichas - {{ item.field_records?.filter(r => r.id).length || 0 }}
                                            </div>

                                            <div class="dashboard-value">
                                                <v-chip
                                                    v-for="service in getGroupedServices(item.field_records)"
                                                    :key="service.name"
                                                    size="x-small"
                                                    class="mr-1 mb-1"
                                                    style="padding-left: 6px;"
                                                    variant="outlined"
                                                    :color="service.color"
                                                >
                                                    <template #prepend>
                                                        <v-avatar start size="18" :color="service.color">
                                                            {{ service.count }}
                                                        </v-avatar>
                                                    </template>
                                                    {{ service.name }}
                                                </v-chip>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12">
                                        <div
                                            :class="[
                                                dark_theme ? 'alerts-box-dark' : 'alerts-box-light',
                                                hasCriticalAlert(item) ? 'alerts-danger-border' : ''
                                            ]"
                                        >
                                            <!-- Header -->
                                            <div :class="dark_theme ? 'alerts-header-dark' : 'alerts-header-light'" :style="hasCriticalAlert(item) ? (dark_theme ? 'color: rgb(250, 100, 100)' : 'color: rgb(195, 10, 0)' ) : ''">
                                                <v-icon size="18" class="mr-2">{{ hasCriticalAlert(item) ? 'mdi-bell-alert' : (getAlerts(item).length ? 'mdi-bell-ring' : 'mdi-bell-off') }}</v-icon>
                                                <strong>Alertas {{ getAlerts(item).length ? ' - ' + getAlerts(item).length : '' }}</strong>
                                            </div>

                                            <!-- Rows -->
                                            <template v-if="getAlerts(item).length">
                                                <div v-for="(alert, index) in getAlerts(item)" :key="index">
                                                    <div class="alerts-row">                                                    

                                                        <v-icon :color="alert.color" size="18" class="mr-3">
                                                            {{ alert.icon }}
                                                        </v-icon>

                                                        <div class="flex-grow-1">
                                                            <div class="bold">{{ alert.title }}</div>
                                                            <div style="opacity: 0.7;">{{ alert.text }}</div>
                                                        </div>

                                                        <v-chip size="small" :color="alert.color || 'grey'" class="pr-5">
                                                            <template #prepend>
                                                                <v-avatar start size="20" style="border: solid 1px white;">
                                                                    <strong style="letter-spacing: 1px;" :style="dark_theme ? 'color: white' : 'color: black'">
                                                                        FC
                                                                    </strong>
                                                                </v-avatar>
                                                            </template>

                                                            {{ alert.id }}
                                                        </v-chip>
                                                    </div>

                                                    <v-divider
                                                        v-if="index < getAlerts(item).length - 1"
                                                        class="my-1"
                                                    />
                                                </div>
                                            </template>

                                            <div v-else class="pa-3" style="opacity: 0.8;">
                                                Nenhum alerta no momento
                                            </div>
                                        </div>
                                    </v-col>

                                </v-row>

                                <v-card-actions class="align-end">

                                    <v-btn prepend-icon="mdi-file-document-plus" variant="flat" size="x-small" color="green"
                                        @click.stop="add_field_record_dialog_planting_id = item.id; add_field_record_dialog = true">
                                        Nova Ficha
                                    </v-btn>

                                    <v-avatar :color="item.status == 0 ? 'green' : 'red'" style="text-shadow: none;"
                                        size="x-small" @click.stop="auth.user.level < 1 || loading_status[item.id]
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
                                                     

                                <v-expand-transition>
                                    <div
                                        v-if="expanded_items[item.id]"
                                    >
                                        <v-divider class="my-3"></v-divider>

                                        <div class="d-flex justify-space-between">
                                            <div class="mb-7 bold d-flex align-start ga-2" style="font-size: 16px;">
                                                <v-icon color="teal">mdi-timeline-clock</v-icon>
                                                Timeline de Serviços                                                                                                                               
                                            </div>
                                            <v-chip size="small" >
                                                Total: {{ item.field_records.length }}
                                            </v-chip>
                                        </div>
                                                                            
                                        <v-timeline
                                            density="compact"
                                            align="start"
                                            side="end"
                                            truncate-line="both"
                                        >
                                            <v-timeline-item
                                                v-for="record in getSortedFieldRecords(item.field_records)"
                                                :key="record.id"
                                                :dot-color="getServiceColor(record.service)"
                                                :icon="getServiceIcon(record.service)"
                                                fill-dot
                                            >
                                               <v-card
                                                    class="pa-3 mb-2 timeline-card"
                                                    :class="dark_theme ? 'alerts-box-dark' : 'alerts-box-light'"
                                                    :style="`
                                                        width: 100%;
                                                        border: 1px solid ${getColorHex(getServiceColor(record.service))};
                                                        cursor: pointer;
                                                    `"
                                                    @click.stop="openEditFieldRecord(record)"
                                                >
                                                    <div class="bold mb-2 d-flex justify-space-between">
                                                        <div>
                                                            <v-chip size="small" class="pl-2 mr-1 mb-2">                                                    
                                                                <template #prepend>
                                                                    <v-avatar start size="26" style="border: solid 1px white;">
                                                                        <strong style="letter-spacing: 1px;" :style="dark_theme ? 'color: white' : 'color: black'">
                                                                            FC
                                                                        </strong>
                                                                    </v-avatar>
                                                                </template>                    
                                                                {{ record.id }}                                
                                                            </v-chip>
                                                            <span class="align-center">{{ record.service }}</span>
                                                        </div>
                                                        
                                                        <v-chip
                                                            variant="outlined"
                                                            size="small"
                                                            :color="getFieldRecordDateColor(record)"
                                                        >
                                                            {{ formatDateBR(record.date) }}
                                                        </v-chip>
                                                    </div>
                                                    <v-divider class="mb-2"></v-divider>
                                                    <v-row density="compact">
                                                        <v-col cols="12" lg="6">
                                                            <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                                                <div class="dashboard-label">
                                                                    <v-icon size="14">mdi-tractor</v-icon>
                                                                    Trator
                                                                </div>

                                                                <div class="dashboard-value">
                                                                    {{ record.tractor?.name || 'Não definido' }}
                                                                </div>
                                                            </div>
                                                        </v-col>

                                                        <v-col cols="12" lg="6">
                                                            <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                                                <div class="dashboard-label">
                                                                    <v-icon size="14">mdi-hammer-wrench</v-icon>
                                                                    Implemento
                                                                </div>

                                                                <div class="dashboard-value">
                                                                    {{ record.implement?.name || 'Não definido' }}
                                                                </div>
                                                            </div>
                                                        </v-col>

                                                        <v-col cols="12">
                                                            <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                                                <div class="dashboard-label">
                                                                    <v-icon size="14">mdi-flask</v-icon>
                                                                    Produtos - Dose ha
                                                                </div>

                                                                <div class="dashboard-value">
                                                                    <div
                                                                        v-for="product_item in record.products"
                                                                        :key="product_item.id"
                                                                        class="d-flex align-center mb-1"
                                                                    >
                                                                        <span class="mr-2 flex-grow-1">
                                                                            {{ product_item.product?.name }}
                                                                        </span>

                                                                        <v-chip
                                                                            size="small"
                                                                            :color="product_item.product?.unit == 0 ? 'teal' : 'blue'"                                                                        
                                                                        >
                                                                            {{ Number(product_item.dosage).toFixed(2) }}
                                                                            <template #append> 
                                                                                <v-avatar v-if="!smAndDown" end>
                                                                                    {{ product_item.product?.unit == 0 ? 'KG' : 'L' }}
                                                                                </v-avatar>                                                                                                                                                                                                                                                                                                                                                                                                             
                                                                            </template>
                                                                            <template #prepend>                                                                               
                                                                                <v-avatar start v-if="smAndDown">
                                                                                    {{ product_item.product?.unit == 0 ? 'KG' : 'L' }}
                                                                                </v-avatar>
                                                                                <v-icon start v-else>
                                                                                    {{ product_item.product?.unit == 0
                                                                                        ? 'mdi-weight-kilogram'
                                                                                        : 'mdi-bottle-tonic' }}
                                                                                </v-icon>    
                                                                            </template>
                                                                        </v-chip>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </v-col>
                                                    </v-row>
                                                </v-card>
                                            </v-timeline-item>
                                        </v-timeline>
                                    </div>
                                </v-expand-transition>
                            </div>   
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

                <DialogAddFieldRecord @new_register="pushNewFieldRecord" @close="add_field_record_dialog = false" icon="mdi-file-document-multiple" :model="add_field_record_dialog"
                    color="rgb(90,180,80)" :planting_id="add_field_record_dialog_planting_id" />

                <DialogEditFieldRecord @edited_register="editFieldRecordItem" @close="edit_field_record_dialog = false" :planting_id="edit_field_record_dialog_planting_id" icon="mdi-file-document-multiple" :data="edit_field_record_dialog_data" :model="edit_field_record_dialog" color="orange"/>

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
import DialogAddFieldRecord from '@/components/dialogs/DialogAddFieldRecord.vue'
import DialogAddPlanting from '@/components/dialogs/DialogAddPlanting.vue'
import DialogEditFieldRecord from '@/components/dialogs/DialogEditFieldRecord.vue'
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
const expanded_items = reactive({})
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

const add_field_record_dialog = ref(false)
const edit_field_record_dialog = ref(false)
const add_field_record_dialog_planting_id = ref(0)
const edit_field_record_dialog_planting_id = ref(0)
const add_dialog = ref(false)
const edit_dialog = ref(false)
const delete_dialog = ref(false)

const edit_field_record_dialog_data = reactive({})
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
function getFieldRecordDateColor(record) {
    if (Number(record.status) === 1) return 'green'

    if (!record.date) return 'blue'

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const target = new Date(record.date + 'T00:00:00')
    target.setHours(0, 0, 0, 0)

    return target < today ? 'red' : 'blue'
}

function openEditFieldRecord(item) {
    Object.assign(edit_field_record_dialog_data, item)
    edit_field_record_dialog_planting_id.value = item.id 
    edit_field_record_dialog.value = true
}

function getColorHex(color) {
    const map = {
        red: '#f44336',
        blue: '#2196f3',
        teal: '#009688',
        green: '#4caf50',
        brown: '#795548',
        grey: '#9e9e9e',
        warning: '#fb8c00',
        info: '#2196f3',
        error: '#f44336',
        'orange-darken-3': '#ef6c00'
    }

    return map[color] || '#888'
}

function getSortedFieldRecords(records = []) {
    return [...records].sort((a, b) =>
        new Date(b.date) - new Date(a.date)
    )
}

function toggleExpand(id) {
    expanded_items[id] = !expanded_items[id]
}

function getAlerts(item) {
    const alerts = []

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const fieldRecords = item.field_records || []

    fieldRecords.forEach(record => {
        // only pending records
        if (Number(record.status) === 1) return
        if (!record.date || !record.service) return

        const target = new Date(record.date + 'T00:00:00')
        target.setHours(0, 0, 0, 0)

        const diff = Math.ceil((target - today) / 86400000)

        let alert = null

        if (diff < 0) {
            alert = {
                text: `Atrasado ${Math.abs(diff)} dia(s)`,
                color: 'error'
            }
        } else if (diff === 0) {
            alert = {
                text: 'Hoje!',
                color: 'warning'
            }
        } else if (diff <= 7) {
            alert = {
                text: `Em ${diff} dia(s)`,
                color: 'info'
            }
        }

        if (alert) {
            alert = {
                id: record.id,
                title: record.service,
                icon: getServiceIcon(record.service),
                sort: diff,
                ...alert
            }
        }

        if (alert) alerts.push(alert)
    })

    // inactivity always on top
    if (item.status == 1 && item.date) {
        const plantingDate = new Date(item.date + 'T00:00:00')
        plantingDate.setHours(0, 0, 0, 0)

        const plantingDays = Math.floor((today - plantingDate) / 86400000)

        // só considera se a data do plantio já chegou
        if (plantingDays >= 0) {
            const validRecords = fieldRecords
                .filter(record => record.date)
                .map(record => ({
                    ...record,
                    parsedDate: new Date(record.date + 'T00:00:00')
                }))

            let referenceDate = plantingDate
            let lastService = 'Plantio'
            let referenceId = item.id

            // se existir ficha, pega a mais recente
            if (validRecords.length) {
                const latest = validRecords.sort(
                    (a, b) => b.parsedDate - a.parsedDate
                )[0]

                latest.parsedDate.setHours(0, 0, 0, 0)

                referenceDate = latest.parsedDate
                lastService = latest.service || 'Serviço'
                referenceId = latest.id
            }

            const inactiveDays = Math.floor(
                (today - referenceDate) / 86400000
            )

            // ALERTA CRÍTICO (15+)
            if (inactiveDays >= 15) {
                alerts.unshift({
                    id: referenceId,
                    title: 'Inatividade',
                    text: `Inativo por ${inactiveDays} dia(s)`,
                    icon: 'mdi-calendar-alert',
                    color: 'orange',
                    fixedTop: true
                })
            }

            // PRÉ-ALERTA (10 a 14)
            else if (inactiveDays >= 10) {
                alerts.unshift({
                    id: referenceId,
                    title: 'Inatividade',
                    text: `Última atividade há ${inactiveDays} dia(s) - ${lastService}`,
                    icon: 'mdi-calendar',
                    color: '',
                    fixedTop: true
                })
            }
        }
    }

    return alerts.sort((a, b) => {
        if (a.fixedTop) return -1
        if (b.fixedTop) return 1
        return (a.sort ?? 9999) - (b.sort ?? 9999)
    })
}

function hasCriticalAlert(item) {
    return getAlerts(item).some(alert =>
        alert.text.includes('Atrasado') ||
        alert.text.includes('Inativo')
    )
}

function getServiceIcon(service) {
    const icons = {
        'Ferti Irrigação': 'mdi-water-sync',
        'Pulverização': 'mdi-spray',
        'Adubação': 'mdi-shovel',
        'Colheita': 'mdi-food-apple',
        'Plantio': 'mdi-seed'
    }

    return icons[service] || 'mdi-bell'
}

function getGroupedServices(field_records = []) {
    const grouped = {}

    field_records.forEach(record => {
        const name = record.service

        if (!name) return

        if (!grouped[name]) {
            grouped[name] = {
                name,
                count: 0,
                color: getServiceColor(name)
            }
        }

        grouped[name].count++
    })

    return Object.values(grouped)
}

function getServiceColor(service) {
    const colors = {
        'Ferti Irrigação': 'blue',
        'Pulverização': 'teal',
        'Adubação': 'brown',
        'Colheita': 'green',
        'Plantio': 'orange-darken-3'
    }

    return colors[service] || 'grey'
}

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
    items.value.splice(items.value.findIndex(item => item.id == edited_item.id), 1, edited_item)
}

function editFieldRecordItem(edited_item) {
    const planting = items.value.find(
        item => item.id == edited_item.planting_id
    )

    if (!planting?.field_records) return

    const index = planting.field_records.findIndex(
        record => record.id == edited_item.id
    )

    if (index !== -1) {
        planting.field_records.splice(index, 1, edited_item)
    }
}

function popItem(id) {
    items.value = items.value.filter(item => item.id != id)
}

function pushNewFieldRecord(item) {
    const planting = items.value.find(
        planting => planting.id == item.planting_id
    )
    if (!planting) return
    if (!planting.field_records) {
        planting.field_records = []
    }
    planting.field_records.unshift(item)
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

<style scoped>
.alerts-box-dark {
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255,255,255,0.02);
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
}

.alerts-box-light {
    border: 1px solid rgba(0, 0, 0, 0.315);
    border-radius: 12px;
    overflow: hidden;
    background: rgba(0,0,0,0.015);
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
}

.alerts-header-dark {
    background: rgba(0,0,0,0.35);
    padding: 10px 12px;
    display: flex;
    align-items: center;
}

.alerts-header-light {
    background: rgba(0,0,0,0.08);
    padding: 10px 12px;
    display: flex;
    align-items: center;
}

.alerts-row {
    display: flex;
    align-items: center;
    padding: 10px 12px;
}

.alerts-danger-border {
    border: 1px solid rgba(255, 70, 70, 0.9) !important;
    box-shadow: rgba(87, 16, 16, 0.459) 0px 0px 20px 2px;
}

.timeline-card {
    transition: all .18s ease;
}

.timeline-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(0,0,0,.28);
}
</style>