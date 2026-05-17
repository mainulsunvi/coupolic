<script setup>
import CardBox from '../global/CardBox.vue';
import { computed } from 'vue'

const props = defineProps({
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
	error: {
		type: String,
		default: ''
	},
	warning: {
		type: String,
		default: ''
	},
	value: {
		type: [String, Number],
		default: ''
	},
})

const emit = defineEmits(['input'])

function handleChange(event) {
	emit('input', event.target.value)
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
			<select :name="name" :id="id" :disabled="disabled" :readonly="readonly" :value="value" @change="handleChange" class="coupolic-input-field">
				<option value="" disabled selected>Select an option</option>
				<option v-for="(value, key) in options" :key="key" :value="key">{{ value }}</option>
			</select>
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
  padding: 0.625rem 2.5rem 0.625rem 0.875rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-medium);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 400;
  transition: all var(--transition-base);
  font-family: 'Inter', sans-serif;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23475569' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  cursor: pointer;
}

.coupolic-input-field:focus {
  outline: none;
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
}

.coupolic-input-field:hover:not(:focus) {
  border-color: var(--primary-light);
}

.coupolic-input-field:disabled {
  background: var(--bg-tertiary);
  cursor: not-allowed;
  opacity: 0.6;
}

.coupolic-input-field option {
  padding: 0.5rem;
  background: var(--bg-primary);
  color: var(--text-primary);
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