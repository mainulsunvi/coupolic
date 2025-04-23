<script setup>
import CardBox from '../global/CardBox.vue';

defineProps({
	name: {
		type: String,
		default: 'select-field'
	},
	id: {
		type: String,
		default: ''
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
		default: 'Select Option'
	},
	options: {
		type: Object,
		default: {}
	},
	subtitle: {
		type: String,
		default: ''
	},
	})
</script>

<template>
	<div class="coupolic-input-container">
		<label :for="name" class="coupolic-input-label">{{ label }}</label>
		<CardBox :width=75>
			<select :name="name" :id="id" :disabled="disabled" :readonly="readonly" class="coupolic-input-field">
				<option value="" disabled selected>Select an option</option>
				<option v-for="(value, key) in options" :key="key" :value="key">{{ value }}</option>
			</select>
			<p v-if="subtitle" class="coupolic-input-subtitle">{{ subtitle }}</p>
		</CardBox>
	</div>
</template>