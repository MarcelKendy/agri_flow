<template>
  <div>
    <EquipmentSection title="Pivôs" icon="mdi-water-pump" api_name="pivot" count_field="plantings_count"
      :add_dialog_component="DialogAddPivot" :edit_dialog_component="DialogEditPivot" :active_tab="active_tab" />

    <v-divider v-if="!smAndDown" class="mb-6" :color="dark_theme ? 'rgba(200, 200, 200)' : 'rgba(0, 0, 0)'"
      opacity="0.5" />

    <EquipmentSection title="Tratores" icon="mdi-tractor" api_name="tractor" count_field="field_records_count"
      :add_dialog_component="DialogAddTractor" :edit_dialog_component="DialogEditTractor" :active_tab="active_tab" />

    <v-divider v-if="!smAndDown" class="mb-6" :color="dark_theme ? 'rgba(200, 200, 200)' : 'rgba(0, 0, 0)'"
      opacity="0.5" />

    <EquipmentSection title="Implementos" icon="mdi-cog-outline" api_name="implement" count_field="field_records_count"
      :add_dialog_component="DialogAddImplement" :edit_dialog_component="DialogEditImplement"
      :active_tab="active_tab" />
  </div>
</template>

<script setup>
// Imports
import { computed, defineProps, defineEmits } from 'vue'
import { useTheme, useDisplay } from 'vuetify'
import EquipmentSection from '@/components/EquipmentSection.vue'

import DialogAddPivot from '@/components/dialogs/DialogAddPivot.vue'
import DialogEditPivot from '@/components/dialogs/DialogEditPivot.vue'

import DialogAddTractor from '@/components/dialogs/DialogAddTractor.vue'
import DialogEditTractor from '@/components/dialogs/DialogEditTractor.vue'

import DialogAddImplement from '@/components/dialogs/DialogAddImplement.vue'
import DialogEditImplement from '@/components/dialogs/DialogEditImplement.vue'

defineProps({
  active_tab: Boolean
})

// Variables
const use_theme = useTheme()
const { smAndDown } = useDisplay()

// Computeds
const dark_theme = computed(() => use_theme.global.name.value == 'customDark')
</script>