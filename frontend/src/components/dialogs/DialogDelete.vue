<template>
  <div>
    <v-dialog v-model="props.model" persistent width="800px">
      <v-card :loading="loading" min-height="">
        <template v-slot:loader="{ isActive }">
          <v-progress-linear :active="isActive" :color="color" height="7" indeterminate></v-progress-linear>
        </template>
        <v-card-title
          v-if="!loading"
          class="py-3"
          style="position: relative"
        >
          <v-row no-gutters>
            <v-col
              cols="10"
              class="d-flex align-left"
            >
              <div class="mr-2">
                <v-img
                  v-if="img"
                  width="32"
                  height="32"
                  :src="'media/icons/' + img"
                />
                <v-icon
                  v-else
                  :color="color"
                  size="32"
                >
                  {{ icon }}
                </v-icon>
              </div>

              <span class="bold" :style="{ color }">
                Deletar Registro
              </span>
            </v-col>            
            <v-col
              cols="2"
              class="d-flex justify-end"
            >
              <v-img
                src="media/icons/logo.png"
                width="32"
                height="32"
              />
            </v-col>
          </v-row>
        </v-card-title>
        <v-card-title class="pa-3" v-else>
          <strong :style="'color:' + color">Salvando</strong>
          <v-img max-width="50" class="loading-gif" src="media/images/loading.gif" />
        </v-card-title>
        <v-card-subtitle>
          <span v-if="loading">Excluindo registro...</span>
          <span v-else>Tem certeza que deseja excluir permanentemente esse registro?</span>
        </v-card-subtitle>
        <v-divider class="mt-2"></v-divider>
        <v-card-text>
          <v-row align="center" justify="center" class="ma-1 mt-0">
            <v-col cols="12">
              <v-alert style="color: white" :color="color" border="start" elevation="3" dark icon="mdi-delete-alert">
                <v-scroll-x-transition>
                  <span v-show="loading">Espera só um pouquinho, carregando...</span>
                </v-scroll-x-transition>
                <div v-if="!loading">
                  <template v-if="info && info.length">
                    <div v-for="row in info" :key="row.key">
                      <span class="font-italic">{{ row.title }}: </span> {{ row.key.split('.').reduce((value, key) => value?.[key], data) }}
                    </div>
                  </template>
                  <template v-else>
                    <div>
                      <span class="font-italic">{{ name_title }}: </span> {{ data[name_key] }}
                    </div>
                  </template>
                  <div v-if="created_at">
                    <span class="font-italic">Data de Cadastro:</span> {{ formatDate(data.created_at) }}
                  </div>
                </div>
              </v-alert>
            </v-col>
          </v-row>
        </v-card-text>
        <v-divider v-if="!loading"></v-divider>
        <v-card-actions class="pa-3" v-if="!loading">
          <v-spacer></v-spacer>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" color="blue" :variant="isHovering ? 'tonal' : 'outlined'" @click="closeDialog()">
                Cancelar
              </v-btn>
            </template>
          </v-hover>
          <v-hover>
            <template v-slot:default="{ isHovering, props }">
              <v-btn v-bind="props" :color="color" :variant="isHovering ? 'tonal' : 'elevated'" @click="deleteItem()">
                <span>Confirmar</span>
              </v-btn>
            </template>
          </v-hover>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/plugins/axios.js'
import { useSnackbarStore } from '@/stores/snackbar'

const emit = defineEmits(['close', 'deleted'])
const loading = ref(false)
const snackbar = useSnackbarStore()
const props = defineProps({
  data: { type: Object, required: true },
  data_name: { type: String, required: true },
  name_key: { type: String, default: 'name' },
  name_title: { type: String, default: 'Nome' },
  created_at: { type: Boolean, default: true },
  info: { type: Array, default: () => [] },
  color: { type: String, default: 'red' },
  icon: { type: String, required: true },
  img: { type: String },
  model: { type: Boolean, required: true },
})

async function deleteItem() {
  loading.value = true
  api.delete('delete_' + props.data_name + '/' + props.data.id).then((response) => {
    emit('deleted', props.data.id)
    closeDialog()
  }).catch(error => {
    console.log(error)
    snackbar.open({ preset: 'error' })
    closeDialog()
  })
}

function closeDialog() {
  emit('close')
  setTimeout(() => { loading.value = false }, 300)
}

function formatDate(input_date) {
  const date = new Date(input_date);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${day}/${month}/${year} ${hours}:${minutes}`;
}
</script>