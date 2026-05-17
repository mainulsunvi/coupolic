<script setup>
import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import CardBox from '../global/CardBox.vue';
import { computed } from 'vue'

const props = defineProps({
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
	value: {
		type: [String, Number],
		default: null
	},
	config: {
		type: Object,
		default: function() { return {} }
	},
	subtitle: {
		type: String,
		default: ''
	},
	error: {
		type: String,
		default: ''
	},
	warning: {
		type: String,
		default: ''
	},
})

const emit = defineEmits(['input'])

function handleInput(event) {
	// Flatpickr returns the date string directly, not an event
	emit('input', event)
}

const hasError = computed(function() {
	return !!props.error
})

const hasWarning = computed(function() {
	return !!props.warning
})

</script>
<template>
	<div class="coupolic-input-container" :class="{ 'has-error': hasError, 'has-warning': hasWarning }">
		<label :for="name" class="coupolic-input-label">{{ label }}</label>
		<CardBox :width=75>
			<Flatpickr @change="handleInput" :placeholder="placeholder"
				:modelValue="value" :name="name" :config="config" class="coupolic-input-field" />
			<p v-if="subtitle" class="coupolic-input-subtitle">{{ subtitle }}</p>
			<p v-if="error" class="coupolic-input-error">{{ error }}</p>
			<p v-if="warning" class="coupolic-input-warning">{{ warning }}</p>
		</CardBox>
	</div>
</template>

<style scoped>
.coupolic-input-container {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  padding: 1.25rem;
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
}

.coupolic-input-container:focus-within {
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
}

.coupolic-input-container.has-error {
  border-color: var(--error);
  background: var(--error-bg);
}

.coupolic-input-container.has-error:focus-within {
  box-shadow: 0 0 0 2px var(--error-bg);
}

.coupolic-input-container.has-warning {
  border-color: var(--warning);
  background: var(--warning-bg);
}

.coupolic-input-container.has-warning:focus-within {
  box-shadow: 0 0 0 2px var(--warning-bg);
}

.coupolic-input-label {
  font-weight: 500;
  color: var(--text-primary);
  width: 35%;
  margin-top: 0.25rem;
  font-size: 0.875rem;
}

.coupolic-input-subtitle {
  font-size: 0.8125rem;
  font-weight: 400;
  color: var(--text-muted);
  margin: 0.25rem 0 0 0;
  line-height: 1.4;
}

.coupolic-input-field {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-medium);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 400;
  transition: all var(--transition-base);
  font-family: 'Inter', sans-serif;
}

.coupolic-input-field::placeholder {
  color: var(--text-muted);
}

.coupolic-input-field:focus {
  outline: none;
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
}

.coupolic-input-field:hover:not(:focus) {
  border-color: var(--primary-light);
}

.coupolic-input-error,
.coupolic-input-warning {
  font-size: 0.8125rem;
  margin: 0.5rem 0 0 0;
  line-height: 1.4;
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.coupolic-input-error {
  color: var(--error);
  font-weight: 500;
}

.coupolic-input-warning {
  color: var(--warning);
  font-weight: 500;
}
</style>
