<script setup>
import { toRef } from 'vue';

const props = defineProps({
	modelValue: {
		type: Array,
		default: () => []
	},
	options: {
		type: Array,
		required: true
	},
	placeholder: {
		type: String,
		default: 'Select options'
	}
});

const emit = defineEmits(['update:modelValue']); // Define the emit event

const selectedValues = toRef(props, 'modelValue'); // Use toRef on props to make modelValue reactive

function updateValue() {
	emit('update:modelValue', selectedValues.value); // Emit the updated value
}
</script>

<template>
	<div class="select2-field">
		<select
			multiple
			v-model="selectedValues"
			@change="updateValue"
			class="select2"
		>
			<option disabled value="">{{ placeholder }}</option>
			<option v-for="option in options" :key="option.value" :value="option.value">
				{{ option.label }}
			</option>
		</select>
	</div>
</template>

<style scoped>
.select2-field {
	width: 100%;
}

.select2 {
	width: 100%;
	padding: 8px;
	border: 1px solid #ccc;
	border-radius: 4px;
}
</style>
