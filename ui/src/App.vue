<script setup>
import { RouterLink, RouterView } from 'vue-router'
import NavMenu from './components/settings/NavMenu.vue';
</script>

<template>
  <div class="coupolic-container">
    <header class="coupolic-settings-header">
      <div class="coupolic-header-upper-section">
        <span class="app-title">Coupolic</span>
      </div>
      <NavMenu />
    </header>
    <div class="inner-box">
      <div class="inner-section-20">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Beatae porro, voluptate magni ratione amet consequatur nihil dignissimos sequi est necessitatibus cumque earum pariatur iure et aut, rerum, numquam labore quidem. Sed, quod libero modi corrupti odio accusamus earum voluptates laboriosam?</div>
      <div class="inner-section-80">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Beatae porro, voluptate magni ratione amet consequatur nihil dignissimos sequi est necessitatibus cumque earum pariatur iure et aut, rerum, numquam labore quidem. Sed, quod libero modi corrupti odio accusamus earum voluptates laboriosam?</div>
    </div>

    <RouterView />
  </div>

</template>
