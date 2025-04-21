<script setup>
import { RouterLink, RouterView } from 'vue-router'
import AppHeader from '@/components/global/AppHeader.vue'
import AppFooter from '@/components/global/AppFooter.vue'
import { useRoute } from 'vue-router';
</script>

<template>
  <div class="coupolic-container">
    <AppHeader />
    <RouterView />
    <AppFooter />
  </div>

</template>
