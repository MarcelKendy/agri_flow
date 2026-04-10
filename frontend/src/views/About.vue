<template>
    <div>
        <v-alert border="start" :color="dark_theme ? 'cyan-lighten-1': 'cyan-lighten-4'" :variant="dark_theme ? 'tonal' : 'elevated'" elevation="15" class="pa-5" align="center">
            <v-img src="media/icons/logo.png" :max-width="'412'"></v-img>
            <strong style="font-size: 20px">Sobre o Credisg Campanhas</strong>
            <br>
            <span>App desenvolvido para o acompanhamento de campanhas. Em caso de dúvidas ou
                solicitações, contate o desenvolvedor.</span>
            <br>
            <span style="font-weight:900; font-size: 14px;" :style="dark_theme ? 'color: rgb(221, 245, 248)' : 'color: black'">Projeto iniciado em set/2023 - IT
                Team - Sicoob Credisg - São Gotardo</span>
        </v-alert>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useTheme } from 'vuetify'
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
</script>