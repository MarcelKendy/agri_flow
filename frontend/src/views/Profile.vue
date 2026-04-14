<template>
    <div>
        <v-card class="profile-card">
            <v-row>
                <v-col cols="4">
                    <v-row>
                        <v-col cols="12" class="align-center">
                            <v-hover>
                                <template v-slot:default="{ isHovering, props }">
                                    <div v-bind="props" class="align-center" style="position: relative">
                                        <v-avatar @click="my_profile && openDialogEditProfile()" size="17vw" style="transition: 0.2s;" :style="(my_profile ? 'cursor: pointer; ' : '') + (dark_theme ? (isHovering ? 'border: solid 4px rgb(255, 255, 255, 0.4);' : 'border: solid 2px rgb(255, 255, 255, 0.15);') : (isHovering ? 'border: solid 4px rgb(255, 255, 255);' : 'border: solid 2px rgb(255, 255, 255);'))">
                                            <v-img v-if="user_data.photo" style="transition: transform 0.3s" :style="isHovering ? 'transform: scale(1.1)' : ''" :src="backend_storage + user_data.photo" cover lazy-src="media/images/man.png">
                                                <template v-slot:placeholder>
                                                    <div class="d-flex align-center justify-center fill-height">
                                                        <v-progress-circular :width="6" :size="248"
                                                        color="grey-lighten-4" indeterminate></v-progress-circular>
                                                    </div>
                                                </template>
                                            </v-img>
                                            <v-img v-else src="media/images/man.png" style="transition: 0.4s ease-in-out"
                                                :style="'object-fit: cover; width: 64px;'">
                                            </v-img>                                    
                                        </v-avatar>                                        
                                        <v-tooltip text="Editar Perfil" :content-class="dark_theme ? 'tooltip-default-dark' : 'tooltip-default-light'">
                                            <template v-slot:activator="{ props }">
                                                <v-scale-transition>
                                                    <v-btn v-if="isHovering && my_profile" v-bind="props" @click="openDialogEditProfile()" color="white" size="2vw" icon="mdi-account-edit" class="account-edit-icon"></v-btn>
                                                </v-scale-transition>
                                            </template>
                                        </v-tooltip>                                                                                                                           
                                        <v-tooltip v-if="last_login" location="top" content-class="tooltip-green" :model-value="isHovering">
                                            <template v-slot:activator="{ props }">
                                                <v-icon v-bind="props" color="green" size="3vw" class="last-login-icon">mdi-radiobox-marked</v-icon>
                                            </template>
                                            <span style="text-shadow: black 1px 0.5px">
                                                <v-icon class="pr-2">mdi-login</v-icon>{{ 'Login hoje às ' + last_login }}
                                            </span>                                            
                                        </v-tooltip>                                                            
                                    </div>                            
                                </template>
                            </v-hover>
                        </v-col>                    
                        <v-col cols="12" class="align-center">
                            <span class="user-name" :style="'text-shadow: 2px 1px ' + (dark_theme ? 'black' : 'white')">{{ user_data?.full_name ?? user_data.name }}</span>
                        </v-col>
                        <v-col cols="12" class="align-center">      
                            <v-hover>
                                <template v-slot:default="{ isHovering, props }">                      
                                    <div v-bind="props" @click="my_profile ? edit_note = true : null" class="user-note align-center" :style="(isHovering && my_profile ? 'cursor: pointer;' : '') + ('width: 95%;') + (dark_theme ? '' : 'box-shadow: rgba(0, 0, 0, 0.07) 0px -23px 25px 0px inset, rgba(0, 0, 0, 0.05) 0px -36px 30px 0px inset, rgba(255, 255, 255, 0.4) 0px -79px 40px 0px inset, rgba(0, 0, 0, 0.06) 0px 0px 0px, rgba(0, 0, 0, 0.01) 0px 0px 0px, rgba(0, 0, 0, 0.02) 0px 0px 4px, rgba(0, 0, 0, 0.516) 0px 1px 2px;')">
                                        <v-tooltip text="Alterar status &#128526;" content-class="tooltip-blue" :model-value="isHovering && my_profile">
                                            <template v-slot:activator="{ props }">
                                                <v-icon size="2vw" v-bind="my_profile ? props : null" class="user-note-icon" :style="dark_theme ? 'text-shadow: rgb(0, 0, 0) 1.5px 1px;' : 'text-shadow: rgb(255, 255, 255) 1.5px 1px;'">mdi-comment-quote</v-icon>
                                            </template>                                                                                        
                                        </v-tooltip>                                        
                                        <v-scale-transition>
                                            <v-btn v-if="isHovering && my_profile && !edit_note" size="2.2vw" icon="mdi-pencil-plus" color="blue" style="position: absolute; font-size: 1vw; z-index: 1;"></v-btn>
                                        </v-scale-transition>                                        
                                        <span v-if="!edit_note" class="user-note-text" :style="isHovering && my_profile ? 'opacity: 0.2' : ''">{{ user_data.note ? user_data.note : 'Defina um status...' }}</span>
                                        <v-textarea v-else ref="editNoteTextarea" color="blue" :loading="loading_note" :disabled="loading_note" 
                                            @keydown.enter="saveNote()" @blur="saveNote()" style="width: 100%; transition: 0.3s;" v-model="user_data.note" 
                                            placeholder="Qual o status de hoje?" clearable counter maxlength="200" auto-grow rows="1">
                                        </v-textarea>
                                    </div>
                                </template>
                            </v-hover>
                        </v-col>
                        <v-col cols="12">
                            <v-card class="about-card" :loading="loading_user">
                                <v-list class="about-card-list">
                                    <v-list-subheader>
                                        <span style="font-size: 1vw;">Sobre</span>                                        
                                        <v-btn v-if="my_profile" @click="openDialogEditProfile()" prepend-icon="mdi-account-edit" style="height: 1.8vw; font-size: 0.9vw; position: absolute; top: 8px; right: 8px;" color="blue">Editar</v-btn>
                                    </v-list-subheader>                                                        
                                    <v-list-item class="about-card-list-item" :class="dark_theme ? 'text-shadow-black-1' : ''">
                                        <template v-slot:prepend>
                                            <v-icon icon="mdi-email"></v-icon>
                                        </template>                            
                                        <v-list-item-title>
                                            <span v-if="!loading_user" class="about-card-list-info">{{ user_data.email }}</span>
                                        </v-list-item-title>
                                    </v-list-item>                                                                        
                                    <v-list-item class="about-card-list-item" :class="dark_theme ? 'text-shadow-black-1' : ''">
                                        <template v-slot:prepend>
                                            <v-icon :icon="user_data.level > 0 ? 'mdi-shield-lock-open' : 'mdi-shield-lock'"></v-icon>
                                        </template>                            
                                        <v-list-item-title>
                                            <span v-if="!loading_user" class="about-card-list-info">{{ 'Acesso ' + accesses_translations[user_data.level] }}</span>
                                        </v-list-item-title>
                                    </v-list-item>
                                    <v-list-item class="about-card-list-item" :class="dark_theme ? 'text-shadow-black-1' : ''">
                                        <template v-slot:prepend>
                                            <v-icon :icon="user_data.status > 0 || my_profile ? 'mdi-account-check' : 'mdi-account-cancel'"></v-icon>
                                        </template>                            
                                        <v-list-item-title>
                                            <span v-if="!loading_user" class="about-card-list-info">{{ 'Usuário ' + (user_data.status > 0 || my_profile ? 'Ativo' : 'Desativado') }}</span>
                                        </v-list-item-title>
                                    </v-list-item>
                                </v-list>
                            </v-card>
                        </v-col>                        
                    </v-row>                    
                </v-col>
                <v-col cols="8"> 
                    <v-row>
                        
                    </v-row>                                                                                 
                </v-col>
            </v-row>
        </v-card>
        <DialogEditProfile @close="closeDialogEditProfile()" :model="profile_dialog" :data="profile_form" color="orange" />
    </div>
</template>
<script setup>
//Imports
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useTheme } from 'vuetify'
import api from '@/plugins/axios.js'
import { useAuthStore } from '@/stores/auth.js'
import DialogEditProfile from '@/components/dialogs/DialogEditProfile.vue'
import { useRoute } from 'vue-router'
import { Chart, registerables } from 'chart.js'

//Variables
Chart.register(...registerables)
const chartCanvas = ref(null)
let chartInstance = null
const route = useRoute()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
const auth = useAuthStore()
const profile_dialog = ref(false)
let profile_form = ''
const last_login = ref('')
const user_data = reactive([])
const edit_note = ref(false)
const loading_note = ref(false)
const loading_user = ref(false)
const editNoteTextarea = ref(null)
const accesses_translations = {
    0: 'Básico',
    1: 'Gestor',    
    2: 'Administrador'
}

//Computeds
const my_profile = computed(() => user_data.id === auth.user.id)

//Mounted
onMounted(() => {
    
})

//Watchers
watch(edit_note, (value) => {
  if (value) {
    requestAnimationFrame(() => {
        editNoteTextarea.value?.focus()
    })
  }
})

watch(() => route.params.user_id, (new_user_id, old_user_id) => {
    if (new_user_id !== old_user_id) {
        getUserProfile()
    }
})

//Created
getUserProfile()

//Methods

function getProfileData() {
    getActiveSession()
}

function saveNote(attempt = 1) {
    loading_note.value = true
    api.put('edit_user/' + user_data.id, { note: user_data.note }).then((response) => {
        auth.setUser(response.data)
        loading_note.value = false
        edit_note.value = false
    }).catch((error) => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => saveNote(attempt + 1), 1000)
        } else {
            loading_note.value = false
        } 
    })
}

function getUserProfile(attempt = 1) {
    if (!route.params.user_id || Number(route.params.user_id) == auth.user.id || Number(route.params.user_id) <= 0) {
        Object.assign(user_data, auth.user)
        getProfileData()
        return
    }    
    loading_user.value = true
    api.get('get_user/' + route.params.user_id).then((response) => {        
        if (response.data && response.data.id) {
            Object.assign(user_data, response.data)            
        } else {
            Object.assign(user_data, auth.user)
        }        
        loading_user.value = false
        getProfileData()
    }).catch((error) => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getUserProfile(attempt + 1), 1000)
        } else {
            loading_user.value = false
        } 
    })    
}

function formatDateTime(input_date, time_only = true) {    
  const date = new Date(input_date)  
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  if (!time_only) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year} às ${hours}:${minutes}`;
  }
  return `${hours}:${minutes}`
}

function getActiveSession(attempt = 1) {
    api.get('get_active_session', { params: { user_id: user_data.id } }).then((response) => {
        let session = response.data                
        last_login.value = session.created_at ? formatDateTime(session.created_at) : ''
    }).catch((error) => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getActiveSession(attempt + 1), 1000)
        } 
    })
}

function openDialogEditProfile() {
    profile_form = reactive({ id: auth.user.id, name: auth.user.full_name, cpf: auth.user.cpf, email: auth.user.email, password: '', password_confirmation: '' })  
    profile_dialog.value = true
}

function closeDialogEditProfile() {
    profile_dialog.value = false 
    profile_form = reactive({ id: auth.user.id, name: auth.user.full_name, cpf: auth.user.cpf, email: auth.user.email, password: '', password_confirmation: '' })
    Object.assign(user_data, auth.user)
}

</script>
<style scoped>

canvas {
    width: 100%;
    height: 80%;    
}

.profile-card {
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 15px;
    background-color: rgba(65, 65, 65, 0.1);
    box-shadow: rgba(0, 0, 0, 0.25) 0px 4px 8px, rgba(0, 0, 0, 0.22) 0px 10px 10px;
}

.last-login-icon {
    position: absolute; 
    bottom: 10px; 
    right: 1.8vw; 
    text-shadow: rgb(0, 55, 0) 0.5px 1px;
}

.account-edit-icon {
    position: absolute; 
    top: 10px; 
    left: 1.8vw;     
    border: solid 1px grey;
}

.user-name {
    font-weight: bolder;
    font-size: 1.5vw;    
}

.user-note {
    position: relative;
    transition: 0.3s;
    background-color: rgba(214, 214, 214, 0.1) !important;
    padding: 1.3vw;
    border-top-right-radius: 25px;    
    border-bottom-left-radius: 25px;
    box-shadow: rgba(0, 0, 0, 0.17) 0px -23px 25px 0px inset, rgba(0, 0, 0, 0.15) 0px -36px 30px 0px inset, rgba(0, 0, 0, 0.1) 0px -79px 40px 0px inset, rgba(0, 0, 0, 0.06) 0px 0px 0px, rgba(0, 0, 0, 0.09) 0px 0px 0px, rgba(0, 0, 0, 0.09) 0px 0px 4px, rgba(0, 0, 0, 0.616) 0px 1px 2px;
}

.user-note-icon {
    position: absolute; 
    top: -7px; 
    left: -0.8vw;     
}

.user-note-text {
    transition: 0.2s;
    font-family: Quicksand;
    font-weight: bolder;
    font-size: 1vw;  
    word-break: break-word;  
}

.about-card {
    width: 100%;        
    font-size: 1vw;
    border: solid rgba(255, 255, 255, 0.1) 1px;
    background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.123), rgba(84, 121, 170, 0.137));    
}

.about-card-list {
    background-color: rgba(255, 255, 255, 0);    
    overflow: visible !important;
}

.about-card-list-item {
    min-height: 3vw;
}

.about-card-list-info {    
    font-size: 1vw;    
}

.refresh-avatar {
    transition: 0.2s;
    cursor: pointer;
}

.refresh-avatar.active {
    background-color: rgb(248, 248, 248); 
    transform: scale(0.85); 
}

.profile-dashboard-card {
    margin-bottom: 20px;
    padding-top: 20px;       
    box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px;   
    border-top: solid rgba(83, 83, 83, 0.13) 2px;
    border-bottom: solid rgba(83, 83, 83, 0.13) 2px;
}

.profile-dashboard-card-dark {
    background-color: rgba(50, 50, 50, 0.267);
}

.profile-dashboard-card-light {
    background-color: rgba(242, 250, 255, 0.616);
}

.profile-dashboard-number-dark {
    transition: 0.3s;
    margin-left: 2vw; 
    margin-right: 10px; 
    font-size: 2vw; 
    text-shadow: black 1px 1px;
}

.profile-dashboard-card-title-dark {
    margin-left: 2vw; 
    font-family: quicksand; 
    font-size: 1.4vw; 
    color: rgb(190, 190, 190); 
    text-shadow: black 1px 1px;
}

.profile-dashboard-number-light {
    transition: 0.3s;
    margin-left: 2vw; 
    margin-right: 10px; 
    font-size: 2vw; 
    text-shadow: rgb(255, 255, 255) 1px 1px;
}

.profile-dashboard-card-title-light {
    margin-left: 2vw; 
    font-family: quicksand; 
    font-size: 1.4vw;   
    text-shadow: rgb(255, 255, 255) 1px 1px;
}
</style>
