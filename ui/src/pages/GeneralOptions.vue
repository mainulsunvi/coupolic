<script setup>
import CardBox from '@/components/global/CardBox.vue';
import { RouterLink, RouterView } from 'vue-router'
import Icon from '@/components/global/LucidIcon.vue'
// import { CircleAlert } from 'lucide-vue-next'
</script>

<template>
	<div class="inner-box">
		<CardBox :width=20 :height=40>
			<div class="coupolic-general-sub-nav-menus">
				<div class="sub-nav-menu" :class="{ 'nav-active': $route.name === 'general-options-general' }">
					<Icon name="Settings" :size=16 :stroke-width=2 />
					<RouterLink :to="{ name: 'general-options-general'}">General</RouterLink>
				</div>
				<div class="sub-nav-menu" :class="{ 'nav-active': $route.name === 'general-options-restriction' } ">
					<Icon name="TriangleAlert" :size=16 :stroke-width=2 />
					<RouterLink :to="{ name: 'general-options-restriction'}">Usage Restriction</RouterLink>
				</div>
				<div class="sub-nav-menu" :class="{ 'nav-active': $route.name === 'general-options-limit' }">
					<Icon name="Hourglass" :size=16 :stroke-width=2 />
				<RouterLink :to="{ name: 'general-options-limit' }">Usage Limit</RouterLink>
			</div>
	</div>
	</CardBox>
	<CardBox :width=80>
		<RouterView />
	</CardBox>
	</div>

</template>