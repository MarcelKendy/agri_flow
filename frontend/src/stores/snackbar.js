import { defineStore } from 'pinia'
export const useSnackbarStore = defineStore('snackbar', {
    state: () => ({
        show: false,
        text: '',
        color: '',
        timeout: 3000,
        location: 'bottom',
        variant: 'elevated',
        prependIcon: '',
        timer: false,
        timerColor: 'white'
    }),
    actions: {
        getPreset(presetName) {
            const presets = {
                success: {
                    text: 'Requisição completada com sucesso!',
                    color: 'green',
                    prependIcon: 'mdi-circle-check'
                },
                error: {
                    text: 'Ocorreu um erro, tente novamente.',
                    color: 'red',
                    prependIcon: 'mdi-server-network-off'
                },
                alert: {
                    color: 'orange',
                    prependIcon: 'mdi-alert'
                },
                info: {
                    color: 'blue',
                    prependIcon: 'mdi-information'
                }
            }
            return presets[presetName] || {}
        },
        open(options = {}) {
            const presetConfig = options.preset ? this.getPreset(options.preset) : {}
            const merged = { ...this.$state, ...presetConfig }
            const final = { ...merged, ...options }
            delete final.preset
            Object.assign(this.$state, final)
            this.show = true
        },
        close() {
            this.show = false
        }
    }
})