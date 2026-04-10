<template>
    <!-- eslint-disable -->
    <v-card :loading="loading_groups">
        <template v-slot:loader="{ isActive }">
            <v-progress-linear :active="isActive" color="primary" height="7" indeterminate></v-progress-linear>
        </template>
        <v-toolbar dark prominent :class="dark_theme ? 'custom-toolbar-dark' : 'custom-toolbar-light'">
            <v-row align="center pr-3">
                <v-col cols="5" lg="3" align="center">
                    <v-text-field clearable variant="underlined" :disabled="loading_groups" v-model="search" color="primary"
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
                    <v-btn :disabled="level_disable || loading_groups" class="add-button"
                        @click="add_group_dialog = true" color="white" size="small" elevation="10" dark
                        style="background-color: rgb(64, 184, 59)">
                        ADICIONAR<v-icon>mdi-plus</v-icon>
                    </v-btn>
                </v-col>
            </v-row>
        </v-toolbar>
        <v-data-table class="mb-2" :headers="headers" :items="groups" :search="search" multi-sort fixed-header :items-per-page-options="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'Tudo' }
            ]" items-per-page="10" items-per-page-text="Itens por página:"
            :no-data-text="loading_groups ? 'Carregando...' : 'Nenhum registro encontrado'">
            <template v-slot:item.name="{ value, item }">
                <span :class="(dark_theme ? 'text-shadow-black-1 ': '') + (item.default_group ? 'font-italic' : '')">{{ value }}</span>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-menu open-on-hover location="start">
                    <template v-slot:activator="{ props }">
                        <v-btn :disabled="Boolean(item.default_group)" variant="text" size="small" icon="mdi-dots-vertical" v-bind="props"></v-btn>
                    </template>
                    <div class="my-1 ml-1">
                        <v-hover>
                            <template v-slot:default="{ isHovering, props }">
                                <v-btn class="hover-buttons" color="red" :variant="isHovering ? 'outlined' : 'elevated'"
                                    v-bind="props" icon size="x-small" @click="openDeleteGroupDialog(item)">
                                    <v-icon :color="isHovering ? 'red' : 'white'">mdi-delete</v-icon>
                                </v-btn>
                            </template>
                        </v-hover>
                        <v-hover>
                            <template v-slot:default="{ isHovering, props }">
                                <v-btn class="mx-1 hover-buttons" color="orange"
                                    :variant="isHovering ? 'outlined' : 'elevated'" v-bind="props" icon size="x-small"
                                    @click="openEditGroupDialog(item)">
                                    <v-icon :color="isHovering ? 'orange' : 'white'">mdi-pencil</v-icon>
                                </v-btn>
                            </template>
                        </v-hover>
                    </div>
                </v-menu>
            </template>
        </v-data-table>
        <DialogAddGroup @new_register="pushNewGroup" @close="add_group_dialog = false" :model="add_group_dialog"
            color="rgb(90, 180, 80)" />
        <DialogEditGroup @edited_register="editGroup" @close="edit_group_dialog = false"
            :data="edit_group_dialog_data" :model="edit_group_dialog" color="orange" />
        <DialogDeleteGroup @deleted="popGroup" @close="delete_group_dialog = false" :data="delete_group_dialog_data"
            :model="delete_group_dialog" color="rgb(230, 60, 60)" />
    </v-card>
</template>
<script setup>
import api from '@/plugins/axios.js'
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import DialogAddGroup from '@/components/dialogs/DialogAddGroup.vue'
import DialogEditGroup from '@/components/dialogs/DialogEditGroup.vue'
import DialogDeleteGroup from '@/components/dialogs/DialogDeleteGroup.vue'
import { useTheme } from 'vuetify'
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')

// Variables
const loading_groups = ref(false)
const groups = ref([])
const add_group_dialog = ref(false)
const auth = useAuthStore()
const level_disable = auth.user.level < 1
const search = ref('')
const headers = reactive([
    { title: 'ID', key: 'id', width: '10%' },
    { title: 'Nome', key: 'name', width: '80%' },
    { title: 'Ações', key: 'actions', sortable: false, width: '10%' }
])
const loading_status = reactive({})
const edit_group_dialog = ref(false)
const delete_group_dialog = ref(false)
const edit_group_dialog_data = reactive({})
const delete_group_dialog_data = reactive({})



// Created
getGroups()

// Methods
function getGroups(attempt = 1) {
    loading_groups.value = true
    api.get('get_groups', { params: { manage: true } }).then((response) => {
        groups.value = response.data
        loading_groups.value = false
    }).catch((error) => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getGroups(attempt + 1), 1000)
        } else {
            loading_groups.value = false
        }        
    })
}

function editGroup(edited_group) {
    groups.value.splice(groups.value.findIndex(group => group.id == edited_group.id), 1, edited_group)
}

function popGroup(id) {
    groups.value = groups.value.filter((group) => group.id != id)
}

function openEditGroupDialog(group) {
    Object.assign(edit_group_dialog_data, group)
    edit_group_dialog.value = true
}

function openDeleteGroupDialog(group) {
    Object.assign(delete_group_dialog_data, group)
    delete_group_dialog.value = true
}

function pushNewGroup(group) {
    groups.value.unshift(group)
}

</script>
<style scoped>
.add-group-button:hover {
    font-weight: bold;
    transform: scale(1.1);
    background-color: white !important;
    color: rgb(39, 56, 153) !important;
}
</style>