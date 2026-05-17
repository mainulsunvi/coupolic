<script setup>
import CardBox from '../global/CardBox.vue';

const props = defineProps({
    name: {
        type: String,
        default: "checkbox-field"
    },
    label: {
        type: String,
        default: "Checkbox Label"
    },
    subtitle: {
        type: String,
        default: "The Subtitle of checkbox"
    },
    value: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['input'])

function handleInput(event) {
    emit('input', event.target.checked)
}

</script>
<template>
    <div class="coupolic-input-container">
        <label :for=name class="coupolic-input-label">{{ label }}</label>
        <CardBox :width=75>
            <input :name=name type="checkbox" class="coupolic-input-checkbox" :checked="value" @input="handleInput"/>
            <span class="coupolic-input-subtitle">{{ subtitle }}</span>
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
  margin-left: 0.5rem;
}

.coupolic-input-checkbox {
  appearance: none;
  width: 18px;
  height: 18px;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-sm);
  background: var(--bg-primary);
  cursor: pointer;
  position: relative;
  transition: all var(--transition-base);
  flex-shrink: 0;
}

.coupolic-input-checkbox:hover {
  border-color: var(--primary-light);
}

.coupolic-input-checkbox:checked {
  background: var(--accent-blue);
  border-color: var(--accent-blue);
}

.coupolic-input-checkbox:checked::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(45deg);
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
}

.coupolic-input-checkbox:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--info-bg);
}
</style>