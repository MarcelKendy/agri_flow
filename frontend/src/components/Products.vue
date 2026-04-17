<template>
    <div>
        <v-card flat>
            <v-card-title class="text-h5 font-weight-bold mb-4" :class="dark_theme ? 'text-shadow-black-2' : ''">
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                <span class="mr-10">{{ title }}</span>
                <v-btn @click="add_dialog = true" color="green" :size="smAndDown ? 'small' : 'default'"
                    append-icon="mdi-plus" class="font-weight-bold new-button">
                    NOVO
                </v-btn>
            </v-card-title>
            <v-divider :thickness="7" class="border-opacity-25 mb-4" color="green"></v-divider>
            <v-card-text>
                <v-list v-if="smAndDown">
                    <v-list-item v-for="item in items" :class="dark_theme ? 'list-item-dark' : 'list-item'">
                        <v-list-item-title :class="dark_theme ? 'text-shadow-black-2' : ''">
                            <v-row class="align-center">
                                <v-col cols="9">
                                    <strong>{{ item.name }}</strong>
                                </v-col>
                                <v-col cols="3" class="">
                                    <v-chip size="small" :color="item.unit == 0 ? 'teal' : 'blue'">{{ item.unit == 0 ? 'KG' : 'L'
                                    }}</v-chip>
                                </v-col>
                            </v-row>
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            {{ item.category }}
                        </v-list-item-subtitle>
                    </v-list-item>
                </v-list>
                <v-data-table v-else class="mb-2 clickable-table" :sort-by="[
                    // { key: 'id', order: 'asc' },
                    // { key: 'name', order: 'asc' }
                ]" :headers="headers" :items="items" :search="search" multi-sort fixed-header :items-per-page-options="[
                    { value: 10, title: '10' },
                    { value: 25, title: '25' },
                    { value: 50, title: '50' },
                    { value: 100, title: '100' },
                    { value: -1, title: 'Tudo' }
                ]" items-per-page="25" items-per-page-text="Itens por página:"
                    :no-data-text="loading ? 'Carregando...' : 'Nenhum registro encontrado'">
                    <template #item="{ item }">
                        <tr :class="dark_theme ? 'table-row' : 'table-row-light'" @click="openEditDialog(item)">
                            <td>
                                <span :class="dark_theme ? 'text-shadow-black-1' : ''">
                                    {{ item.name }}
                                </span>
                            </td>
                            <td v-if="!smAndDown && item.category">
                                <span :class="dark_theme ? 'text-shadow-black-1' : ''">
                                    {{ item.category }}
                                </span>
                            </td>
                            <td>
                                <v-chip :color="item.unit == 0 ? 'teal' : 'blue'">{{ item.unit == 0 ? 'KG' : 'L' }}</v-chip>
                            </td>
                            <td v-if="!smAndDown">
                                <v-menu open-on-hover location="start">
                                    <template #activator="{ props }">
                                        <v-btn v-bind="props" variant="text" size="small" icon="mdi-dots-vertical"
                                            @click.stop />
                                    </template>
                                    <div class="my-1 ml-1">
                                        <v-hover v-if="false">
                                            <template v-slot:default="{ isHovering, props }">
                                                <v-btn class="hover-buttons" color="red" :disabled="auth.user.level < 1"
                                                    :variant="isHovering ? 'outlined' : 'elevated'" v-bind="props" icon
                                                    size="x-small" @click="openDeleteDialog(item)">
                                                    <v-icon :color="isHovering ? 'red' : 'white'">mdi-delete</v-icon>
                                                </v-btn>
                                            </template>
                                        </v-hover>
                                        <v-hover>
                                            <template v-slot:default="{ isHovering, props }">
                                                <v-btn class="mx-1 hover-buttons" color="orange"
                                                    :disabled="auth.user.level < 1"
                                                    :variant="isHovering ? 'outlined' : 'elevated'" v-bind="props" icon
                                                    size="x-small" @click="openEditDialog(item)">
                                                    <v-icon :color="isHovering ? 'orange' : 'white'">mdi-pencil</v-icon>
                                                </v-btn>
                                            </template>
                                        </v-hover>
                                    </div>
                                </v-menu>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
                <DialogAddProduct @new_register="pushNewItem" @close="add_dialog = false" :icon="icon"
                    :model="add_dialog" color="rgb(90, 180, 80)" />
                <DialogEditProduct @edited_register="editItem" @close="edit_dialog = false" :icon="icon"
                    :data="edit_dialog_data" :model="edit_dialog" color="orange" />
                <DialogDelete @deleted="popItem" @close="delete_dialog = false" :icon="icon" :data="delete_dialog_data"
                    data_name="product" :model="delete_dialog" color="rgb(230, 60, 60)" />
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup>
// Imports
import api from '@/plugins/axios.js';
import { useAuthStore } from '@/stores/auth.js'
import { ref, computed, reactive } from 'vue'
import { useTheme, useDisplay } from 'vuetify'
import DialogAddProduct from '@/components/dialogs/DialogAddProduct.vue'
import DialogEditProduct from '@/components/dialogs/DialogEditProduct.vue'
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
const search = ref('')
const add_dialog = ref(false)
const edit_dialog = ref(false)
const delete_dialog = ref(false)
const edit_dialog_data = reactive({})
const delete_dialog_data = reactive({})

// Computeds
const headers = computed(() => {
    if (smAndDown.value) {
        return [
            { title: 'Nome', key: 'name', width: '80%' },
            { title: 'Unidade', key: 'unit', width: '20%' },
        ]
    }
    return [
        { title: 'Nome', key: 'name', width: '50%' },
        { title: 'Categoria', key: 'category', width: '30%' },
        { title: 'Unidade', key: 'unit', width: '10%' },
        { title: 'Ações', key: 'actions', sortable: false, width: '10%' }
    ]
})

// Created
getItems()

// Methods
function getItems(attempt = 1) {
    loading.value = true
    api.get('get_products').then((response) => {
        items.value = response.data
        loading.value = false
    }).catch((error) => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getItems(attempt + 1), 1000)
        } else {
            loading.value = false
        }
    })
}

function editItem(edited_item) {
    items.value.splice(items.value.findIndex(item => item.id == edited_item.id), 1, edited_item)
}

function popItem(id) {
    items.value = items.value.filter((item) => item.id != id)
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

<style scoped>
.content-section {
    padding: 10px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.05);
}
</style>