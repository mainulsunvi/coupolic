<script setup>
import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import CardBox from '../global/CardBox.vue';

defineProps({
	id: {
		type: String,
		default: 'datetime-field'
	},
	label: {
		type: String,
		default: 'Select Date and Time'
	},
	name: {
		type: String,
		default: 'coupolic-flatpickr-input'
	},
	placeholder: {
		type: String,
		default: 'Select Date and Time'
	},
	modelValue: {
		type: [String, Number],
		default: null
	},
	config: {
		type: Object,
		default: () => ({})
	},
	subtitle: {
		type: String,
		default: ''
	},
})

const emit = defineEmits(['update:modelValue']);

</script>
<template>
	<div class="coupolic-input-container">
		<label :for="name" class="coupolic-input-label">{{ label }}</label>
		<CardBox :width=75>
			<Flatpickr @input="emit('update:modelValue', $event.target.value)" :placeholder="placeholder"
				:value="modelValue" :name="name" :config="config" @change="flatPickrChange" class="coupolic-input-field" />
			<p v-if="subtitle" class="coupolic-input-subtitle">{{ subtitle }}</p>
		</CardBox>
	</div>
</template>
