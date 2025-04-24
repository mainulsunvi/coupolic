<script setup>
import CardBox from '../global/CardBox.vue';

defineProps({
	type: {
		type: String,
		required: true,
	},
	placeholder: {
		type: String,
		default: 'Enter the Text'
	},
	name: {
		type: String,
		default: 'text-field'
	},
	id: {
		type: String,
		default: 'text-field'
	},
	disabled: {
		type: Boolean,
		default: false
	},
	readonly: {
		type: Boolean,
		default: false
	},
	label: {
		type: String,
		default: 'Enter Text'
	},
	value: {
		type: [String, Number],
		default: null
	},
	subtitle: {
		type: String,
		default: ''
	},
})

</script>
<template>
	<div class="coupolic-input-container">
		<label v-if="label" :for="name" class="coupolic-input-label">{{ label }}</label>
		<CardBox :width=75>
			<input :type="type" :placeholder="placeholder" :name="name" :id="id" :disabled="disabled"
				:readonly="readonly" :value=value class="coupolic-input-field"/>
			<p v-if="subtitle" class="coupolic-input-subtitle">{{ subtitle }}</p>
		</CardBox>
	</div>
</template>