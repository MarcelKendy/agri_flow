import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'
import api from '@/plugins/axios.js'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('templateapp_token'))
    const user = ref(JSON.parse(localStorage.getItem('templateapp_user')))    
    const is_auth = ref(false)
    const router = useRouter()

    function setToken(token_value) {
        localStorage.setItem('templateapp_token', token_value)
        token.value = token_value
    }

    function setUser(user_value) {
        localStorage.setItem('templateapp_user', JSON.stringify(user_value))
        user.value = user_value
    }

    function setUserAttributes(attributes) {
        Object.assign(user.value, attributes)
        localStorage.setItem('templateapp_user', JSON.stringify(user.value))
    }

    function setIsAuth(auth = true) {
        is_auth.value = auth
    }

    function login(user, token) {
        setUser(user)
        setToken(token)
        setIsAuth()
        router.push({ name: 'dashboard' })
    }

    async function validateSession() {
        try {
            const { data } = await api.get('auth', { headers: { Authorization: 'Bearer ' + token.value }, params: { user: user.value } })
            return data
        } catch (error) {
            clear()
            console.log(error.response.data)
            return false
        }
    }

    function hasData() {
        return (token.value != null && user.value != null && localStorage.getItem('templateapp_token') != null && localStorage.getItem('templateapp_user') != null)
    }

    function clear() {
        router.push({ name: 'login' })
        api.post('delete_session', { token: token.value, user_id: user.value.id }).catch(error => console.log(error))
        is_auth.value = false
        localStorage.removeItem('templateapp_token')
        localStorage.removeItem('templateapp_user')
        token.value = ''
        user.value = ''
    }

    return { token, user, is_auth, setToken, setUser, setUserAttributes, setIsAuth, validateSession, hasData, login, clear }
})