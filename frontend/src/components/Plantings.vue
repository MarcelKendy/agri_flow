<template>
    <div>
        <v-card flat style="background-color: rgba(0, 0, 0, 0)">
            <v-card-title style="position: relative;" class="bold" :class="dark_theme ? 'text-shadow-black-2' : ''"
                :style="smAndDown
                    ? (dark_theme
                        ? 'background-color: rgba(90, 90, 90, 0.2)'
                        : 'background-color: rgba(150, 150, 150, 0.2)')
                    : ''">
                <v-icon :color="color" class="mr-2">{{ icon }}</v-icon>
                <span class="mr-10">{{ title }}</span>

                <v-btn @click="add_dialog = true" color="green" :size="smAndDown ? 'small' : 'default'"
                    append-icon="mdi-plus" class="bold new-button">
                    NOVO
                </v-btn>
            </v-card-title>

            <v-divider :thickness="7" class="border-opacity-25 mb-4" color="green"></v-divider>
            <v-expand-transition>
                <v-row class="align-center" v-if="pendingFieldRecordsAlert && pending_alert">
                    <v-col cols="12">
                        <v-alert
                            :color="pendingFieldRecordsAlert.color"
                            variant="tonal"
                            border="start"                        
                            :icon="pendingFieldRecordsAlert.icon"
                            class="alert-pending-records"
                            @click="goToFieldRecordsTab"
                        >   
                            <div><strong style="font-size: 20px;">Alertas</strong></div>
                            <div class="font-italic"><strong>Fichas pendentes:</strong></div>
                            <div class="d-flex align-center flex-wrap ga-2 w-100 mb-1 mt-2">                            
                                
                                <v-chip
                                    v-if="pendingFieldRecordsAlert.today > 0"
                                    color="orange"
                                    variant="elevated"
                                    size="small"
                                >
                                    Hoje! 
                                    <template #append>
                                        <v-avatar end>
                                            {{ pendingFieldRecordsAlert.today }}
                                        </v-avatar>
                                    </template>
                                </v-chip>
                                <v-chip
                                    v-if="pendingFieldRecordsAlert.late > 0"
                                    color="red"
                                    variant="elevated"
                                    size="small"
                                >
                                    Atrasadas
                                    <template #append>
                                        <v-avatar end>
                                            {{ pendingFieldRecordsAlert.late }}
                                        </v-avatar>
                                    </template> 
                                </v-chip>
                            </div>
                            <v-btn icon="mdi-close" size="x-small" @click.stop="pending_alert = false" :color="pendingFieldRecordsAlert.color" style="position: absolute; right: 10px; top: 10px;"></v-btn>
                        </v-alert>
                    </v-col>
                </v-row>      
            </v-expand-transition>                  
            <v-card-text>
                <v-row v-if="items_length" class="align-center">
                    <v-col cols="12" class="d-flex ga-2">
                        <v-btn @click="filter_active = !filter_active" color="teal" prepend-icon="mdi-filter"
                            :disabled="loading || !items_length"
                            :append-icon="filter_active ? 'mdi-eye-off' : 'mdi-eye'" size="small">
                            {{ filter_active ? 'Esconder filtros' : 'Exibir filtros' }}
                        </v-btn>
                        <v-btn v-if="has_active_filters" @click="clearFilters" color="warning" size="small"
                            prepend-icon="mdi-filter-remove">
                            Limpar filtros
                        </v-btn>
                    </v-col>
                    <v-expand-transition>
                        <v-col cols="12" v-if="items_length && filter_active">
                            <div :class="dark_theme ? 'filter-section-dark' : 'filter-section-light'">
                                <div class="bold mb-5" style="font-size: 15px;">
                                    <v-icon>mdi-filter</v-icon>
                                    Filtros
                                </div>

                                <v-row>
                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.crops" label="Culturas" :items="crops" multiple chips
                                            item-title="name" item-value="id" closable-chips clearable variant="solo"
                                            color="teal" :loading="loading_crops" :disabled="loading_crops"
                                            no-data-text="Nenhum dado cadastrado..." prepend-icon="mdi-seed" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.varieties" label="Variedades" :items="varieties"
                                            multiple chips closable-chips clearable variant="solo" color="teal"
                                            :loading="loading_crops" :disabled="loading_crops"
                                            no-data-text="Nenhum dado cadastrado..." prepend-icon="mdi-flower-tulip" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.pivots" label="Pivôs" :items="pivots" multiple chips
                                            :loading="loading_pivots" :disabled="loading_pivots" closable-chips
                                            clearable variant="solo" color="teal" prepend-icon="mdi-water-pump"
                                            item-title="name" item-value="id"
                                            no-data-text="Nenhum dado cadastrado..." />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.status" label="Status" :items="['Ativo', 'Inativo']"
                                            multiple chips closable-chips clearable variant="solo" color="teal"
                                            prepend-icon="mdi-list-status" />
                                    </v-col>

                                    <v-col cols="12" md="6" lg="4">
                                        <v-select v-model="filters.date" label="Data"
                                            :items="['Antigos', 'Hoje!', 'Futuros']" multiple chips closable-chips
                                            clearable variant="solo" color="teal" prepend-icon="mdi-calendar" />
                                    </v-col>
                                </v-row>
                            </div>
                        </v-col>
                    </v-expand-transition>
                </v-row>

                <v-row v-if="items_length" class="align-center">
                    <v-col cols="12">
                        <v-text-field v-model="search_field"
                            :label="'Busca avançada em ' + filtered_items.length + ' registro(s)'"
                            prepend-icon="mdi-magnify" density="compact" clearable color="green" />
                    </v-col>
                </v-row>

                <v-row>
                    <v-alert v-if="!items_length || !paginated_items.length" icon="mdi-information" border="bottom"
                        class="mb-2" :color="dark_theme ? 'rgba(50, 50, 50, 0.6)' : 'rgba(250, 250, 250, 0.8)'">
                        {{ !items_length ? (loading ? 'Carregando, aguarde...' : 'Lista vazia, inclua um item clicando no botão "Novo" acima') : ('Não há dados para o filtro ' + (search_field.length ? ('"' + search_field + '"') : '')) }}
                    </v-alert>
                    <v-col v-else v-for="item in paginated_items" :key="item.id" cols="12" md="6"
                        :class="dark_theme ? 'text-shadow-black-2' : ''">
                        <v-card class="items-card" :class="dark_theme ? 'list-item-dark' : 'list-item'"
                            style="padding: 0;">
                            <div @click="toggleExpand(item.id)" style="padding: 20px;">
                                <div class="d-flex justify-space-between align-start mb-3">
                                    <div style="font-size: 34px;">
                                        {{ getCropEmoji(item.crop?.name) }}
                                    </div>

                                    <div class="flex-grow-1 px-2">
                                        <div class="bold" style="font-size: 16px;">
                                            {{
                                                item.name.length > 40
                                                    ? item.name.substring(0, 40) + '...'
                                                    : item.name
                                            }}
                                        </div>

                                        <div class="mt-1 d-flex flex-wrap ga-1">
                                            <v-chip size="x-small" :color="item.status == 1 ? 'green' : 'red'"
                                                variant="elevated" style="text-shadow: none;">
                                                {{ item.status == 1 ? 'Ativo' : 'Inativo' }}
                                            </v-chip>
                                        </div>
                                    </div>

                                    <div class="text-center" style="height: 70px;">
                                        <template v-if="getDaysAfterPlanting(item.date) > 0">
                                            <div class="bold text-warning" style="font-size: 25px;">
                                                {{ getDaysAfterPlanting(item.date) }}
                                            </div>
                                            <div class="font-weight-light">DAP</div>
                                        </template>

                                        <template v-else-if="getDaysAfterPlanting(item.date) == 0">
                                            <div class="bold" style="font-size: 22px;">
                                                <v-chip color="info" size="large">Hoje!</v-chip>
                                            </div>
                                        </template>

                                        <template v-else>
                                            <div class="bold text-green" style="font-size: 25px;">
                                                {{ Math.abs(getDaysAfterPlanting(item.date)) }}
                                            </div>
                                            <div class="font-weight-light" style="font-size: 12px;">
                                                dias até o plantio
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <v-divider class="mb-3"></v-divider>

                                <v-row density="comfortable">
                                    <v-col cols="6">
                                        <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                            <div class="dashboard-label">
                                                <v-icon size="14">mdi-sprout</v-icon>
                                                Cultura
                                            </div>

                                            <div class="dashboard-value">
                                                {{ item.crop?.name || 'Não definido' }}
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="6">
                                        <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                            <div class="dashboard-label">
                                                <v-icon size="14">mdi-seed</v-icon>
                                                Variedade
                                            </div>

                                            <div class="dashboard-value">
                                                {{ item.variety || 'Não definido' }}
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="6">
                                        <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                            <div class="dashboard-label">
                                                <v-icon size="14">mdi-water-pump</v-icon>
                                                Pivô
                                            </div>

                                            <div class="dashboard-value">
                                                {{ item.pivot?.name || 'Não definido' }}
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="6">
                                        <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                            <div class="dashboard-label">
                                                <v-icon size="14">mdi-ruler-square</v-icon>
                                                Área
                                            </div>

                                            <div class="dashboard-value">
                                                {{ item.size_ha ? item.size_ha + ' ha' : 'Não definido' }}
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="6">
                                        <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                            <div class="dashboard-label">
                                                <v-icon size="14">mdi-calendar</v-icon>
                                                Plantio
                                            </div>

                                            <div class="dashboard-value">
                                                {{ formatDateBR(item.date) }}
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="6">
                                        <div :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                            <div class="dashboard-label">
                                                <v-icon size="14">mdi-file-document-multiple</v-icon>
                                                Fichas - {{item.field_records?.filter(r => r.id).length || 0}}
                                            </div>

                                            <div class="dashboard-value">
                                                <v-chip v-if="item.field_records.length == 0" size="x-small"
                                                    class="mr-1 mb-1" style="padding-left: 6px;" variant="outlined"
                                                    color="grey-darken-2">
                                                    Vazio
                                                </v-chip>
                                                <v-chip v-for="service in getGroupedServices(item.field_records)"
                                                    :key="service.name" size="x-small" class="mr-1 mb-1"
                                                    style="padding-left: 6px;" variant="outlined"
                                                    :color="service.color">
                                                    <template #prepend>
                                                        <v-avatar start size="18" :color="service.color">
                                                            {{ service.count }}
                                                        </v-avatar>
                                                    </template>
                                                    {{ service.name }}
                                                </v-chip>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12">
                                        <div :class="[
                                            dark_theme ? 'alerts-box-dark' : 'alerts-box-light',
                                            hasCriticalAlert(item) ? 'alerts-danger-border' : ''
                                        ]">
                                            <!-- Header -->
                                            <div :class="dark_theme ? 'alerts-header-dark' : 'alerts-header-light'"
                                                :style="hasCriticalAlert(item) ? (dark_theme ? 'color: rgb(250, 100, 100)' : 'color: rgb(195, 10, 0)') : ''">
                                                <v-icon size="18" class="mr-2">{{ hasCriticalAlert(item) ?
                                                    'mdi-bell-alert' : (getAlerts(item).length ?
                                                        'mdi-bell-ring' : 'mdi-bell-off') }}</v-icon>
                                                <strong>Alertas {{ getAlerts(item).length ? ' - ' +
                                                    getAlerts(item).length : '' }}</strong>
                                            </div>

                                            <!-- Rows -->
                                            <template v-if="getAlerts(item).length">
                                                <div v-for="(alert, index) in getAlerts(item)" :key="index">
                                                    
                                                    <div
                                                        class="alerts-row"
                                                        :class="getAlertRowClass(alert)"
                                                        @click.stop="openAlertFieldRecord(alert, item)"
                                                    >

                                                        <v-icon :color="alert.color" size="18" class="mr-3">
                                                            {{ alert.icon }}
                                                        </v-icon>

                                                        <div class="flex-grow-1">
                                                            <div class="bold">{{ alert.title }}</div>
                                                            <div style="opacity: 0.7;">{{ alert.text }}</div>
                                                        </div>

                                                        <div class="d-flex flex-column align-end" >
                                                            <v-chip
                                                                size="small" 
                                                                :color="alert.color || 'grey'"
                                                                class="pr-5"
                                                            >
                                                                <template #prepend>
                                                                <v-avatar start size="20" style="border: solid 1px white;">
                                                                    <strong
                                                                    style="letter-spacing: 1px;"
                                                                    :style="dark_theme ? 'color: white' : 'color: black'"
                                                                    >
                                                                    FC
                                                                    </strong>
                                                                </v-avatar>
                                                                </template>

                                                                {{ alert.id }}
                                                            </v-chip>

                                                            <v-switch
                                                                class="pr-3"                                                            
                                                                style="margin: 0px !important;"
                                                                hide-details                                                                
                                                                density="compact"
                                                                color="green"
                                                                :loading="loading_field_record_status[alert.id]"
                                                                :disabled="loading_field_record_status[alert.id]"
                                                                @click.stop
                                                                @update:model-value="finishFieldRecord(alert.id)"
                                                            >
                                                            </v-switch>
                                                        </div>
                                                    </div>

                                                    <v-divider v-if="index < getAlerts(item).length - 1" />
                                                </div>
                                            </template>

                                            <div v-else class="pa-3" style="opacity: 0.8;">
                                                Nenhum alerta no momento
                                            </div>
                                        </div>
                                    </v-col>

                                </v-row>

                                <v-card-actions class="align-end" style="position: relative;">
                                    <v-btn variant="elevated" style="position: absolute; left: 5px; bottom: 0px;"
                                        size="x-small" :color="dark_theme ? 'grey-darken-2' : 'grey-darken-1'" icon>
                                        <v-icon size="x-large">{{ expanded_items[item.id] ? 'mdi-chevron-up' :
                                            'mdi-chevron-down' }}</v-icon>
                                    </v-btn>
                                    <v-btn prepend-icon="mdi-file-document-plus" variant="elevated" size="x-small"
                                        color="green"
                                        @click.stop="add_field_record_dialog_planting_id = item.id; add_field_record_dialog = true">
                                        Nova Ficha
                                    </v-btn>
                                    <v-tooltip :text="item.status == 0 ? 'Retomar Plantio' : 'Pausar Plantio'"
                                        :content-class="item.status == 0 ? 'tooltip-green' : 'tooltip-red'"
                                        location="top">
                                        <template v-slot:activator="{ props }">
                                            <v-avatar v-bind="props" :color="item.status == 0 ? 'green' : 'red'" variant="elevated"
                                                style="text-shadow: none;" size="x-small" @click.stop="auth.user.level < 1 || loading_status[item.id]
                                                    ? null
                                                    : editPlanting(item.id, { status: item.status == 1 ? 0 : 1 })">
                                                <v-progress-circular v-if="loading_status[item.id]" indeterminate
                                                    size="15" width="3" />

                                                <v-icon size="small" v-else>
                                                    {{ item.status == 1 ? 'mdi-pause' : 'mdi-play' }}
                                                </v-icon>
                                            </v-avatar>
                                        </template>
                                    </v-tooltip>
                                    <v-btn prepend-icon="mdi-pencil" variant="elevated" size="x-small" color="orange"
                                        @click.stop="openEditDialog(item)">
                                        Editar
                                    </v-btn>
                                </v-card-actions>

                                <v-expand-transition>
                                    <div v-if="expanded_items[item.id]">
                                        <v-divider class="my-3"></v-divider>
                                        <div class="d-flex ga-2 mb-4 py-2 menu-scroll" style="text-shadow: none">
                                            <v-chip v-for="section in expanded_items_sections" :key="section.key"
                                                clickable
                                                :style="getExpandedSection(item.id) === section.key ? 'border: solid 2px rgba(50, 50, 50, 0.6)' : ''"
                                                :variant="getExpandedSection(item.id) === section.key ? 'elevated' : 'outlined'"
                                                class="px-4 menu-scroll-item"
                                                :color="getExpandedSection(item.id) === section.key
                                                    ? 'green-lighten-2'
                                                    : (dark_theme ? 'rgba(190, 190, 190, 0.8)' : 'rgba(30, 30, 30, 0.6)')"
                                                @click.stop="expanded_section[item.id] = section.key">
                                                <v-icon start>{{ section.icon }}</v-icon>
                                                {{ section.label }}
                                            </v-chip>
                                        </div>

                                        <div class="d-flex justify-space-between mb-6">
                                            <div class="bold d-flex align-center ga-2" style="font-size: 16px;">
                                                <v-icon color="teal">
                                                    {{
                                                        expanded_items_sections.find(
                                                            s => s.key === getExpandedSection(item.id)
                                                        )?.icon
                                                    }}
                                                </v-icon>

                                                {{
                                                    expanded_items_sections.find(
                                                        s => s.key === getExpandedSection(item.id)
                                                    )?.title
                                                }}
                                            </div>
                                            <v-chip v-if="getExpandedSection(item.id) === 'timeline'" size="small">
                                                Total: {{ getFilteredTimeline(item).length }}
                                            </v-chip>
                                        </div>
                                        <div v-if="getExpandedSection(item.id) === 'timeline'">
                                            <v-row v-if="item.field_records.length > 0" class="align-center mb-8"
                                                style="margin-top: -10px;">
                                                <v-col cols="12" lg="8" class="align-center"
                                                    style="margin-bottom: -20px;">
                                                    <v-select variant="underlined"
                                                        v-model="getTimelineFilter(item.id).services" label="Serviços"
                                                        @click.stop placeholder="Filtre pelo serviço da ficha"
                                                        :items="services" multiple chips closable-chips clearable
                                                        color="teal" prepend-icon="mdi-briefcase" />
                                                </v-col>
                                                <v-col cols="12" lg="4" style="margin-bottom: -40px;">
                                                    <v-checkbox v-model="getTimelineFilter(item.id).status" @click.stop
                                                        label="Concluídos" color="teal"></v-checkbox>
                                                </v-col>
                                                <v-col cols="12" class="align-end">
                                                    <v-btn prepend-icon="mdi-filter-off"
                                                        :disabled="getTimelineFilter(item.id).status == false && getTimelineFilter(item.id).services.length == 0"
                                                        @click.stop="clearTimelineFilter(item.id)" size="x-small"
                                                        color="grey-lighten-2">
                                                        Limpar Filtro
                                                    </v-btn>
                                                </v-col>

                                            </v-row>

                                            <v-timeline density="compact" align="start" side="end" truncate-line="both">
                                                <v-timeline-item v-if="getFilteredTimeline(item).length == 0"
                                                    dot-color="grey-darken-3" icon="mdi-information">
                                                    <v-card class="pa-3 mb-2 timeline-card" :style="` 
                                                        border: 1px solid grey;
                                                        cursor: pointer;
                                                    `"
                                                        @click.stop="add_field_record_dialog_planting_id = item.id; add_field_record_dialog = true">
                                                        Nenhuma ficha cadastrada{{ item.field_records.length > 0 ? ' com esse filtro' : '' }}, clique em
                                                        "Nova Ficha" ou aqui para adicionar. +
                                                    </v-card>
                                                </v-timeline-item>
                                                <v-timeline-item v-for="record in getFilteredTimeline(item)"
                                                    :key="record.id" :dot-color="getServiceColor(record.service)"
                                                    :icon="getServiceIcon(record.service)" fill-dot>
                                                    <v-card class="pa-3 mb-2 timeline-card"
                                                        :class="dark_theme ? 'alerts-box-dark' : 'alerts-box-light'"
                                                        :style="`
                                                        width: 100%;
                                                        border: 1px solid ${getColorHex(getServiceColor(record.service))};
                                                        cursor: pointer;
                                                    `" @click.stop="openEditFieldRecord(record, item)">
                                                        <div class="bold mb-2 d-flex justify-space-between">
                                                            <div>                                                                
                                                                <span class="align-start">
                                                                    {{ record.service }}
                                                                    <v-icon class="ml-2" :color="getFieldRecordDateColor(record)">{{ getFieldRecordDateColor(record) == 'green' ? 'mdi-check-circle' : (getFieldRecordDateColor(record) == 'blue' ? 'mdi-timer-sand' : 'mdi-timer-sand-empty') }}</v-icon>
                                                                </span>
                                                                <v-chip variant="outlined" size="small" class="mt-3"
                                                                    :color="getFieldRecordDateColor(record)">
                                                                    {{ formatDateBR(record.date) }}
                                                                </v-chip>
                                                            </div>

                                                            <div class="d-flex flex-column align-end">
                                                                <v-chip size="small" class="pl-2 mr-1 mb-2">
                                                                    <template #prepend>
                                                                        <v-avatar start size="26"
                                                                            style="border: solid 1px white;">
                                                                            <strong style="letter-spacing: 1px;"
                                                                                :style="dark_theme ? 'color: white' : 'color: black'">
                                                                                FC
                                                                            </strong>
                                                                        </v-avatar>
                                                                    </template>
                                                                    {{ record.id }}
                                                                </v-chip>                                                                

                                                                <v-tooltip text="Copiar para Whatsapp"
                                                                    content-class="tooltip-green" location="left">
                                                                    <template v-slot:activator="{ props }">
                                                                        <v-btn v-bind="props" size="28" icon
                                                                            color="green"
                                                                            @click.stop="wppCopyText(prepareWppCopyText(record, item))">
                                                                            <v-icon color="white">
                                                                                mdi-whatsapp
                                                                            </v-icon>
                                                                        </v-btn>
                                                                    </template>
                                                                </v-tooltip>
                                                            </div>
                                                        </div>

                                                        <v-divider class="mb-2"></v-divider>
                                                        <v-row density="compact">
                                                            <v-col cols="12" lg="6">
                                                                <div
                                                                    :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                                                    <div class="dashboard-label">
                                                                        <v-icon size="14">mdi-tractor</v-icon>
                                                                        Trator
                                                                    </div>

                                                                    <div class="dashboard-value">
                                                                        {{ record.tractor?.name || 'Não definido' }}
                                                                    </div>
                                                                </div>
                                                            </v-col>

                                                            <v-col cols="12" lg="6">
                                                                <div
                                                                    :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                                                    <div class="dashboard-label">
                                                                        <v-icon size="14">mdi-hammer-wrench</v-icon>
                                                                        Implemento
                                                                    </div>

                                                                    <div class="dashboard-value">
                                                                        {{ record.implement?.name || 'Não definido' }}
                                                                    </div>
                                                                </div>
                                                            </v-col>

                                                            <v-col cols="12">
                                                                <div
                                                                    :class="'dashboard-box-' + (dark_theme ? 'dark' : 'light')">
                                                                    <div class="dashboard-label">
                                                                        <v-icon size="14">mdi-flask</v-icon>
                                                                        Produtos - Dose ha
                                                                    </div>

                                                                    <div class="dashboard-value">
                                                                        <div v-for="product_item in record.products"
                                                                            :key="product_item.id"
                                                                            class="d-flex align-center mb-1">
                                                                            <span class="mr-2 flex-grow-1">
                                                                                {{ product_item.product?.name }}
                                                                            </span>

                                                                            <v-chip size="small"
                                                                                :color="product_item.product?.unit == 0 ? 'teal' : 'blue'">
                                                                                {{
                                                                                    Number(product_item.dosage).toFixed(2)
                                                                                }}
                                                                                <template #append>
                                                                                    <v-avatar v-if="!smAndDown" end>
                                                                                        {{ product_item.product?.unit ==
                                                                                            0 ?
                                                                                            'KG' : 'L' }}
                                                                                    </v-avatar>
                                                                                </template>
                                                                                <template #prepend>
                                                                                    <v-avatar start v-if="smAndDown">
                                                                                        {{ product_item.product?.unit ==
                                                                                            0 ?
                                                                                            'KG' : 'L' }}
                                                                                    </v-avatar>
                                                                                    <v-icon start v-else>
                                                                                        {{ product_item.product?.unit ==
                                                                                            0
                                                                                            ? 'mdi-weight-kilogram'
                                                                                            : 'mdi-bottle-tonic' }}
                                                                                    </v-icon>
                                                                                </template>
                                                                            </v-chip>
                                                                        </div>
                                                                        <v-btn size="x-small" color="grey-lighten-2"
                                                                            :append-icon="expanded_nutrition[record.id] ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                                                                            prepend-icon="mdi-flask-outline"
                                                                            @click.stop="toggleNutrition(record.id)">
                                                                            Detalhes
                                                                        </v-btn>
                                                                        <v-expand-transition>
                                                                            <div v-if="expanded_nutrition[record.id]">
                                                                                <v-divider class="my-3"></v-divider>
                                                                                <strong>- Gasto total no
                                                                                    plantio</strong>
                                                                                <div
                                                                                    class="d-flex flex-wrap ga-1 mb-4 mt-2">
                                                                                    <v-chip
                                                                                        v-for="product_item in record.products"
                                                                                        :key="product_item.id"
                                                                                        variant="outlined" class="pr-0"
                                                                                        style="border-radius: 6px; text-shadow: none;">
                                                                                        {{ product_item.product?.name }}

                                                                                        <template #append>
                                                                                            <v-chip size="small"
                                                                                                style="border-radius: 6px; text-shadow: none;"
                                                                                                variant="elevated"
                                                                                                class="ml-2"
                                                                                                :color="product_item.product?.unit == 0 ? 'teal' : 'blue'">
                                                                                                {{
                                                                                                    getTotalProductAmount(product_item,
                                                                                                        item).toFixed(2) }}
                                                                                                <template #append>
                                                                                                    <v-avatar end
                                                                                                        :style="'border: solid 1px ' +
                                                                                                            (dark_theme
                                                                                                                ? 'rgba(190, 190, 190, 0.8)'
                                                                                                                : 'rgba(50, 50, 50, 0.9)')">
                                                                                                        {{
                                                                                                            product_item.product?.unit
                                                                                                                == 0 ? 'KG' :
                                                                                                                'L' }}
                                                                                                    </v-avatar>
                                                                                                </template>
                                                                                            </v-chip>
                                                                                        </template>
                                                                                    </v-chip>
                                                                                </div>
                                                                                <v-divider class="my-3"></v-divider>
                                                                                <strong>- Nutrientes totais por
                                                                                    ha</strong>
                                                                                <div v-if="Object.keys(calculateNutrients(record)).length > 0"
                                                                                    class="d-flex flex-wrap ga-1 mt-2">
                                                                                    <v-chip
                                                                                        v-for="(value, nutrient) in calculateNutrients(record)"
                                                                                        :key="nutrient"
                                                                                        :color="nutrient_colors[nutrient]"
                                                                                        variant="flat" class="pr-0"
                                                                                        style="text-shadow: none;">
                                                                                        {{ getNutrientLabel(nutrient) }}
                                                                                        <template #append>
                                                                                            <v-chip size="small"
                                                                                                variant="elevated"
                                                                                                class="ml-2"
                                                                                                :color="dark_theme ? 'rgba(190, 190, 190, 0.7)' : 'rgba(10, 10, 10, 0.6)'">
                                                                                                {{ value.toFixed(2) }}
                                                                                                <template #append>
                                                                                                    <v-avatar end
                                                                                                        :style="'border: solid 1px ' + (dark_theme ? 'rgba(190, 190, 190, 0.8)' : 'rgba(50, 50, 50, 0.9)')">ha</v-avatar>
                                                                                                </template>
                                                                                            </v-chip>
                                                                                        </template>
                                                                                    </v-chip>
                                                                                </div>
                                                                                <div v-else class="my-2"
                                                                                    style="opacity: 0.8;">Nutrientes não
                                                                                    definidos nos
                                                                                    produtos</div>
                                                                            </div>
                                                                        </v-expand-transition>
                                                                    </div>
                                                                </div>
                                                            </v-col>
                                                        </v-row>
                                                    </v-card>
                                                </v-timeline-item>
                                            </v-timeline>
                                        </div>

                                        <div v-if="getExpandedSection(item.id) === 'nutrition'">
                                            <div v-if="item.field_records.length">
                                                <v-row density="comfortable" class="mb-6">  
                                                    <v-col
                                                        v-for="nutrient in getPlantingNutrientCards(item)"
                                                        :key="nutrient.key"
                                                        cols="12"
                                                        sm="4"
                                                    >
                                                        <v-card
                                                            class="nutrient-card"
                                                            :style="`
                                                                border: 2px solid ${getColorHex(nutrient.key)};
                                                                background-color: ${hexToRgba(nutrient.key, 0.1)};
                                                            `"
                                                        >
                                                            <div
                                                                class="bold mb-2 d-flex align-center ga-2"
                                                                :style="`font-size: 18px; color: ${getColorHex(nutrient.key)}`"
                                                            >
                                                                {{ nutrient.label }}
                                                                <span style="font-size: 14px; opacity: 0.7;">
                                                                    ({{ nutrient.symbol }})
                                                                </span>
                                                            </div>

                                                            <div class="bold align-center" style="font-size: 20px;">
                                                                {{ nutrient.perHa.toFixed(2) }}
                                                            </div>
                                                            <div style="opacity: 0.6;" class="align-center">kg / ha</div>

                                                            <div class="bold mt-2 align-center" style="font-size: 20px;">
                                                                {{ nutrient.total.toFixed(2) }} kg
                                                            </div>
                                                            <div style="opacity: 0.6;" class="align-center">
                                                                Total no plantio ({{ item.size_ha }} ha)
                                                            </div>
                                                        </v-card>
                                                    </v-col>
                                                </v-row>
                                                <v-divider></v-divider>
                                                <div class="ma-3">
                                                    <strong>- Tabela detalhada</strong>
                                                </div>                                                
                                                <div :class="dark_theme ? 'nutrition-table-scroll-dark' : 'nutrition-table-scroll-light'">
                                                
                                                    <div
                                                        class="nutrition-grid mb-2" :class="dark_theme ? 'nutrition-header-dark' : 'nutrition-header-light'"
                                                        :style="`
                                                            grid-template-columns:
                                                                260px
                                                                110px
                                                                repeat(${getUsedNutrients(item).length}, 120px);
                                                        `"
                                                    >
                                                        <div class="bold">Produto</div>
                                                        <div class="bold text-center">Dose</div>

                                                        <div
                                                            v-for="nutrient in getUsedNutrients(item)"
                                                            :key="nutrient.key"
                                                            class="bold text-center"
                                                            :style="`color: ${getColorHex(nutrient.key)}`"
                                                        >
                                                            {{ nutrient.symbol }}
                                                        </div>
                                                    </div>
                                                
                                                    <div
                                                        v-for="record in getSortedFieldRecords(item.field_records)"
                                                        :key="record.id"
                                                        class="mb-4"
                                                    >                                                   
                                                        <div class="opacity-70 mb-1 pl-2">
                                                            FC-{{ record.id }} • {{ formatDateBR(record.date) }}
                                                        </div>

                                                        <div
                                                            v-for="product_item in record.products"
                                                            :key="product_item.id"
                                                            class="nutrition-grid"
                                                            :style="`
                                                                grid-template-columns:
                                                                    260px
                                                                    110px
                                                                    repeat(${getUsedNutrients(item).length}, 120px);
                                                            `"
                                                        >                                                      
                                                            <div>
                                                                {{ product_item.product?.name }}
                                                            </div>

                                                            <div class="text-center">
                                                                {{ Number(product_item.dosage).toFixed(2) }}
                                                                <span style="opacity: 0.6; font-size: 12px;">kg/ha</span>
                                                            </div>
                                                        
                                                            <div
                                                                v-for="nutrient in getUsedNutrients(item)"
                                                                :key="nutrient.key"
                                                                class="text-center"
                                                                :style="`color: ${getColorHex(nutrient.key)}`"
                                                            >
                                                                {{
                                                                    product_item.product?.[nutrient.key]
                                                                        ? (
                                                                            (product_item.product[nutrient.key] / 100) *
                                                                            product_item.dosage
                                                                        ).toFixed(2)
                                                                        : '—'
                                                                }}
                                                                <span
                                                                    v-if="product_item.product?.[nutrient.key]"
                                                                    style="opacity: 0.5; font-size: 11px;"
                                                                >
                                                                    kg/ha
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                    <div
                                                        class="nutrition-grid mt-4 nutrition-total-first" :class="dark_theme ? 'nutrition-total-dark' : 'nutrition-total-light'"
                                                        :style="`
                                                            grid-template-columns:
                                                                260px
                                                                110px
                                                                repeat(${getUsedNutrients(item).length}, 120px);
                                                        `"
                                                    >
                                                        <div class="bold"> <v-icon>mdi-sigma</v-icon> Acumulado por ha</div>
                                                        <div></div>

                                                        <div
                                                            v-for="nutrient in getUsedNutrients(item)"
                                                            :key="nutrient.key"
                                                            class="bold text-center"
                                                            :style="`color: ${getColorHex(nutrient.key)}`"
                                                        >
                                                            {{
                                                                calculatePlantingNutrientsPerHa(item)[nutrient.key]
                                                                    ?.toFixed(2) || '—'
                                                            }}
                                                            <span style="opacity: 0.6; font-size: 11px;">kg/ha</span>
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="nutrition-grid nutrition-total-second" :class="dark_theme ? 'nutrition-total-dark' : 'nutrition-total-light'"
                                                        :style="`
                                                            grid-template-columns:
                                                                260px
                                                                110px
                                                                repeat(${getUsedNutrients(item).length}, 120px);
                                                        `"
                                                    >
                                                        <div class="bold">
                                                            <v-icon>mdi-sigma</v-icon> Acumulado no plantio ({{ item.size_ha }} ha)
                                                        </div>
                                                        <div></div>

                                                        <div
                                                            v-for="nutrient in getUsedNutrients(item)"
                                                            :key="nutrient.key"
                                                            class="bold text-center"
                                                            :style="`color: ${getColorHex(nutrient.key)}`"
                                                        >
                                                            {{
                                                                (
                                                                    calculatePlantingNutrientsPerHa(item)[nutrient.key] *
                                                                    item.size_ha
                                                                )?.toFixed(2) || '—'
                                                            }}
                                                            <span style="opacity: 0.6; font-size: 11px;">kg</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>    
                                            <div v-else class="d-flex align-start w-100">
                                                <v-avatar
                                                    color="grey-darken-3"
                                                    size="38"
                                                    style="margin-right: 10px;"
                                                >
                                                    <v-icon>mdi-information</v-icon>
                                                </v-avatar>

                                                <v-card
                                                    class="pa-3 mb-2 timeline-card flex-grow-1"
                                                    style="border: 1px solid grey; cursor: pointer;"
                                                    @click.stop="add_field_record_dialog_planting_id = item.id; add_field_record_dialog = true"
                                                >
                                                    Nenhuma ficha cadastrada{{ item.field_records.length > 0 ? ' com esse filtro' : '' }},
                                                    clique em "Nova Ficha" ou aqui para adicionar. +
                                                </v-card>
                                            </div>                                                                                                                                                                      
                                        </div>                                        
                                    </div>
                                </v-expand-transition>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>

                <v-row class="align-center pt-3" v-if="!loading && paginated_items.length > 0">
                    <v-col cols="12">
                        <v-pagination v-model="current_page" :length="total_pages" :total-visible="5"
                            rounded="circle" />
                    </v-col>

                    <v-col cols="12" class="align-end">
                        <v-select label="Itens por página:" color="green" density="compact"
                            :items="[5, 10, 15, 50, 'Todos']" style="max-width: 130px;" variant="outlined"
                            v-model="items_per_page" />
                    </v-col>
                </v-row>

                <DialogAddFieldRecord @new_register="pushNewFieldRecord" @close="add_field_record_dialog = false"
                    icon="mdi-file-document-multiple" :model="add_field_record_dialog" color="rgb(90,180,80)"
                    :planting_id="add_field_record_dialog_planting_id" />

                <DialogEditFieldRecord @edited_register="editFieldRecordItem" @close="edit_field_record_dialog = false"
                    :planting_id="edit_field_record_dialog_planting_id" icon="mdi-file-document-multiple"
                    :data="edit_field_record_dialog_data" :model="edit_field_record_dialog" color="orange" />

                <DialogAddPlanting @new_register="pushNewItem" @close="add_dialog = false" :icon="icon"
                    :model="add_dialog" color="rgb(90, 180, 80)" />

                <DialogEditPlanting @edited_register="editItem" @close="edit_dialog = false" :icon="icon"
                    :data="edit_dialog_data" :model="edit_dialog" color="orange" />

                <DialogDelete @deleted="popItem" @close="delete_dialog = false" :icon="icon" :data="delete_dialog_data"
                    data_name="planting" :model="delete_dialog" color="rgb(230, 60, 60)" />
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup>
// Imports
import api from '@/plugins/axios.js'
import { useAuthStore } from '@/stores/auth.js'
import { useSnackbarStore } from '@/stores/snackbar'
import { ref, computed, reactive, watch } from 'vue'
import { useTheme, useDisplay } from 'vuetify'
import DialogAddFieldRecord from '@/components/dialogs/DialogAddFieldRecord.vue'
import DialogAddPlanting from '@/components/dialogs/DialogAddPlanting.vue'
import DialogEditFieldRecord from '@/components/dialogs/DialogEditFieldRecord.vue'
import DialogEditPlanting from '@/components/dialogs/DialogEditPlanting.vue'
import DialogDelete from '@/components/dialogs/DialogDelete.vue'

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
const snackbar = useSnackbarStore()
const emit = defineEmits(['change_tab'])

const items = ref([])
const expanded_items = reactive({})
const expanded_section = reactive({})
const expanded_nutrition = reactive({})
const crops = ref([])
const pivots = ref([])
const varieties = ref([])
const services = ['Ferti Irrigação', 'Pulverização', 'Adubação', 'Colheita', 'Plantio']

const loading = ref(false)
const loading_crops = ref(false)
const loading_pivots = ref(false)
const loading_status = reactive({})
const loading_field_record_status = reactive({})

const search_field = ref('')
const current_page = ref(1)
const items_per_page = ref(10)

const filter_active = ref(false)

const add_field_record_dialog = ref(false)
const edit_field_record_dialog = ref(false)
const add_field_record_dialog_planting_id = ref(0)
const edit_field_record_dialog_planting_id = ref(0)
const add_dialog = ref(false)
const edit_dialog = ref(false)
const delete_dialog = ref(false)

const pending_alert = ref(true)

const edit_field_record_dialog_data = reactive({})
const edit_dialog_data = reactive({})
const delete_dialog_data = reactive({})

const filters = reactive({
    crops: [],
    varieties: [],
    pivots: [],
    status: ['Ativo'],
    date: []
})
const timeline_filters = reactive({})

const nutrient_colors = {
    nitrogen: '#4CAF50',
    phosphorus: '#009688',
    potassium: '#673AB7',
    calcium: '#2196F3',
    magnesium: '#FB8C00',
    sulfur: '#FBC02D',
    boron: '#E91E63',
    copper: '#795548',
    manganese: '#3F51B5',
    zinc: '#00BCD4',
    iron: '#C62828',
    molybdenum: '#9E9E9E',
    silicon: '#95ab55'
}

const nutrient_order = [
    { key: 'nitrogen', label: 'Nitrogênio', symbol: 'N', always: true },
    { key: 'phosphorus', label: 'Fósforo', symbol: 'P', always: true },
    { key: 'potassium', label: 'Potássio', symbol: 'K', always: true },

    { key: 'calcium', label: 'Cálcio', symbol: 'Ca' },
    { key: 'magnesium', label: 'Magnésio', symbol: 'Mg' },
    { key: 'sulfur', label: 'Enxofre', symbol: 'S' },
    { key: 'boron', label: 'Boro', symbol: 'B' },
    { key: 'copper', label: 'Cobre', symbol: 'Cu' },
    { key: 'manganese', label: 'Manganês', symbol: 'Mn' },
    { key: 'zinc', label: 'Zinco', symbol: 'Zn' },
    { key: 'iron', label: 'Ferro', symbol: 'Fe' },
    { key: 'molybdenum', label: 'Molibdênio', symbol: 'Mo' },
    { key: 'silicon', label: 'Silício', symbol: 'Si' }
]

const expanded_items_sections = [
    {
        key: 'timeline',
        label: 'Timeline',
        title: 'Timeline de Serviços',
        icon: 'mdi-timeline-clock'
    },
    {
        key: 'nutrition',
        label: 'Nutrição',
        title: 'Nutrição do Plantio',
        icon: 'mdi-flask-outline'
    },
]

const searchable_fields = [
    { key: 'name' },
    { key: 'variety' },
    { key: 'crop.name' },
    { key: 'pivot.name' },
    { key: 'date' },
    { key: 'size_ha' },
    {
        key: 'status',
        map: {
            '0': 'inativo inativa pausado pausada',
            '1': 'ativo ativa'
        }
    }
]

// Created
getItems()
getCrops()
getPivots()

// Computeds
const items_length = computed(() => items.value.length)

const has_active_filters = computed(() => {
    return Object.values(filters).some(value => value.length > 0)
})

const filtered_items = computed(() => {
    current_page.value = 1

    let data = [...items.value]

    if (filters.crops.length) data = data.filter(item => filters.crops.includes(item.crop?.id))
    if (filters.varieties.length) data = data.filter(item => filters.varieties.includes(item.variety))
    if (filters.pivots.length) data = data.filter(item => filters.pivots.includes(item.pivot?.id))
    if (filters.status.length) data = data.filter(item => filters.status.includes(item.status == 1 ? 'Ativo' : 'Inativo'))
    if (filters.date.length) data = data.filter(item => filters.date.includes(getDateType(item.date)))

    if (!search_field.value) return data

    const search = search_field.value.toLowerCase().trim()

    return data.filter(item => {
        return searchable_fields.some(field => {
            const raw_value = getNestedValue(item, field.key)

            if (raw_value === null || raw_value === undefined) return false

            if (field.map) {
                const mapped_value = field.map[raw_value]
                if (!mapped_value) return false

                return mapped_value
                    .toLowerCase()
                    .split(' ')
                    .some(word => word.startsWith(search))
            }

            return String(raw_value).toLowerCase().includes(search)
        })
    })
})

const paginated_items = computed(() => {
    if (items_per_page.value === 'Todos') return filtered_items.value

    const start = (current_page.value - 1) * items_per_page.value
    const end = start + items_per_page.value

    return filtered_items.value.slice(start, end)
})

const total_pages = computed(() => {
    if (items_per_page.value === 'Todos') return 1
    return Math.ceil(filtered_items.value.length / items_per_page.value)
})

// Watchers
watch(items_per_page, () => {
    current_page.value = 1
})

watch(items_length, value => {
    if (!value) {
        clearFilters()
        filter_active.value = false
    }
})

// Methods
const pendingFieldRecordsAlert = computed(() => {
    const allRecords = items.value.flatMap(item => item.field_records || [])

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    let todayCount = 0
    let lateCount = 0

    allRecords.forEach(record => {
        // ignora concluídos
        if (Number(record.status) === 1) return
        if (!record.date) return

        const target = new Date(record.date + 'T00:00:00')
        target.setHours(0, 0, 0, 0)

        if (target < today) lateCount++
        else if (target.getTime() === today.getTime()) todayCount++
    })

    if (!todayCount && !lateCount) return null

    return {
        today: todayCount,
        late: lateCount,
        color: lateCount > 0 ? 'error' : 'warning',
        icon: lateCount > 0 ? 'mdi-bell-alert' : 'mdi-bell-circle'
    }
})

function goToFieldRecordsTab() {
    emit('change_tab', 2)
}

function finishFieldRecord(id) {
    loading_field_record_status[id] = true
    api.put('edit_field_record/' + id, { status: 1 }).then(() => {
        items.value.forEach(item => {
            const record = item.field_records?.find(r => r.id == id)
            if (record) record.status = 1
        })
    }).catch(error => {
        console.log(error)
        snackbar.open({ preset: 'error' })
        loading_field_record_status[id] = false
    })
}

function getAlertRowClass(alert) {
    if (!alert?.color) return ''

    if (alert.color === 'error' || alert.color === 'red') {
        return dark_theme.value
            ? 'alert-bg-red-dark'
            : 'alert-bg-red-light'
    }

    if (alert.color === 'warning' || alert.color === 'orange') {
        return dark_theme.value
            ? 'alert-bg-orange-dark'
            : 'alert-bg-orange-light'
    }

    return ''
}

function openAlertFieldRecord(alert, planting) {
    const record = planting.field_records.find(r => r.id === alert.id)
    if (!record) return
    openEditFieldRecord(record, planting)
}

function getUsedNutrients(planting) {
    const used = new Set()

    planting.field_records?.forEach(record => {
        record.products?.forEach(product_item => {
            nutrient_order.forEach(n => {
                const percent = Number(product_item.product?.[n.key])
                if (percent && percent > 0) {
                    used.add(n.key)
                }
            })
        })
    })

    return nutrient_order.filter(n => used.has(n.key))
}

function getPlantingNutrientCards(planting) {
    const perHa = calculatePlantingNutrientsPerHa(planting)
    const total = calculatePlantingTotal(perHa, planting.size_ha)

    return nutrient_order
        .map(nutrient => ({
            ...nutrient,
            perHa: perHa[nutrient.key] || 0,
            total: total[nutrient.key] || 0
        }))
        .filter(n =>
            n.always || n.perHa > 0
        )
}

function calculatePlantingTotal(nutrientsPerHa, size_ha) {
    const totals = {}
    if (!size_ha) return totals

    Object.keys(nutrientsPerHa).forEach(key => {
        totals[key] = nutrientsPerHa[key] * size_ha
    })

    return totals
}

function calculatePlantingNutrientsPerHa(planting) {
    const totals = {}

    if (!planting.field_records?.length) return totals

    planting.field_records.forEach(record => {
        record.products?.forEach(product_item => {
            const product = product_item.product
            const dosage = Number(product_item.dosage)

            if (!product || !dosage) return

            nutrient_order.forEach(({ key }) => {
                const percent = Number(product[key])
                if (!percent || percent <= 0 || percent > 100) return

                const value = (percent / 100) * dosage
                totals[key] = (totals[key] || 0) + value
            })
        })
    })

    return totals
}

function getExpandedSection(id) {
    if (!expanded_section[id]) {
        expanded_section[id] = 'timeline'
    }
    return expanded_section[id]
}

function getTotalProductAmount(product_item, planting) {
    if (!product_item || !planting?.size_ha) return 0

    const dosage = Number(product_item.dosage)
    const size = Number(planting.size_ha)

    if (!dosage || !size) return 0

    return dosage * size
}

function calculateNutrients(record) {
    const totals = {}

    if (!record.products?.length) return totals

    record.products.forEach(product_item => {
        const product = product_item.product
        const dosage = Number(product_item.dosage)

        if (!product || !dosage) return

        Object.keys(nutrient_colors).forEach(nutrient => {
            const percent = Number(product[nutrient])

            if (
                !percent ||
                percent <= 0 ||
                percent > 100
            ) return

            const value = (percent / 100) * dosage

            if (!totals[nutrient]) {
                totals[nutrient] = 0
            }

            totals[nutrient] += value
        })
    })

    return totals
}

function getNutrientLabel(key) {
    const labels = {
        nitrogen: 'Nitrogênio',
        phosphorus: 'Fósforo',
        potassium: 'Potássio',
        calcium: 'Cálcio',
        magnesium: 'Magnésio',
        sulfur: 'Enxofre',
        boron: 'Boro',
        copper: 'Cobre',
        manganese: 'Manganês',
        zinc: 'Zinco',
        iron: 'Ferro',
        molybdenum: 'Molibdênio',
        silicon: 'Silício'
    }

    return labels[key] || key
}

function toggleNutrition(record_id) {
    expanded_nutrition[record_id] = !expanded_nutrition[record_id]
}

function clearTimelineFilter(id) {
    timeline_filters[id] = {
        services: [],
        status: false
    }
}

function getFilteredTimeline(item) {
    const filter = getTimelineFilter(item.id)

    let records = [...(item.field_records || [])]

    if (filter.services.length) {
        records = records.filter(record =>
            filter.services.includes(record.service)
        )
    }

    if (filter.status) {
        records = records.filter(record => Number(record.status) === 1)
    }

    return records.sort((a, b) => new Date(b.date) - new Date(a.date))
}

function getTimelineFilter(id) {
    if (!timeline_filters[id]) {
        timeline_filters[id] = {
            services: [],
            status: false
        }
    }

    return timeline_filters[id]
}

function getFieldRecordDateColor(record) {
    if (Number(record.status) === 1) return 'green'

    if (!record.date) return 'blue'

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const target = new Date(record.date + 'T00:00:00')
    target.setHours(0, 0, 0, 0)

    return target < today ? 'red' : 'blue'
}

function openEditFieldRecord(item, full_item) {
    Object.assign(edit_field_record_dialog_data, prepareWppCopyText(item, full_item))
    edit_field_record_dialog_planting_id.value = item.id
    edit_field_record_dialog.value = true
}

function getColorHex(color) {
    if (!color) return '#888888'

    // 1️⃣ Se já for HEX válido, retorna direto
    if (
        typeof color === 'string' &&
        color.startsWith('#') &&
        (color.length === 7 || color.length === 4)
    ) {
        return color
    }

    // 2️⃣ Tokens Vuetify conhecidos
    const vuetifyColors = {
        red: '#f44336',
        blue: '#2196f3',
        teal: '#009688',
        green: '#4caf50',
        brown: '#795548',
        grey: '#9e9e9e',
        warning: '#fb8c00',
        info: '#2196f3',
        error: '#f44336',

        'orange-darken-3': '#ef6c00',
        'green-lighten-2': '#81c784',
        'grey-darken-1': '#757575',
        'grey-darken-2': '#616161',
        'grey-lighten-2': '#e0e0e0',
        'grey-lighten-3': '#eeeeee',
        'blue-darken-2': '#1976d2',
        'teal-darken-2': '#00796b',
        'brown-darken-1': '#6d4c41',
    }

    if (vuetifyColors[color]) {
        return vuetifyColors[color]
    }

    // 3️⃣ Nutrientes (usa o mapa oficial)
    if (nutrient_colors[color]) {
        return nutrient_colors[color]
    }

    // 4️⃣ Fallback seguro
    return '#888888'
}

function hexToRgba(color, alpha = 1) {
    const hex = getColorHex(color).replace('#', '')

    const bigint = parseInt(hex, 16)
    const r = (bigint >> 16) & 255
    const g = (bigint >> 8) & 255
    const b = bigint & 255

    return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

function getSortedFieldRecords(records = []) {
    return [...records].sort((a, b) =>
        new Date(b.date) - new Date(a.date)
    )
}

function toggleExpand(id) {
    getTimelineFilter(id)
    expanded_items[id] = !expanded_items[id]
}

function getAlerts(item) {
    const alerts = []

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const fieldRecords = item.field_records || []

    fieldRecords.forEach(record => {
        // only pending records
        if (Number(record.status) === 1) return
        if (!record.date || !record.service) return

        const target = new Date(record.date + 'T00:00:00')
        target.setHours(0, 0, 0, 0)

        const diff = Math.ceil((target - today) / 86400000)

        let alert = null

        if (diff < 0) {
            alert = {
                text: `Atrasado ${Math.abs(diff)} dia(s)`,
                color: 'error'
            }
        } else if (diff === 0) {
            alert = {
                text: 'Hoje!',
                color: 'warning'
            }
        } else if (diff <= 7) {
            alert = {
                text: `Em ${diff} dia(s)`,
                color: 'info'
            }
        }

        if (alert) {
            alert = {
                id: record.id,
                title: record.service,
                icon: getServiceIcon(record.service),
                sort: diff,
                ...alert
            }
        }

        if (alert) alerts.push(alert)
    })

    // inactivity always on top
    if (item.status == 1 && item.date) {
        const plantingDate = new Date(item.date + 'T00:00:00')
        plantingDate.setHours(0, 0, 0, 0)

        const plantingDays = Math.floor((today - plantingDate) / 86400000)

        // só considera se a data do plantio já chegou
        if (plantingDays >= 0) {
            const validRecords = fieldRecords
                .filter(record => record.date)
                .map(record => ({
                    ...record,
                    parsedDate: new Date(record.date + 'T00:00:00')
                }))

            let referenceDate = plantingDate
            let lastService = 'Plantio'
            let referenceId = item.id

            // se existir ficha, pega a mais recente
            if (validRecords.length) {
                const latest = validRecords.sort(
                    (a, b) => b.parsedDate - a.parsedDate
                )[0]

                latest.parsedDate.setHours(0, 0, 0, 0)

                referenceDate = latest.parsedDate
                lastService = latest.service || 'Serviço'
                referenceId = latest.id
            }

            const inactiveDays = Math.floor(
                (today - referenceDate) / 86400000
            )

            // ALERTA CRÍTICO (15+)
            if (inactiveDays >= 15) {
                alerts.unshift({
                    id: referenceId,
                    title: 'Inatividade',
                    text: `Inativo por ${inactiveDays} dia(s)`,
                    icon: 'mdi-calendar-alert',
                    color: 'red',
                    fixedTop: true
                })
            }

            // PRÉ-ALERTA (10 a 14)
            else if (inactiveDays >= 10) {
                alerts.unshift({
                    id: referenceId,
                    title: 'Inatividade',
                    text: `Última atividade há ${inactiveDays} dia(s) - ${lastService}`,
                    icon: 'mdi-calendar',
                    color: '',
                    fixedTop: true
                })
            }
        }
    }

    return alerts.sort((a, b) => {
        if (a.fixedTop) return -1
        if (b.fixedTop) return 1
        return (a.sort ?? 9999) - (b.sort ?? 9999)
    })
}

function hasCriticalAlert(item) {
    return getAlerts(item).some(alert =>
        alert.text.includes('Atrasado') ||
        alert.text.includes('Inativo')
    )
}

function prepareWppCopyText(item, full_item) {
    let item_copy = { ...item }
    let full_item_copy = { ...full_item }
    delete full_item_copy.field_records
    item_copy['planting'] = full_item_copy
    return item_copy
}

function wppCopyText(item) {
    const safe = value => value ?? ''

    const formatDateBR = date => {
        if (!date) return ''
        const [year, month, day] = date.split('-')
        return `${day}/${month}/${year}`
    }

    const getDaysAfterPlanting = date => {
        if (!date) return ''
        const planting = new Date(date + 'T00:00:00')
        const today = new Date()

        planting.setHours(0, 0, 0, 0)
        today.setHours(0, 0, 0, 0)

        return Math.floor((today - planting) / 86400000)
    }

    const getCropEmoji = name => {
        if (!name) return '🌱'

        const crop = name.toLowerCase()

        const dictionary = {
            alho: '🧄',
            cenoura: '🥕',
            milho: '🌽',
            soja: '🫘',
            batata: '🥔',
            abacate: '🥑',
            tomate: '🍅',
            cebola: '🧅',
            cana: '🎋',
            café: '☕',
            cafe: '☕',
            trigo: '🌾',
            feijão: '🫘',
            feijao: '🫘',
            brachiaria: '🌿',
            braquiaria: '🌿'
        }

        for (const key in dictionary) {
            if (crop.includes(key)) return dictionary[key]
        }

        return '🌱'
    }

    const cropName = safe(item.planting?.crop?.name)
    const emoji = getCropEmoji(cropName)
    const service = safe(item.service)
    const plantingName = safe(item.planting?.name)
    const pivotName = safe(item.planting?.pivot?.name)
    const date = formatDateBR(item.date)
    const dap = getDaysAfterPlanting(item.planting?.date)
    const tractor = safe(item.tractor?.name)
    const implement = safe(item.implement?.name)
    const variety = safe(item.planting?.variety)
    const size = item.planting?.size_ha ? `${item.planting.size_ha} ha` : ''
    const notes = safe(item.notes)

    const code = `FC-${item.id}`

    const products = item.products?.length
        ? item.products.map(product => {
            const name = safe(product.product?.name)
            const dosage = safe(product.dosage)

            const unitMap = {
                1: 'L/ha',
                0: 'Kg/ha',
            }
            const unit = unitMap[product.product?.unit] ?? ''
            return `• ${name} ${dosage} ${unit}`.trim()
        }).join('\n')
        : ''
    const dapText =
        dap === ''
            ? ''
            : dap < 0
                ? `${Math.abs(dap)} dias até o plantio`
                : `${dap} dias`

    const text = [
        `${emoji} *${cropName}${service ? ' — ' + service : ''}*`,
        '——————————————',
        `*Ficha:* ${code}`,
        `📍 *Área:* ${plantingName}`,
        `💧 *Pivô:* ${pivotName}`,
        `📅 *Data:* ${date}`,
        `🌿 *DAP:* ${dapText}`,
        `🚜 *Trator:* ${tractor}`,
        `⚙️ *Implemento:* ${implement}`,
        `📌 *Local:* `,
        `🌱 *Cultura:* ${cropName}`,
        `🔩 *Horímetro inicial:* `,
        `🔩 *Horímetro final:* `,
        `📐 *Tamanho:* ${size}`,
        `💧 *Vol. calda:* `,
        `👤 *Operador:* `,
        `🧬 *Variedade:* ${variety}`,
        `📝 *Observações:* ${notes}`,
        '——————————————',
        '🧪 *Produtos:*',
        products,
        '——————————————',
        '_Quirino Agronegócios_'
    ].join('\n')

    navigator.clipboard.writeText(text)

    snackbar.open({
        color: 'green',
        prependIcon: 'mdi-whatsapp',
        text: 'Texto copiado para a área de transferência!',
        timer: true
    })
}

function getServiceIcon(service) {
    const icons = {
        'Ferti Irrigação': 'mdi-water-sync',
        'Pulverização': 'mdi-spray',
        'Adubação': 'mdi-shovel',
        'Colheita': 'mdi-food-apple',
        'Plantio': 'mdi-seed'
    }

    return icons[service] || 'mdi-bell'
}

function getGroupedServices(field_records = []) {
    const grouped = {}

    field_records.forEach(record => {
        const name = record.service

        if (!name) return

        if (!grouped[name]) {
            grouped[name] = {
                name,
                count: 0,
                color: getServiceColor(name)
            }
        }

        grouped[name].count++
    })

    return Object.values(grouped)
}

function getServiceColor(service) {
    const colors = {
        'Ferti Irrigação': 'blue',
        'Pulverização': 'teal',
        'Adubação': 'brown',
        'Colheita': 'green',
        'Plantio': 'orange-darken-3'
    }

    return colors[service] || 'grey'
}

function getItems(attempt = 1) {
    loading.value = true
    api.get('get_plantings').then(response => {
        items.value = response.data
        loading.value = false
    }).catch(error => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getItems(attempt + 1), 1000)
        } else {
            snackbar.open({ preset: 'error' })
            loading.value = false
        }
    })
}

function getCrops(attempt = 1) {
    loading_crops.value = true
    api.get('get_crops').then(response => {
        crops.value = response.data
        const all_varieties = []
        crops.value.map(crop => {
            if (crop.varieties?.length) {
                all_varieties.push(...crop.varieties.split(';'))
            }
        })
        varieties.value = [...new Set(all_varieties)]
        loading_crops.value = false
    }).catch(error => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getCrops(attempt + 1), 1000)
        } else {
            snackbar.open({ preset: 'error' })
            loading_crops.value = false
        }
    })
}

function getPivots(attempt = 1) {
    loading_pivots.value = true
    api.get('get_pivots').then(response => {
        pivots.value = response.data
        loading_pivots.value = false
    }).catch(error => {
        console.log(error)
        if (attempt <= 5) {
            setTimeout(() => getPivots(attempt + 1), 1000)
        } else {
            snackbar.open({ preset: 'error' })
            loading_pivots.value = false
        }
    })
}

function clearFilters() {
    Object.keys(filters).forEach(key => {
        filters[key] = []
    })
}

function editPlanting(id, values) {
    loading_status[id] = true
    api.put('edit_planting/' + id, values).then(response => {
        items.value.find(item => item.id == id).status = response.data.status
        loading_status[id] = false
    }).catch(error => {
        console.log(error)
        snackbar.open({ preset: 'error' })
        loading_status[id] = false
    })
}

function formatDateBR(date) {
    if (!date) return 'Não definido'

    const [year, month, day] = date.split('-')
    return `${day}/${month}/${year}`
}

function getDateType(date) {
    if (!date) return 'Antigos'

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const target = new Date(date + 'T00:00:00')

    if (target < today) return 'Antigos'
    if (target > today) return 'Futuros'

    return 'Hoje!'
}

function getDaysAfterPlanting(date) {
    if (!date) return 0

    const planting = new Date(date + 'T00:00:00')
    const today = new Date()

    planting.setHours(0, 0, 0, 0)
    today.setHours(0, 0, 0, 0)

    return Math.floor((today - planting) / 86400000)
}

function getCropEmoji(name) {
    if (!name) return '🌱'

    const crop = name.toLowerCase()

    const dictionary = {
        alho: '🧄',
        cenoura: '🥕',
        milho: '🌽',
        soja: '🫘',
        batata: '🥔',
        abacate: '🥑',
        tomate: '🍅',
        cebola: '🧅',
        cana: '🎋',
        café: '☕',
        cafe: '☕',
        trigo: '🌾',
        feijão: '🫘',
        feijao: '🫘',
        brachiaria: '🌿',
        braquiaria: '🌿'
    }

    for (const key in dictionary) {
        if (new RegExp(`\\b${key}\\b`, 'i').test(crop)) {
            return dictionary[key]
        }
    }

    return '🌱'
}

function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, key) => acc?.[key], obj)
}

function editItem(edited_item) {
    items.value.splice(items.value.findIndex(item => item.id == edited_item.id), 1, edited_item)
}

function editFieldRecordItem(edited_item) {
    const planting = items.value.find(
        item => item.id == edited_item.planting_id
    )

    if (!planting?.field_records) return

    const index = planting.field_records.findIndex(
        record => record.id == edited_item.id
    )

    if (index !== -1) {
        planting.field_records.splice(index, 1, edited_item)
    }
}

function popItem(id) {
    items.value = items.value.filter(item => item.id != id)
}

function pushNewFieldRecord(item) {
    const planting = items.value.find(
        planting => planting.id == item.planting_id
    )
    if (!planting) return
    if (!planting.field_records) {
        planting.field_records = []
    }
    planting.field_records.unshift(item)
}

function pushNewItem(item) {
    items.value.unshift(item)
}

function openEditDialog(item) {
    Object.assign(edit_dialog_data, item)
    edit_dialog.value = true
}

function openDeleteDialog(item) {
    Object.assign(delete_dialog_data, item)
    delete_dialog.value = true
}
</script>

<style scoped>
.alerts-box-dark {
    border: 1px solid rgba(255, 255, 255, 0.144);
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.034);
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
}

.alerts-box-light {
    border: 1px solid rgba(0, 0, 0, 0.315);
    border-radius: 12px;
    overflow: hidden;
    background: rgba(0, 0, 0, 0.034);
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
}

.alerts-header-dark {
    background: rgba(0, 0, 0, 0.35);
    padding: 10px 12px;
    display: flex;
    align-items: center;
}

.alerts-header-light {
    background: rgba(0, 0, 0, 0.171);
    padding: 10px 12px;
    display: flex;
    align-items: center;
}

.alerts-row {
    display: flex;
    align-items: center;
    padding: 10px;
    cursor: pointer;
    transition: all 0.18s ease;
}

:deep(.v-switch) {
    height: 30px;
}

.alerts-danger-border {
    border: 1px solid rgba(255, 70, 70, 0.9) !important;
    box-shadow: rgba(87, 16, 16, 0.459) 0px 0px 20px 2px;
}

.timeline-card {
    transition: all .18s ease;
}

.timeline-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, .28);
}

:deep(.v-timeline-item__body) {
    width: 100%;
    padding-left: 10px;
}

.menu-scroll {
    overflow-x: auto;
    overflow-y: hidden;
    flex-wrap: nowrap;
    -webkit-overflow-scrolling: touch;
    /* iOS smooth */
}

.menu-scroll::-webkit-scrollbar {
    height: 6px;
}

.menu-scroll::-webkit-scrollbar-thumb {
    background: rgba(120, 120, 120, 0.4);
    border-radius: 4px;
}

.menu-scroll-item {
    flex: 0 0 auto;
    /* 🔑 impede o chip de encolher/quebrar */
}

.nutrient-card {
    padding: 15px;
    background-color: rgba(50, 50, 50, 0.6);
    border-radius: 15px;
}

/* CONTAINER COM SCROLL X + Y */
.nutrition-table-scroll-dark,
.nutrition-table-scroll-light {
    overflow: auto;
    max-height: 650px; /* ajuste aqui */
    -webkit-overflow-scrolling: touch;
    padding-bottom: 2px;
    border-radius: 10px;
    position: relative;
}

/* DARK */
.nutrition-table-scroll-dark {
    border: solid 2px rgba(84, 84, 84, 0.38);
    background-color: rgba(0, 0, 0, 0.2);
}

/* LIGHT */
.nutrition-table-scroll-light {
    border: solid 2px rgba(105, 104, 104, 0.421);
    background-color: rgba(255, 255, 255, 0.885);
}

.nutrition-grid {
    display: grid;
    gap: 6px;
    align-items: center;
    min-width: max-content;
    padding: 5px 5px 5px 15px;
}

/* HEADER FIXO */
.nutrition-header-dark,
.nutrition-header-light {
    position: sticky;
    top: 0;
    z-index: 30;
    padding: 15px 15px 10px;
    border-bottom: 2px solid rgba(150,150,150,.4);
}

.nutrition-header-dark {
    background: rgba(10,10,10,.95);
}

.nutrition-header-light {
    background: rgba(230,230,230,.96);
}

/* RODAPÉ FIXO */
.nutrition-total-dark,
.nutrition-total-light {
    position: sticky;
    z-index: 25;
    padding: 15px 15px 10px;
    border-top: 2px solid rgba(150,150,150,.4);
}

/* PRIMEIRA LINHA FIXA (fica acima da segunda) */
.nutrition-total-first {
    bottom: 48px; /* altura da última linha */
}

/* SEGUNDA LINHA FIXA */
.nutrition-total-second {
    bottom: 0;
}

.nutrition-total-dark {
    background: rgba(35,35,35,.96);
}

.nutrition-total-light {
    background: rgba(245,245,245,.97);
}

.alert-bg-red-light {
    background-color: rgba(255, 35, 35, 0.363);
}

.alert-bg-red-dark {
    background-color: rgba(255, 0, 0, 0.097);
}

.alert-bg-orange-light {
    background-color: rgba(255, 213, 129, 0.619);
}

.alert-bg-orange-dark {
    background-color: rgba(255, 136, 0, 0.151);
}

.alerts-row:hover {
    transform: scale(101%);
    box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px;
}

.alert-pending-records {
    transition: 0.3s;
    display: flex;
    align-items: start;
    margin-bottom: 20px;
    margin-top: 10px;
    box-shadow: rgba(0, 0, 0, 0.17) 0px -23px 25px 0px inset, rgba(0, 0, 0, 0.089) 0px -36px 30px 0px inset, rgba(0, 0, 0, 0.103) 0px -79px 40px 0px inset, rgba(0, 0, 0, 0) 0px 2px 1px, rgba(0, 0, 0, 0) 0px 4px 2px, rgba(0, 0, 0, 0.041) 0px 8px 4px, rgba(0, 0, 0, 0.041) 0px 16px 8px, rgba(0, 0, 0, 0) 0px 32px 16px;
}

.alert-pending-records:hover {
    cursor: pointer;
    transform: scale(0.99)
}

</style>