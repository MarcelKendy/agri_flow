<template>
  <div>
    <v-autocomplete :readonly="readonly" :disabled="disabled" :loading="loading" :clearable="!readonly"
      auto-select-first :color="color" :rules="getRules({ required: true })" @click.stop density="compact"
      no-data-text="Não encontrado..." v-model="model" style="padding-bottom: 0px !important" item-title="name"
      item-value="id" :items="items" variant="underlined"></v-autocomplete>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const emit = defineEmits(['change'])
const model = ref(props.model)
const props = defineProps({
  items: { type: Array, required: true },
  model: { type: Number, required: true },
  color: { type: String, required: true },
  loading: { default: false },
  disabled: { default: false },
  readonly: { default: false }
})

watch(model, (new_value, old_value) => {
  new_value ? emit('change', new_value) : null
})

</script>
