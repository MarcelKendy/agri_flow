<template>
    <div>
        
    </div>
</template>

<script setup>
import api from '@/plugins/axios.js'
import { ref, reactive, computed, watch, onBeforeUnmount } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { useTheme } from 'vuetify'
const use_theme = useTheme()
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')

// Variables
const auth = useAuthStore()


</script>
<style>
.dashboard-toolbar {
    background-image: url('media/images/backgrounds/transparentbg.png');
    background-size: cover !important;
    background-position: center !important;
    padding-left: 15px !important;
    padding-right: 5px !important;
}

.custom-switch .v-switch__thumb {
  width: 40px !important;
  height: 40px !important;
  font-size: 25px !important;
  background-color: rgba(255, 255, 255, 0) !important;
  box-shadow: none !important;
}

.custom-switch .v-switch__track {
  opacity: 1 !important; 
  background-color: #ff9800 !important;
}

.custom-switch i {
  opacity: 1 !important;
  text-shadow: 1px 1px rgb(0, 0, 0);
  color: rgb(255, 229, 204) !important;
}

</style>