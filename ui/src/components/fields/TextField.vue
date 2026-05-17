<script setup>
import CardBox from '../global/CardBox.vue';

const props = defineProps({
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
	height: {
		type: Number,
		default: 100
	}
})

const emit = defineEmits(['input'])

function handleInput(event) {
	emit('input', event.target.value)
}

</script>
<template>
	<div class="coupolic-input-container">
		<label v-if="label" :for="name" class="coupolic-input-label">{{ label }}</label>
		<CardBox :width=75>
			<textarea :placeholder="placeholder" :name="name" :id="id" :disabled="disabled"
				:readonly="readonly" :value="value" @input="handleInput" class="coupolic-input-field" :style="{ height: height + 'px' }" />
			<p v-if="subtitle" class="coupolic-input-subtitle">{{ subtitle }}</p>
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
  padding: 0.75rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-medium);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 400;
  transition: all var(--transition-base);
  font-family: 'Inter', sans-serif;
  resize: vertical;
  min-height: 80px;
  line-height: 1.5;
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

.coupolic-input-field:disabled {
  background: var(--bg-tertiary);
  cursor: not-allowed;
  opacity: 0.6;
}
</style>