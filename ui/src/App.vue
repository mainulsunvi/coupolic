<script setup>
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { computed } from 'vue'
import AppHeader from '@/components/global/AppHeader.vue'
import AppFooter from '@/components/global/AppFooter.vue'

const route = useRoute()

// Check if current route is settings page
const isSettingsPage = computed(() => route.name === 'settings')
</script>

<template>
  <div class="coupolic-container">
    <AppHeader />
    <RouterView />
    <AppFooter v-if="!isSettingsPage" />
  </div>

</template>
