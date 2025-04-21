<script setup>
import { defineProps } from 'vue'

defineProps({
	height: {
		type: Number,
		default: 100
	},
	width: {
		type: Number,
		default: 100
	},
	padding: {
		type: Number,
		default: 24
	}
});

</script>

<template>
	<div class="inner-section" :style="{ height: height + '%', width: width + '%', padding: padding + 'px' }">
		<slot />
	</div>
</template>