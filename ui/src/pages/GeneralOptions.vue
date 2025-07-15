<script setup>
import CardBox from '@/components/global/CardBox.vue';
import { RouterLink, RouterView } from 'vue-router'
// import Icon from '@/components/global/LucidIcon.vue'
import { Icon } from "@iconify/vue";
</script>

<template>
	<div class="inner-box">
		<CardBox :width=16 :height=70>
			<div class="coupolic-general-sub-nav-menus">
				<div class="sub-nav-menu" :class="{ 'nav-active': $route.name === 'general-options-general' }">
					<Icon icon="proicons:settings" width="18" height="18" />
					<RouterLink :to="{ name: 'general-options-general' }">General</RouterLink>
				</div>
				<div class="sub-nav-menu" :class="{ 'nav-active': $route.name === 'general-options-restriction' }">
					<Icon icon="proicons:alert-rhombus" width="18" height="18" />
					<RouterLink :to="{ name: 'general-options-restriction' }">Usage Restriction</RouterLink>
				</div>
				<div class="sub-nav-menu" :class="{ 'nav-active': $route.name === 'general-options-limit' }">
					<Icon icon="proicons:timer-off" width="18" height="18" />
					<RouterLink :to="{ name: 'general-options-limit' }">Usage Limit</RouterLink>
				</div>
			</div>
		</CardBox>
		<CardBox :width=80 :padding=0 :background=false :border=false>
			<RouterView />
		</CardBox>
	</div>

</template>