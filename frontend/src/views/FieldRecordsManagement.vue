<template>
    <div>
        <v-sheet :elevation="smAndDown ? 0 : 1" :class="smAndDown ? 'mx-2' : ''" style="background-color: rgba(0, 0, 0, 0);">
            <v-tabs v-model="tab" :class="dark_theme ? 'tabs-dark' : 'tabs-light'" color="green" grow :show-arrows="smAndDown" :mobile="smAndDown"
                :center-active="smAndDown">                
                <v-tab v-for="item in items" :key="item.id" :value="item.id" :prepend-icon="item.icon"
                    style="border-radius: 10px;" :style="smAndDown ? 'font-size: 12px' : ''">
                    {{ item.title }}
                </v-tab>
            </v-tabs>
            <v-tabs-window v-model="tab">
                <v-tabs-window-item v-for="item in items" :key="item.id" :value="item.id">
                    <v-card flat :style="smAndDown ? 'background-color: rgba(0, 0, 0, 0);': ''">
                        <v-card-text :style="smAndDown ? 'padding: 0px; padding-top: 10px;' : ''">
                            <keep-alive>
                                <component :is="item.componentInstance" :title="item.title" :icon="item.icon" />
                            </keep-alive>
                        </v-card-text>
                    </v-card>
                </v-tabs-window-item>
            </v-tabs-window>
        </v-sheet>
    </div>
</template>

<script setup>
// Imports
import api from '@/plugins/axios.js'
import { useAuthStore } from '@/stores/auth.js'
import { ref, computed, defineAsyncComponent } from 'vue'
import { useTheme, useDisplay } from 'vuetify'

// Variables
const { smAndDown } = useDisplay()
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value === 'customDark')
const auth = useAuthStore()
const tab = ref(1)

const items = [
    {
        id: 1,
        icon: 'mdi-sprout',
        text: 'Plantings',
        title: 'Plantios',
        component: 'Plantings',
        componentInstance: defineAsyncComponent(() => import('@/components/Plantings.vue'))
    },
    {
        id: 2,
        icon: 'mdi-file-document-multiple',
        text: 'Field Records',
        title: 'Fichas de Campo',
        component: 'FieldRecords',
        componentInstance: defineAsyncComponent(() => import('@/components/FieldRecords.vue'))
    },
    {
        id: 3,
        icon: 'mdi-seed',
        text: 'Crops',
        title: 'Culturas',
        component: 'Crops',
        componentInstance: defineAsyncComponent(() => import('@/components/Crops.vue'))
    },
    {
        id: 4,
        icon: 'mdi-flask-outline',
        text: 'Products',
        title: 'Produtos',
        component: 'Products',
        componentInstance: defineAsyncComponent(() => import('@/components/Products.vue'))
    },
    {
        id: 5,
        icon: 'mdi-water-pump',
        text: 'Pivots',
        title: 'Pivôs',
        component: 'Pivots',
        componentInstance: defineAsyncComponent(() => import('@/components/Pivots.vue'))
    },
    {
        id: 6,
        icon: 'mdi-tractor',
        text: 'Tractors',
        title: 'Tratores',
        component: 'Tractors',
        componentInstance: defineAsyncComponent(() => import('@/components/Tractors.vue'))
    },
    {
        id: 7,
        icon: 'mdi-cog-outline',
        text: 'Implements',
        title: 'Implementos',
        component: 'Implements',
        componentInstance: defineAsyncComponent(() => import('@/components/Implements.vue'))
    }
]

</script>

<style>
.page-title-section {
    margin: 5px;
    margin-top: 80px;
    position: relative;
}

.tabs-dark {
    background-color: rgba(52, 66, 56, 0.507);
    border-radius: 6px;
}

.tabs-light {
    background-color: rgba(243, 255, 216, 0.979);
    border-radius: 6px;
}

</style>