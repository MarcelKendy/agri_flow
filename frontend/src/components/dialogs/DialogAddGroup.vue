<template>
  <div>
    <v-dialog v-model="props.model" persistent width="800px">
      <v-card :loading="loading" min-height="">
        <template v-slot:loader="{ isActive }">
          <v-progress-linear :active="isActive" :color="color" height="7" indeterminate></v-progress-linear>
        </template>
        <v-card-title style="position: relative" v-if="!loading">
          <v-row>
            <v-col cols="11" class="mt-1">
              <v-row>
                <v-col cols="1" style="margin-right: -5px">
                  <v-img v-if="$route.meta.img" width="32" :src="'media/icons/' + $route.meta.img"></v-img>
                  <v-icon v-else  class="mr-0" :color="color">{{ $route.meta.icon }}</v-icon>
                </v-col>
                <v-col cols="11" class="pl-0">
                  <span :style="'color:' + color">Adicionar Grupo</span>
                </v-col>
              </v-row>
            </v-col>
            <v-col cols="1">
              <v-img style="width: 32px; position:absolute; top: 20px" src="media/icons/sicoobicon.png" />
            </v-col>
          </v-row>
        </v-card-title>
        <v-card-title class="pa-3" v-else>
          <strong :style="'color:' + color">Salvando</strong>
          <v-img max-width="50" class="loading-gif" src="media/images/loading.gif" />
        </v-card-title>
        <v-card-subtitle>
          <span v-if="loading">Inserindo grupo</span>
          <span v-else>Defina o novo grupo</span>
        </v-card-subtitle>
        <v-divider class="mt-2"></v-divider>
        <v-scroll-x-transition>
          <v-card-text v-if="loading">
            <v-row align="center" justify="center" class="ma-1 mt-0">
              <v-col cols="12">
                <v-alert color="success" border="start" elevation="3" dark icon="mdi-content-save">
                  <span>Espera só um pouquinho, carregando...</span>
                </v-alert>
              </v-col>
            </v-row>
          </v-card-text>
        </v-scroll-x-transition>
        <v-card-text class="mb-5" v-if="!loading">
          <v-form ref="form">
            <v-row>
              <v-col cols="12">
                <v-text-field clearable :color="color" label="Nome"
                  :rules="getRules({ required: true, minlen: { val: 3 } })" v-model="item.name"></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-divider v-if="!loading"></v-divider>
        <v-card-actions class="pa-3" v-if="!loading">
          <v-spacer></v-spacer>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" color="red" :variant="isHovering ? 'tonal' : 'outlined'" @click="closeDialog()">
                Cancelar
              </v-btn>
            </template>
          </v-hover>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" :color="color" :variant="isHovering ? 'tonal' : 'elevated'" @click="addGroup()">
                Confirmar
              </v-btn>
            </template>
          </v-hover>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
//Imports
import { ref, watch, reactive, computed } from 'vue'
import api from '@/plugins/axios.js'

// Variables
const emit = defineEmits(['close', 'new_register'])
const loading = ref(false)
const props = defineProps({
  color: { type: String, default: 'green' },
  model: { type: Boolean, required: true },
})
let item = reactive({
  name: ''
})
const form = ref(null)

//Computeds
const model_computed = computed(() => props.model)

//Watchers
watch(model_computed, (v) => {
  if (v) {
    item = reactive({
      name: ''
    })
  }
})

//Methods
async function addGroup() {
  const { valid } = await form.value.validate()
  if (!valid) return false
  loading.value = true
  api.post('add_group', item).then((response) => {
    emit('new_register', response.data)
    closeDialog()
  }).catch(error => {
    console.log(error)
    closeDialog()
  })
}

function closeDialog() {
  emit('close')
  setTimeout(() => { loading.value = false }, 300)
}

</script>
