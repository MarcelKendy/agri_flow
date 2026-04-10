<template>
    <!-- eslint-disable -->
    <v-card :loading="loading_products">
        <template v-slot:loader="{ isActive }">
            <v-progress-linear :active="isActive" color="primary" height="7" indeterminate></v-progress-linear>
        </template>
        <v-toolbar dark prominent :class="dark_theme ? 'custom-toolbar-dark' : 'custom-toolbar-light'">
            <v-row align="center pr-3">
                <v-col cols="5" lg="3" align="center">
                    <v-text-field clearable variant="underlined" :disabled="loading_products" v-model="search" color="primary"
                        placeholder="Pesquisar...">
                        <template v-slot:prepend>
                            <v-icon color="primary">mdi-text-search</v-icon>
                        </template>
                        <template v-slot:append>
                            <v-tooltip :content-class="dark_theme ? 'tooltip-default-dark' : 'tooltip-default-light'"
                                text='Pesquise por qualquer campo. Em valores (R$), separe dezenas por "." e centavos por ",", e em datas, usar o formato "aaaa-mm-dd"'>
                                <template v-slot:activator="{ props }">
                                    <v-icon v-bind="props" size="xs-small" color="primary">mdi-information</v-icon>
                                </template>
                            </v-tooltip>
                        </template>
                    </v-text-field>
                </v-col>
                <v-col cols="2"></v-col>
                <v-col cols="5" lg="7" align="end">
                    <v-btn :disabled="level_disable || loading_products" class="add-button"
                        @click="add_product_dialog = true" color="white" size="small" elevation="10" dark
                        style="background-color: rgb(64, 184, 59)">
                        ADICIONAR<v-icon>mdi-plus</v-icon>
                    </v-btn>
                </v-col>
            </v-row>
        </v-toolbar>
        <v-data-table class="mb-2" :sort-by="[{ key: 'name', order: 'asc' }, { key: 'status', order: 'desc' }]" :headers="headers"
            :items="products" :search="search" multi-sort fixed-header :items-per-page-options="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'Tudo' }
            ]" items-per-page="10" items-per-page-text="Itens por página:"
            :no-data-text="loading_products ? 'Carregando...' : 'Nenhum registro encontrado'">
            <template v-slot:item.name="{ value }">
                <span :class="dark_theme? 'text-shadow-black-1': ''">{{ value }}</span>
            </template>
            <template v-slot:item.type="{ value }">
                <v-chip elevation="1"
                    :color="value == 0 ? 'primary' : (value == 1 ? 'green' : 'blue')"
                    :prepend-icon="value == 0 ? 'mdi-plus-circle-multiple' : (value == 1 ? 'mdi-cash-multiple' : 'mdi-numeric-9-plus-box-multiple')">{{
                        value == 0 ? 'Valor e Quantidade' : (value == 1 ? 'Valor' : 'Quantidade')
                    }}</v-chip>
            </template>
            <template v-slot:item.status="{ value, item }">
                <v-switch :loading="loading_status[item.id]" :disabled="loading_status[item.id]"
                    color="rgb(40,150,170)" v-model="item.status"
                    @change="editProductStatus(item.id, { status: Number(value) })"></v-switch>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-menu open-on-hover location="start">
                    <template v-slot:activator="{ props }">
                        <v-btn variant="text" size="small" icon="mdi-dots-vertical" v-bind="props"></v-btn>
                    </template>
                    <div class="my-1 ml-1">
                        <v-hover v-if="false">
                            <template v-slot:default="{ isHovering, props }">
                                <v-btn class="hover-buttons" color="red" :variant="isHovering ? 'outlined' : 'elevated'"
                                    v-bind="props" icon size="x-small" @click="openDeleteProductDialog(item)">
                                    <v-icon :color="isHovering ? 'red' : 'white'">mdi-delete</v-icon>
                                </v-btn>
                            </template>
                        </v-hover>
                        <v-hover>
                            <template v-slot:default="{ isHovering, props }">
                                <v-btn class="mx-1 hover-buttons" color="orange"
                                    :variant="isHovering ? 'outlined' : 'elevated'" v-bind="props" icon size="x-small"
                                    @click="openEditProductDialog(item)">
                                    <v-icon :color="isHovering ? 'orange' : 'white'">mdi-pencil</v-icon>
                                </v-btn>
                            </template>
                        </v-hover>
                    </div>
                </v-menu>
            </template>
        </v-data-table>
        <DialogAddProduct @new_register="pushNewProduct" @close="add_product_dialog = false" :model="add_product_dialog"
            color="rgb(90, 180, 80)" />
        <DialogEditProduct @edited_register="editProduct" @close="edit_product_dialog = false"
            :data="edit_product_dialog_data" :model="edit_product_dialog" color="orange" />
        <DialogDeleteProduct @deleted="popProduct" @close="delete_product_dialog = false" :data="delete_product_dialog_data"
            :model="delete_product_dialog" color="rgb(230, 60, 60)" />
    </v-card>
</template>
<script setup>
import api from '@/plugins/axios.js'
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import DialogAddProduct from '@/components/dialogs/DialogAddProduct.vue'
import DialogEditProduct from '@/components/dialogs/DialogEditProduct.vue'
import DialogDeleteProduct from '@/components/dialogs/DialogDeleteProduct.vue'
import { useTheme } from 'vuetify'
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')

// Variables
const loading_products = ref(false)
const products = ref([])
const add_product_dialog = ref(false)
const auth = useAuthStore()
const level_disable = auth.user.level < 1
const search = ref('')
const headers = reactive([
    { title: 'ID', key: 'id', width: '10%' },
    { title: 'Nome', key: 'name', width: '40%' },
    { title: 'Critério', key: 'type', width: '20%' },
    { title: 'Status', key: 'status', width: '20%' },
    { title: 'Ações', key: 'actions', sortable: false, width: '10%' }
])
const loading_status = reactive({})
const edit_product_dialog = ref(false)
const delete_product_dialog = ref(false)
const edit_product_dialog_data = reactive({})
const delete_product_dialog_data = reactive({})



// Created
getProducts()

// Methods
function getProducts(attempt = 1) {
    loading_products.value = true
    api.get('get_products', { params: { manage: true } }).then((response) => {
        products.value = response.data.map((item) => { return { ...item, status: !!item.status } })
        loading_products.value = false
    }).catch((error) => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getProducts(attempt + 1), 1000)
        } else {
            loading_products.value = false
        }    
    })
}

function editProduct(edited_product) {
    products.value.splice(products.value.findIndex(product => product.id == edited_product.id), 1, edited_product)
}

function popProduct(id) {
    products.value = products.value.filter((product) => product.id != id)
}

function openEditProductDialog(product) {
    Object.assign(edit_product_dialog_data, product)
    edit_product_dialog.value = true
}

function openDeleteProductDialog(product) {
    Object.assign(delete_product_dialog_data, product)
    delete_product_dialog.value = true
}

function editProductStatus(id, query) {
    loading_status[id] = true
    api.put('edit_product/' + id, query).then((response) => {
        loading_status[id] = false
    }).catch((error) => {
        console.log(error)
        loading_status[id] = false
    })
}

function pushNewProduct(product) {
    products.value.unshift(product)
}

</script>
<style scoped>

</style>