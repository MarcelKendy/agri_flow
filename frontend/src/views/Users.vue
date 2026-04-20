<template>
    <div>
        <v-card :loading="loading_users" :min-height="items.length ? '' : '250px'">
            <template v-slot:loader="{ isActive }">
                <v-progress-linear :active="isActive" color="primary" height="7" indeterminate></v-progress-linear>
            </template>
            <v-toolbar :class="dark_theme ? 'custom-toolbar-dark' : 'custom-toolbar-light'">
                <v-row class="align-center pr-3">
                    <v-col cols="3">
                        <v-text-field color="primary" v-model="search_field" placeholder="Pesquisar..." clearable
                            variant="underlined">
                            <template v-slot:prepend>
                                <v-icon color="primary" icon="mdi-account-search" />
                            </template>
                        </v-text-field>
                    </v-col>
                    <v-col cols="9" class="align-end">
                        <v-btn :disabled="loading_users" class="add-button"
                            @click="add_user_dialog = true" color="white" size="small" elevation="10" dark
                            style="background-color: rgb(64, 184, 59)">
                            ADICIONAR<v-icon>mdi-plus</v-icon>
                        </v-btn>
                    </v-col>
                </v-row>
            </v-toolbar>
            <div v-if="loading_users" style="display: flex; justify-content: center; align-items: center;">
                <v-img max-width="64" class="pt-15" src="media/images/loading.gif" />
            </div>
            <v-list lines="two" class="pa-2" id="users-list">
                <v-list-item v-if="!loading_users && paginated_items.length == 0"
                    :class="(dark_theme ? 'list-item-dark' : 'list-item') + 'ma-13'">
                    <template v-slot:prepend>
                        <v-avatar color="grey-darken-2">
                            <v-icon color="white">mdi-account-search</v-icon>
                        </v-avatar>
                    </template>
                    <span>Sem registros com o filtro selecionado</span>
                </v-list-item>
                <v-list-item v-else v-for="item in paginated_items" :key="item.id"
                    :class="(dark_theme ? 'list-item-dark' : 'list-item')">
                    <template v-slot:prepend>
                        <v-hover v-slot:default="{ isHovering, props }">         
                            <router-link :to="{ name: 'profile', params: { user_id: item.id } }">                      
                                <v-avatar v-bind="props" :size="isHovering ? 72 : 64" @click.stop style="transition: 0.2s" :style="isHovering ? 'border: solid 2px white' : ''"  :color="!item.photo ? (item.status != 1 ? 'red-lighten-1' : (item.level > 1 ? 'green-lighten-1' : 'teal-lighten-1')) : ''">
                                    <v-img v-if="item.photo" :src="backend_storage + item.photo" cover lazy-src="media/images/man.png" style="transition: 0.2s" :style="isHovering ? 'opacity: 0.6;' : ''">
                                        <template v-slot:placeholder>
                                            <div class="d-flex align-center justify-center fill-height">
                                                <v-progress-circular :width="(64 * 4) / 64"
                                                    :size="64" color="grey-lighten-4"
                                                    indeterminate></v-progress-circular>
                                            </div>
                                        </template>
                                    </v-img>
                                    <v-icon v-else size="x-large" color="white" :style="isHovering ? 'opacity: 0.6;' : ''">{{ item.status != 1 ? 'mdi-account-cancel' :
                                        (item.level == 1 ?
                                            'mdi-account-tie' : (item.level == 2 ? 'mdi-account-supervisor' : 'mdi-account'))
                                    }}</v-icon>
                                    <v-scale-transition>
                                        <v-icon v-if="isHovering" style="position: absolute;" class="text-shadow-black-2" color="white">mdi-account-eye</v-icon>
                                    </v-scale-transition>                                            
                                </v-avatar>
                            </router-link> 
                        </v-hover>
                    </template>
                    <v-row>
                        <v-col cols="4" class="d-flex align-center">
                            <span>{{ item.name }}</span>
                        </v-col>
                        <v-col cols="3" class="d-flex align-center">
                            <span>{{ item.email }}</span>
                        </v-col>
                        <v-col cols="2" class="d-flex align-center">
                            <span>{{ item.cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') }}</span>
                        </v-col>                                                
                        <v-col cols="1" class="d-flex align-center">
                            <v-select color="primary" label="Nível" variant="underlined" v-model="item.level"
                                @update:modelValue="editUser(item.id + '_level', item, 'level')"
                                :items="[{ title: 'Básico', value: 0 }, { title: 'Gestor', value: 1 }, { title: 'Adm', value: 2 }]"
                                :loading="edit_loader[item.id + '_level']"
                                :disabled="edit_loader[item.id + '_level']"></v-select>
                        </v-col>
                        <v-col cols="1" class="d-flex align-center">
                            <v-tooltip :content-class="dark_theme ? 'tooltip-default-dark' : 'tooltip-default-light'"
                                text="Enviar reset de senha por e-mail" location="top">
                                <template v-slot:activator="{ props }">
                                    <v-btn v-bind="props" :icon="item.reset_password_icon"
                                        :color="item.reset_password_icon == 'mdi-lock-reset' ? 'yellow-lighten-1' : 'success'"
                                        :loading="item.loading_password" :disabled="item.loading_password || item.status == 0" variant="text"
                                        @click="(item.reset_password_icon == 'mdi-lock-reset') && resetPassword({ id: item.id, email: item.email, user_id: auth.user.id })"></v-btn>
                                </template>
                            </v-tooltip>
                        </v-col>
                        <v-col cols="1">
                            <small :class="'pl-2 font-italic ' + (dark_theme ? '' : 'bold')">ativo</small>
                            <v-switch color="primary" @change="switchClick({ id: item.id, status: item.status })"
                                :disabled="item.loading_status" v-model="item.status"
                                :loading="item.loading_status"></v-switch>
                        </v-col>
                    </v-row>
                </v-list-item>
            </v-list>
            <div v-if="!loading_users && paginated_items.length > 0" style="position: relative">
                <v-pagination v-model="current_page" class="mt-2 mb-4" :length="total_pages" :total-visible="5"
                    rounded="circle"></v-pagination>
                <v-row style="position:absolute; top: 15px; right: 90px; width: 250px">
                    <v-col cols="8">
                        <span class="font-quicksand bold" :class="dark_theme ? 'text-shadow-black-2' : ''">Itens por
                            página:</span>
                    </v-col>
                    <v-col cols="4" class="pa-0">
                        <v-select color="primary" no-data-text="Ocorreu um erro, atualize a página"
                            :items="[5, 10, 15, 30, 100]" variant="solo-filled"
                            :rules="getRules({ required: true, number: { val: 1 } })" style="top: 200px !important;"
                            density="compact" v-model="items_per_page"></v-select>
                    </v-col>
                </v-row>
            </div>
        </v-card>
        <DialogAddUser @new_register="pushNewUser" @close="add_user_dialog = false" :model="add_user_dialog"
            color="rgb(90, 180, 80)" />
    </div>
</template>

<script setup>
import api from '@/plugins/axios.js'
import { ref, reactive, watch, computed } from 'vue'
import DialogAddUser from '@/components/dialogs/DialogAddUser.vue'
import { useAuthStore } from '@/stores/auth.js'
import { useSnackbarStore } from '@/stores/snackbar'
import { useTheme, useDisplay } from 'vuetify'

const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const items = ref([])
const loading_users = ref(false)
const add_user_dialog = ref(false)
const auth = useAuthStore()
const snackbar = useSnackbarStore()
const search_field = ref('')
const edit_loader = reactive({})
const current_page = ref(1)
const items_per_page = ref(10);


// Created
getUsers()

// Computeds
const filtered_items = computed(() => {
    current_page.value = 1
    return (!search_field.value || search_field.value.length === 0)
        ? items.value
        : items.value.filter((item) =>
            item.name.toLowerCase().includes(search_field.value.toLowerCase().trim())
        )
})

const paginated_items = computed(() => {
    const start = (current_page.value - 1) * items_per_page.value
    const end = start + items_per_page.value
    return filtered_items.value.slice(start, end)
})

const total_pages = computed(() => {
    return Math.ceil(filtered_items.value.length / items_per_page.value)
})

// Watchers
watch(items_per_page, () => {
    current_page.value = 1
})

// Methods
function pushNewUser(user) {
    user = {
        ...user,
        status: true,
        reset_password_icon: 'mdi-lock-reset'
    }
    items.value.unshift(user)
}

function getUsers(attempt = 1) {
    loading_users.value = true
    api.get('get_users', { params: { manage: true } }).then(response => {
        items.value = response.data.map(item => {
            return {
                ...item,
                status: item.status == 1,
                reset_password_icon: 'mdi-lock-reset'
            }
        })
        loading_users.value = false
    }).catch(error => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getUsers(attempt + 1), 1000)
        } else {
            snackbar.open({ preset: 'error' })
            loading_users.value = false
        }        
    })
}

function editUser(loader_index, item, field) {
    edit_loader[loader_index] = true
    let query = {}
    query[field] = item[field]
    api.put('edit_user/' + item.id, query).then((response) => {
        edit_loader[loader_index] = false
    }).catch((error) => {
        console.log(error)
        snackbar.open({ preset: 'error' })
        edit_loader[loader_index] = false
    })
}

function switchClick(new_value) {
    const item = items.value.find(item => item.id == new_value.id)
    item.loading_status = true
    api.put('edit_user/' + new_value.id, new_value).then(response => {
        item.loading_status = false
    }).catch(error => {
        console.log(error)
        snackbar.open({ preset: 'error' })
        item.loading_status = false
    })
}

function resetPassword(data) {
    const item = items.value.find(item => item.id == data.id)
    data.email = data.email.split('@')[0] 
    item.loading_password = true
    api.post('send_password_reset_mail', data).then(response => {
        item.loading_password = false
        item.reset_password_icon = 'mdi-lock-check'
        setTimeout(() => { item.reset_password_icon = 'mdi-lock-reset' }, 2000)
    }).catch(error => {
        console.log(error)
        snackbar.open({ preset: 'error' })
        item.loading_password = false
    })
}


</script>
<style>
#users-list .v-input__details {
    display: none;
}
</style>