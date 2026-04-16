<template>
    <div>
        <v-card flat>
            <v-card-title class="text-h5 font-weight-bold mb-4">
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                {{ title }}
            </v-card-title>

            <v-divider class="mb-4"></v-divider>

            <v-card-text>
                <div class="content-section">
                    <p>This is the {{ title }} module. Add your content here.</p>
                </div>
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

// Computeds

// Created

// Methods

</script>

<style scoped>
.content-section {
    padding: 10px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.05);
}
</style>