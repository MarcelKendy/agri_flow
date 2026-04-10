<template>
    <div>
        <div class="background-div1"></div>
        <Particles/>
        <div class="centered">
            <v-scale-transition>
                <v-card v-show="start" :width="tabs == 1 ? '450px' : '650px'" class="ma-auto login-card" :loading="loading"
                    id="login-card">
                    <template v-slot:loader="{ isActive }">
                        <v-progress-linear :active="isActive" color="teal-darken-3" height="7"
                            indeterminate></v-progress-linear>
                    </template>
                    <v-toolbar class="login-toolbar" style="transition: 0.4s" :height="end_animation ? '90' : '150'">
                        <v-row>
                            <v-col cols="12" style="position: relative;">
                                <v-scale-transition>
                                    <div class="animation-container" v-if="logo_animation">
                                        <strong class="text-animation gradient-text font-quicksand" :style="{                                        
                                            transition: '0.4s',
                                            fontSize: end_animation ? '26px' : '34px',
                                            paddingRight: end_animation ? '25px' : '0px'
                                        }">
                                            Camp
                                        </strong>
                                        <div class="logo-container" style="position: absolute;" :style="{
                                            transition: '0.4s',
                                            paddingLeft: end_animation ? '35px' : '0px'
                                        }">
                                            <img src="/media/icons/logo.png" style="transition: 0.4s" :style="end_animation ? 'max-width: 80px;' : 'max-width: 124px;'"/>
                                        </div>
                                    </div>
                                </v-scale-transition>
                            </v-col>
                        </v-row>
                        <template v-if="end_animation && !reset_password_ui" v-slot:extension>
                            <v-tabs color="teal" v-model="tabs" grow :disabled="loading || alert">
                                <v-tab :value="1">
                                    <v-icon class="pr-2">mdi-login</v-icon>Login
                                </v-tab>
                                <v-tab :value="2">
                                    <v-icon class="pr-2">mdi-account-plus</v-icon>Novo
                                </v-tab>
                            </v-tabs>
                        </template>
                    </v-toolbar>                    
                    <v-card v-if="reset_password_ui" class="form-card ma-1 pt-3" elevation="10">
                        <v-card-text>
                            <v-form ref="form0">
                                <v-row>
                                    <v-col cols="12" class="align">
                                        <v-text-field color="teal" v-model="reset_password_form.password"
                                            :readonly="loading || alert"
                                            :rules="getRules({ required: true, minlen: { val: 5 }, maxlen: { val: 30 }, hasLowercase: true, hasUppercase: true, hasNumber: true, hasSpecialchar: true })"
                                            clearable label="Nova Senha" placeholder="Digite uma nova senha" counter
                                            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                                            :type="visible ? 'text' : 'password'"
                                            prepend-inner-icon="mdi-lock-outline"
                                            @click:append-inner="visible = !visible"
                                            @keyup.enter="resetPasswordNLogin()"></v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-text-field color="teal" v-model="reset_password_form.password_confirmation"
                                            :readonly="loading || alert"
                                            :rules="getRules({ required: true, equals: { val: reset_password_form.password, message: 'As senhas não se correspondem' } })"
                                            clearable label="Confirme sua senha" placeholder="Repita sua senha"
                                            counter :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                                            :type="visible ? 'text' : 'password'"
                                            prepend-inner-icon="mdi-lock-outline"
                                            @click:append-inner="visible = !visible"
                                            @keyup.enter="resetPasswordNLogin()"></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-form>
                        </v-card-text>                    
                        <v-divider></v-divider>
                        <v-card-actions style="position: relative">
                            <v-slide-x-reverse-transition>
                                <v-alert height="35" v-show="alert" :color="alert_fail ? 'red' : 'green'" border="start" elevation="3" dark
                                            class="mx-2"><v-icon size="small" class="mr-2 ml-0 pl-0">{{ alert_fail ? 'mdi-account-question' : 'mdi-emoticon-wink' }}</v-icon>{{ alert_message }}</v-alert>
                            </v-slide-x-reverse-transition>                            
                            <v-hover>
                                <template v-slot:default="{ isHovering, props }">
                                    <v-btn v-if="!alert" v-bind="props" :variant="isHovering ? 'tonal' : 'outlined'"
                                        style="position: absolute; right: 20px" :loading="loading" :disabled="loading" color="teal" append-icon="mdi-login"
                                        @click="resetPasswordNLogin()">Confirmar</v-btn>                                                 
                                </template>
                            </v-hover>
                        </v-card-actions>
                    </v-card>    
                    <v-window color="teal" v-model="tabs" v-else>
                        <v-window-item :key="1" :value="1">
                            <v-card class="form-card ma-1 pt-3" elevation="10">
                                <v-card-text v-if="!show_otp_form">
                                    <v-form ref="form1">
                                        <v-row>
                                            <v-col cols="12">
                                                <v-text-field color="teal" v-model="login_form.email" :readonly="loading || alert"
                                                :rules="getRules({ required: { message: 'Digite seu email corporativo' }, email: { sicoob: true, message: 'Email incorreto (nome.sobrenome@sicoobcredisg.com.br)', maxlen: {val: 60} } })"
                                                class="mb-1" clearable label="E-mail" placeholder="nome.sobrenome"
                                                suffix="@sicoobcredisg.com.br" @keyup.enter="!loading && sendOtp()"></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-text-field color="teal" v-model="login_form.password"
                                                    :readonly="loading || alert"
                                                    :rules="getRules({ required: {message: 'Digite sua senha'}, minlen: { val: 5 }, maxlen: { val: 30 }, hasLowercase: true, hasUppercase: true, hasNumber: true, hasSpecialchar: true })"
                                                    clearable label="Senha" placeholder="Digite sua senha"
                                                    :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                                                    :type="visible ? 'text' : 'password'"
                                                    prepend-inner-icon="mdi-lock-outline"
                                                    @click:append-inner="visible = !visible"
                                                    @keyup.enter="!loading && sendOtp()"></v-text-field>
                                            </v-col>
                                        </v-row>
                                    </v-form>
                                </v-card-text>
                                <v-card-text v-else>
                                    <v-alert class="align-center text-shadow-black-1 pa-3" variant="outlined" color="teal-lighten-4">
                                        <v-icon class="mr-2" size="x-large">mdi-email-fast</v-icon>
                                        <strong style="font-size: 16px;">Insira o código de login enviado no seu e-mail</strong>
                                    </v-alert>
                                    <v-otp-input v-model="otp_form.otp" base-color="teal-lighten-4" type="text" autofocus :error="alert && alert_fail" @keyup.enter="(show_otp_form && otp_form.otp.length == 6 && !loading_resend_otp && !alert) ? checkOtpNLogin() : null" :loading="loading" variant="outlined"></v-otp-input>
                                </v-card-text>
                                <v-divider></v-divider>
                                <v-card-actions style="position: relative">
                                    <v-slide-x-reverse-transition>
                                        <v-alert height="35" v-show="alert" :color="alert_fail ? 'red' : 'green'" border="start" elevation="3" dark
                                            class="mx-2"><v-icon size="small" class="mr-2 ml-0 pl-0">{{ alert_fail ? 'mdi-account-question' : 'mdi-emoticon-wink' }}</v-icon>{{ alert_message }}</v-alert>
                                    </v-slide-x-reverse-transition>
                                    <v-btn v-if="!alert && !show_otp_form" :disabled="loading" style="position: absolute; left: 10px" variant="tonal" prepend-icon="mdi-lock-reset" @click="reset_password_dialog = true" size="small">Esqueci minha senha</v-btn>
                                    <v-btn v-else-if="show_otp_form && !alert" :disabled="disable_resend_otp || loading || loading_resend_otp" :loading="loading_resend_otp" style="position: absolute; left: 10px" variant="tonal" prepend-icon="mdi-email-alert" @click="resendOtp()" size="small">{{ disable_resend_otp ? 'Reenviar e-mail (' + disable_resend_otp_timer + 's)' : 'Reenviar e-mail' }}</v-btn>
                                    <v-hover>
                                        <template v-slot:default="{ isHovering, props }">
                                            <v-btn v-if="!alert" v-bind="props" :variant="isHovering ? 'tonal' : 'outlined'"
                                                style="position: absolute; right: 20px" :loading="loading" :disabled="loading || (show_otp_form && otp_form.otp.length < 6) || loading_resend_otp" color="teal" append-icon="mdi-login"
                                                @click="show_otp_form ? checkOtpNLogin() : sendOtp()">{{ show_otp_form ? 'Entrar' : 'Confirmar' }}</v-btn> 
                                        </template>
                                    </v-hover>
                                </v-card-actions>
                            </v-card>
                        </v-window-item>
                        <v-window-item :key="2" :value="2">
                            <v-card class="form-card ma-1 pt-3" elevation="10" dark>
                                <v-card-text>
                                    <v-form ref="form2">
                                        <v-row>
                                            <v-col cols="12">
                                                <v-text-field color="teal" v-model="signin_form.name"
                                                    :readonly="loading || alert"
                                                    :rules="getRules({ required: true, maxlen: { val: 100 }, name: true })" @keyup="nameMask(signin_form)"
                                                    clearable label="Nome Completo" placeholder="Nome Sobrenome"
                                                    @change="setEmail(signin_form.name)"></v-text-field>
                                            </v-col>
                                            <v-col cols="8">
                                                <v-text-field color="teal" v-model="signin_form.email"
                                                    :readonly="loading || alert"
                                                    :rules="getRules({ required: { message: 'Digite seu email corporativo' }, email: { sicoob: true, message: 'Email incorreto (nome.sobrenome@sicoobcredisg.com.br)', maxlen: { val: 60 } } })"
                                                    clearable label="E-mail" placeholder="nome.sobrenome"
                                                    suffix="@sicoobcredisg.com.br"
                                                    hint="Não precisa digitar o domínio! Apenas nome.sobrenome"></v-text-field>
                                            </v-col>
                                            <v-col cols="4">
                                                <v-text-field color="teal" v-model="signin_form.cpf"
                                                    v-mask="'###.###.###-##'" :readonly="loading || alert"
                                                    :rules="getRules({ required: true, cpf: true })" clearable label="CPF"
                                                    placeholder="Informe seu CPF"></v-text-field>
                                            </v-col>
                                            <v-col cols="6">
                                                <v-select color="teal" v-model="signin_form.group_id" label="Grupo"
                                                    :rules="getRules({ required: true })" :readonly="loading || alert"
                                                    :disabled="loading_groups" :loading="loading_groups" :items="groups"
                                                    item-title="name" item-value="id"></v-select>
                                            </v-col>
                                            <v-col cols="6">
                                                <v-select color="teal" v-model="signin_form.sp" label="PA"
                                                    :rules="getRules({ required: true })" :readonly="loading || alert"
                                                    :items="[{ title: 'Matriz', value: 0 }, { title: 'PA-01', value: 1 }, { title: 'PA-02', value: 2 }, { title: 'UAD', value: 88 }, { title: 'UPS', value: 89 }, { title: 'PA-97', value: 97 }]"></v-select>
                                            </v-col>
                                            <v-col cols="6">
                                                <v-text-field color="teal" v-model="signin_form.password"
                                                    :readonly="loading || alert"
                                                    :rules="getRules({ required: true, minlen: { val: 5 }, maxlen: { val: 30 }, hasLowercase: true, hasUppercase: true, hasNumber: true, hasSpecialchar: true })"
                                                    clearable label="Senha" placeholder="Digite sua senha" counter
                                                    :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                                                    :type="visible ? 'text' : 'password'"
                                                    prepend-inner-icon="mdi-lock-outline"
                                                    @click:append-inner="visible = !visible"
                                                    @keyup.enter="(!loading && !alert) && signin()"></v-text-field>
                                            </v-col>
                                            <v-col cols="6">
                                                <v-text-field color="teal" v-model="signin_form.password_confirmation"
                                                    :readonly="loading || alert"
                                                    :rules="getRules({ required: true, equals: { val: signin_form.password, message: 'As senhas não se correspondem' } })"
                                                    clearable label="Confirme sua senha" placeholder="Repita sua senha"
                                                    counter :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                                                    :type="visible ? 'text' : 'password'"
                                                    prepend-inner-icon="mdi-lock-outline"
                                                    @click:append-inner="visible = !visible"
                                                    @keyup.enter="(!loading && !alert) && signin()"></v-text-field>
                                            </v-col>
                                        </v-row>
                                    </v-form>
                                </v-card-text>
                                <v-divider></v-divider>
                                <v-card-actions style="position: relative">
                                    <v-slide-x-reverse-transition>
                                        <v-alert height="35" v-show="alert" :color="alert_fail ? 'red' : 'green'" border="start" elevation="3" dark
                                            class="mx-2"><v-icon size="small"
                                                class="mr-2 ml-0 pl-0">{{ alert_fail ? 'mdi-alert-circle' : 'mdi-emoticon-wink'}}</v-icon>{{ alert_message
                                                }}</v-alert>
                                    </v-slide-x-reverse-transition>
                                    <v-hover>
                                        <template v-slot:default="{ isHovering, props }">
                                            <v-btn v-if="!alert" v-bind="props" :variant="isHovering ? 'tonal' : 'outlined'"
                                                style="position: absolute; right: 20px" :loading="loading" color="teal"
                                                @click="signin()">Cadastrar</v-btn>
                                        </template>
                                    </v-hover>
                                </v-card-actions>
                            </v-card>
                        </v-window-item>
                    </v-window>
                </v-card>
            </v-scale-transition>
        </div>
        <v-snackbar v-model="snackbar" :color="snack_bar_success ? 'blue-darken-3' : 'orange-darken-3'">
            <v-icon :color="snack_bar_success ? 'white' : 'pink'" class="mr-2">{{ snack_bar_success ? 'mdi-email-check' : 'mdi-alert' }}</v-icon>
            <span class="text-shadow-black-1">{{ snackbar_text }}</span>
            <template v-slot:actions>
                <v-btn :color="snack_bar_success ? 'white' : 'pink'" variant="text" icon="mdi-close" @click="snackbar = false"></v-btn>
            </template>
        </v-snackbar>
        <DialogResetPassword :model="reset_password_dialog" @close="reset_password_dialog = false" @done="doneSendingEmail()" />        
    </div>
</template>

<script setup>
import api from '@/plugins/axios.js'
import { ref, reactive, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { useTheme } from 'vuetify'
import Particles from '@/components/Particles.vue'
import DialogResetPassword from '@/components/Dialogs/DialogResetPassword.vue'
import { useRoute } from 'vue-router'
import CryptoJS from 'crypto-js'

const route = useRoute()
const use_theme = useTheme()
use_theme.global.name.value = 'customDark'
const form0 = ref(null)
const form1 = ref(null)
const form2 = ref(null)
const auth = useAuthStore()
const login_form = reactive({ email: '', password: '' })
let signin_form = reactive({ name: '', cpf: '', email: '', group_id: '', sp: '', password: '', password_confirmation: '' })
let reset_password_form = reactive({ uid: null, token: null, password: '', password_confirmation: '' })
const tabs = ref(1)
const loading = ref(false)
const loading_groups = ref(false)
const groups = reactive([])
const visible = ref(false)
const alert_message = ref('')
const alert = ref(false)
const alert_fail = ref(true)
const start = ref(false)
const logo_animation = ref(false)
const end_animation = ref(false)
const loading_verify_password_reset_token = ref(false)
const reset_password_ui = ref(false)
const snackbar = ref(false)
const snackbar_text = ref('')
const reset_password_dialog = ref(false)
const snack_bar_success = ref(false)
const show_otp_form = ref(false)
const otp_form = reactive({ id: '', name: '', email: '', otp: '' })
const disable_resend_otp = ref(false)
const disable_resend_otp_timer = ref(60)
const disable_resend_otp_interval = ref(null)
const loading_resend_otp = ref(false)

//Mounted
onMounted(() => {
    if (route.params.reset_password_uid && route.params.reset_password_token) {
        verifyPasswordResetToken()
    }
})

//BeforeUnmount
onBeforeUnmount(() => {
    clearInterval(disable_resend_otp_interval.value)
})

//Computeds
const otp_form_otp = computed(() => otp_form?.otp || '')

//Watchers
watch(start, (v) => {
    if (v) {
        setTimeout(() => { logo_animation.value = true }, 300)
        setTimeout(() => { end_animation.value = true }, 2000)
    }
})

watch(otp_form_otp, (v) => {
    if (v?.length == 6 && show_otp_form.value) {
        checkOtpNLogin()
    }
})

//Created
setTimeout(() => { start.value = true }, 300)
getGroups()

//Methods
function resendOtp() {
    loading_resend_otp.value = true
    api.post('resend_otp', otp_form).then((response) => {
        loading_resend_otp.value = false
        alert_message.value = response?.data?.message 
        alert_fail.value = false
        alert.value = true                        
        setTimeout(() => { alert.value = false }, 4000) 
        setTimeout(() => { alert_fail.value = true }, 4300)
        disableResendOtp()
    }).catch((err) => {
        console.log(err)
        alert_message.value = err?.response?.data?.message || 'Ocorreu um erro, tente novamente'
        alert.value = true
        setTimeout(() => { alert.value = false }, 4000)
        loading_resend_otp.value = false
    })
}

function disableResendOtp() {
    disable_resend_otp.value = true   
    disable_resend_otp_timer.value = 60        
    disable_resend_otp_interval.value = setInterval(() => { 
        if (disable_resend_otp_timer.value > 0) {
            disable_resend_otp_timer.value-- 
        } else {
            clearInterval(disable_resend_otp_interval.value)
            disable_resend_otp.value = false
        }            
    }, 1000)
}

async function resetPasswordNLogin(attempts = 1) {
    const { valid } = await form0.value.validate()
    if (!valid) return false
    loading.value = true
    let reset_password_form_copy = { ...reset_password_form }
    reset_password_form_copy.password = encryptPassword(reset_password_form_copy.password)
    delete reset_password_form_copy['password_confirmation']
    api.post('reset_password_n_login', reset_password_form_copy).then((response) => {
        auth.login(response.data.user, response.data.token)
    }).catch((err) => {
        console.log(err)
        if (err.response?.status === 400 || err.response?.status === 402) { 
            if (err.response?.status === 400) {
                reset_password_ui.value = false
                reset_password_form = reactive({ uid: null, token: null, password: '', password_confirmation: '' })
                reset_password_form_copy = { ...reset_password_form }
            }            
            loading.value = false            
            snack_bar_success.value = false            
            snackbar_text.value = err.response.data.message
            snackbar.value = true
        } else if (attempts < 5) {
            setTimeout(() => resetPasswordNLogin(attempts + 1), 1000)
        } else {
            alert_message.value = 'Ocorreu um erro, tente novamente.'
            loading.value = false            
            alert.value = true
            setTimeout(() => { alert.value = false }, 4000)
        }        
    })
}

function doneSendingEmail() {
    snack_bar_success.value = true
    reset_password_dialog.value = false
    snackbar_text.value = 'Se o usuário existir e estiver ativo, o e-mail foi enviado'
    snackbar.value = true
}

function verifyPasswordResetToken(attempts = 1) {
    loading_verify_password_reset_token.value = true
    api.post('verify_password_reset_token', { uid: route.params.reset_password_uid, token: route.params.reset_password_token }).then((response) => {        
        Object.assign(reset_password_form, response.data)
        reset_password_ui.value = true
        loading_verify_password_reset_token.value = false
        snack_bar_success.value = true
        snackbar_text.value = 'Token válido, redefina sua senha. (Deve ser diferente das 5 últimas)'     
        snackbar.value = true
    }).catch((err) => {
        snack_bar_success.value = false
        if (err.response?.status === 400 || err.response?.status === 404) {
            snackbar_text.value = err.response.data.message        
            loading_verify_password_reset_token.value = false
            snackbar.value = true
        } else if (attempts < 5) {
            console.log(err)
            setTimeout(() => verifyPasswordResetToken(attempts + 1), 1000)
        } else {
            snackbar_text.value = 'Erro de conexão. Atualize a página e cheque o status do servidor.'
            loading_verify_password_reset_token.value = false
            snackbar.value = true
        }        
    })
}

function encryptPassword(password) {
    const key = CryptoJS.enc.Utf8.parse('My$uperSecretKey3267567890AyCjEF') 
    const iv = CryptoJS.enc.Utf8.parse('MyInitVector3267') 
    const encrypted = CryptoJS.AES.encrypt(password, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 })
    return encrypted.toString()
}

async function sendOtp() {    
    const { valid } = await form1.value.validate()
    if (!valid) return false
    loading.value = true
    let login_form_copy = { ...login_form }
    login_form_copy.password = encryptPassword(login_form_copy.password)
    api.post('send_otp', login_form_copy)
        .then(response => {
            if (response?.data?.id) {
                Object.assign(otp_form, response.data)
                disableResendOtp()
                show_otp_form.value = true            
            } else {
                alert_message.value = 'Ocorreu um erro, tente novamente'
                alert.value = true
                setTimeout(() => { alert.value = false }, 4000)
            }
            loading.value = false
        }).catch(err => {
            console.log(err)
            alert_message.value = err?.response?.data?.message || 'Ocorreu um erro, tente novamente'
            alert.value = true
            setTimeout(() => { alert.value = false }, 4000)
            loading.value = false
        })
}

function checkOtpNLogin() {    
    loading.value = true
    api.post('check_otp_n_login', otp_form)
        .then(response => {
            auth.login(response.data.user, response.data.token)
        }).catch(err => {
            console.log(err)
            alert_message.value = err?.response?.data?.message || 'Ocorreu um erro, tente novamente'
            alert.value = true
            setTimeout(() => { alert.value = false }, 4000)
            loading.value = false
        })
}

function getGroups(attempt = 1) {
    loading_groups.value = true
    api.get('get_groups').then((response) => {
        Object.assign(groups, response.data)
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

function setEmail(full_name) {
    if (!/^[A-Z][\p{L}\s´~]* [A-Z][\p{L}\s´~]*$/u.test(full_name)) return
    let full_name_array = full_name.trim().toLowerCase().split(' ')
    signin_form.email = full_name_array[0] + '.' + full_name_array[full_name_array.length - 1]
}

async function signin() {
    const { valid } = await form2.value.validate()
    if (!valid) return false
    loading.value = true
    signin_form.cpf = signin_form.cpf.replace(/\D/g, '')
    let signin_form_copy = { ...signin_form }
    signin_form_copy.password = encryptPassword(signin_form_copy.password)
    delete signin_form_copy['password_confirmation']
    api.post('signin', signin_form_copy)
        .then(response => {
            loading.value = false
            alert_message.value = response?.data?.message
            alert_fail.value = false
            alert.value = true                        
            setTimeout(() => { alert.value = false }, 5000) 
            setTimeout(() => { 
                tabs.value = 1
                alert_fail.value = true 
                signin_form = reactive({ name: '', cpf: '', email: '', group_id: '', sp: '', password: '', password_confirmation: '' })
                signin_form_copy = { ...signin_form }
                form2.value.reset()
            }, 5300)                                                           
        })
        .catch(err => {
            console.log(err)
            alert_message.value = err?.response?.data?.message || 'Ocorreu um erro, tente novamente'
            alert.value = true
            setTimeout(() => { alert.value = false }, 4000)
            loading.value = false
        })
}

</script>

<style scoped>
.login-toolbar {
    background: linear-gradient(to right, #414345, #232526);
    /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
    box-shadow: rgba(0, 0, 0, 0.17) 0px -23px 25px 0px inset, rgba(0, 0, 0, 0.15) 0px -36px 30px 0px inset, rgba(0, 0, 0, 0.1) 0px -79px 40px 0px inset, rgba(0, 0, 0, 0.06) 0px 2px 1px, rgba(0, 0, 0, 0.09) 0px 4px 2px, rgba(0, 0, 0, 0.09) 0px 8px 4px, rgba(0, 0, 0, 0.09) 0px 16px 8px, rgba(0, 0, 0, 0.09) 0px 32px 16px;
}

.background-div1 {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('media/images/login_wallpapers/lazy.jpg');
    background-image: url('../media/images/login_wallpapers/lazy.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.login-card {
    box-shadow: rgba(37, 78, 73, 0.171) 0px 2px 9px, rgba(41, 88, 88, 0.329) 5px 7px 63px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
    transition: 0.6s;
}

.login-card:hover {
    box-shadow: rgba(17, 27, 26, 0.753) 10px 18px 19px, rgba(38, 70, 71, 0.664) 5px 7px 63px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
}

.animation-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.gradient-text {
    color: transparent;
    background: linear-gradient(45deg, #00ffb3, #0004f7);
    background-clip: text;
    -webkit-background-clip: text;
    text-shadow: 1px 1px  rgba(0, 0, 0, 0.192);
}

.logo-container {
    transition: 1s;
    animation: moveLogo 1.5s forwards;
}

.text-animation {
    opacity: 0;
    animation: moveText 1.5s forwards;
    animation-delay: 0.5s;
}


@keyframes moveText {
    0% {
        transform: translateX(0);
    }

    100% {
        opacity: 1;
        transform: translateX(55px);
    }
}

@keyframes moveLogo {
    0% {
        transform: translateX(0);
    }

    30% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-55px);
    }
}
</style>