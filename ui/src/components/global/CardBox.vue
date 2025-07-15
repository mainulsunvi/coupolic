<script setup>

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
		type: [Number, Array ],
		default: 24
	},
	background: {
		type: Boolean,
		default: true
	},
	border: {
		type: Boolean,
		default: true
	},
});

</script>

<template>
	<div class="inner-section"
		:style="{ height: height + '%', width: width + '%', background: background ? 'var(--coupolic-palette-50)' : 'none', border: border ? '1px solid var(--coupolic-palette-100)' : 'none', padding: padding + 'px' }">
		<slot />
	</div>
</template>