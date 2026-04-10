<template>
    <div>
        <v-alert border="start" color="yellow-lighten-1" variant="tonal" align="center" class="pa-0">
            <v-row style="height: 350px">
                <v-col cols="12">
                    <v-img src="media/images/404.png" width="512"></v-img>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <strong style="font-size: 20px; color: rgb(202, 132, 40)" class="text-decoration-underline">Ops, rota inválida.</strong>
                    <br>
                    <span>Assim como o mundial do palmeiras, essa página não existe. Tente a barra de navegação ou clique
                        no
                        botão abaixo.</span>
                </v-col>
            </v-row>
            <br>
            <v-btn class="elevated mt-0 mb-6" to="/" elevation="10" prepend-icon="mdi-home" color="orange">Início</v-btn>
            <br>
            <v-divider class="pt-5"></v-divider>
            <v-row class="pb-5">
                <v-col cols="12">
                    <span style="font-weight:900; color: rgb(221, 245, 248); font-size: 14px;">Projeto iniciado em 04/2026
                        AgriFlow - São Gotardo</span>
                </v-col>
            </v-row>


        </v-alert>
    </div>
</template>
<script setup>
import { ref, reactive, computed } from 'vue'
import { useTheme } from 'vuetify'
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
</script>