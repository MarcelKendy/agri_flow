<template>
    <div class="module-container">
        <v-card flat>
            <v-card-title class="text-h5 font-weight-bold mb-4" :class="dark_theme ? 'text-shadow-black-2' : ''">
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                <span class="mr-10">{{ title }}</span>
                <v-btn @click="add_crop_dialog = true" color="green" append-icon="mdi-plus" class="font-weight-bold"
                    style="border-radius: 7px; border: solid 1px rgba(255, 255, 255, 0.4)">
                    NOVO
                </v-btn>
            </v-card-title>
            <v-divider :thickness="7" class="border-opacity-25 mb-4" color="green"></v-divider>
            <v-card-text>
                <v-data-table class="mb-2" :sort-by="[{ key: 'id', order: 'asc' }, { key: 'name', order: 'asc' }]"
                    :headers="headers" :items="crops" :search="search" multi-sort fixed-header :items-per-page-options="[
                        { value: 10, title: '10' },
                        { value: 25, title: '25' },
                        { value: 50, title: '50' },
                        { value: 100, title: '100' },
                        { value: -1, title: 'Tudo' }
                    ]" items-per-page="10" items-per-page-text="Itens por página:"
                    :no-data-text="loading_crops ? 'Carregando...' : 'Nenhum registro encontrado'">
                    <template v-slot:item.name="{ value }">
                        <span :class="dark_theme ? 'text-shadow-black-1' : ''">{{ value }}</span>
                    </template>
                    <template v-slot:item.varieties="{ value }">
                        <v-chip-group column>
                            <v-chip v-for="variety in value.split(';')" :key="variety" size="small" variant="outlined">
                                {{ variety }}
                            </v-chip>
                        </v-chip-group>
                    </template>
                    <template v-slot:item.actions="{ item }">
                        <v-menu open-on-hover location="start">
                            <template v-slot:activator="{ props }">
                                <v-btn variant="text" size="small" icon="mdi-dots-vertical" v-bind="props"></v-btn>
                            </template>
                            <div class="my-1 ml-1">
                                <v-hover v-if="false">
                                    <template v-slot:default="{ isHovering, props }">
                                        <v-btn class="hover-buttons" color="red" :disabled="auth.user.level < 1"
                                            :variant="isHovering ? 'outlined' : 'elevated'" v-bind="props" icon
                                            size="x-small" @click="openDeleteCropDialog(item)">
                                            <v-icon :color="isHovering ? 'red' : 'white'">mdi-delete</v-icon>
                                        </v-btn>
                                    </template>
                                </v-hover>
                                <v-hover>
                                    <template v-slot:default="{ isHovering, props }">
                                        <v-btn class="mx-1 hover-buttons" color="orange" :disabled="auth.user.level < 1"
                                            :variant="isHovering ? 'outlined' : 'elevated'" v-bind="props" icon
                                            size="x-small" @click="openEditCropDialog(item)">
                                            <v-icon :color="isHovering ? 'orange' : 'white'">mdi-pencil</v-icon>
                                        </v-btn>
                                    </template>
                                </v-hover>
                            </div>
                        </v-menu>
                    </template>
                </v-data-table>
                <DialogAddCrop @new_register="pushNewCrop" @close="add_crop_dialog = false" :icon="icon"
                    :model="add_crop_dialog" color="rgb(90, 180, 80)" />
                <DialogEditCrop @edited_register="editCrop" @close="edit_crop_dialog = false" :icon="icon"
                    :data="edit_crop_dialog_data" :model="edit_crop_dialog" color="orange" />
                <DialogDeleteCrop @deleted="popCrop" @close="delete_crop_dialog = false" :icon="icon"
                    :data="delete_crop_dialog_data" :model="delete_crop_dialog" color="rgb(230, 60, 60)" />
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup>
// Imports
import api from '@/plugins/axios.js';
import { useAuthStore } from '@/stores/auth.js'
import { ref, computed, reactive } from 'vue'
import { useTheme } from 'vuetify'
import DialogAddCrop from '@/components/dialogs/DialogAddCrop.vue'
import DialogEditCrop from '@/components/dialogs/DialogEditCrop.vue'
import DialogDeleteCrop from '@/components/dialogs/DialogDeleteCrop.vue'

// Variables
const props = defineProps({
    title: { type: String, required: true },
    icon: { type: String, required: true },
    color: { type: String, default: 'green' },
})
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const auth = useAuthStore()
const crops = ref([])
const loading_crops = ref(false)
const search = ref('')
const headers = reactive([
    { title: 'ID', key: 'id', width: '10%' },
    { title: 'Nome', key: 'name', width: '30%' },
    { title: 'Variedades', key: 'varieties', width: '50%' },
    { title: 'Ações', key: 'actions', sortable: false, width: '10%' }
])
const add_crop_dialog = ref(false)
const edit_crop_dialog = ref(false)
const delete_crop_dialog = ref(false)
const edit_crop_dialog_data = reactive({})
const delete_crop_dialog_data = reactive({})

// Computeds

// Created
getCrops()

// Methods
function getCrops(attempt = 1) {
    loading_crops.value = true
    api.get('get_crops').then((response) => {
        crops.value = response.data
        loading_crops.value = false
    }).catch((error) => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getCrops(attempt + 1), 1000)
        } else {
            loading_crops.value = false
        }
    })
}

function editCrop(edited_crop) {
    crops.value.splice(crops.value.findIndex(crop => crop.id == edited_crop.id), 1, edited_crop)
}

function popCrop(id) {
    crops.value = crops.value.filter((crop) => crop.id != id)
}

function openEditCropDialog(crop) {
    Object.assign(edit_crop_dialog_data, crop)
    edit_crop_dialog.value = true
}

function openDeleteCropDialog(crop) {
    Object.assign(delete_crop_dialog_data, crop)
    delete_crop_dialog.value = true
}

function editCropStatus(id, query) {
    loading_status[id] = true
    api.put('edit_crop/' + id, query).then((response) => {
        loading_status[id] = false
    }).catch((error) => {
        console.log(error)
        loading_status[id] = false
    })
}

function pushNewCrop(crop) {
    crops.value.unshift(crop)
}

</script>

<style scoped>
.module-container {
    padding: 20px;
}

.content-section {
    padding: 10px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.05);
}
</style>