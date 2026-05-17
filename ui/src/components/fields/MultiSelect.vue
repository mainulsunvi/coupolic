<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import CardBox from '../global/CardBox.vue'

const props = defineProps({
  options: {
    type: Object,
    default: function() { return {} }
  },
  name: {
    type: String,
    default: 'multi-select-field'
  },
  id: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  label: {
    type: String,
    default: 'Select Options'
  },
  value: {
    type: Array,
    default: function() { return [] }
  },
  subtitle: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select options...'
  }
})

const emit = defineEmits(['input'])

const isOpen = ref(false)
const searchQuery = ref('')
const selectedOptions = ref([])
const multiselectRef = ref(null)

// Handle click outside to close dropdown
function handleClickOutside(event) {
  if (multiselectRef.value && !multiselectRef.value.contains(event.target)) {
    closeDropdown()
  }
}

// Initialize selected options from prop value
watch(function() { return props.value }, function(newValue) {
  if (Array.isArray(newValue)) {
    selectedOptions.value = [...newValue]
  }
}, { immediate: true })

const availableOptions = computed(function() {
  const optionsArray = Object.keys(props.options).map(function(key) {
    const label = props.options[key]
    return {
      key: key,
      label: label,
      // Extract more info from the label if it contains structured data like "[ID] - Title - Price"
      details: extractDetails(key, label)
    }
  })

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    return optionsArray.filter(function(option) {
      return option.label.toLowerCase().includes(query) || option.key.toLowerCase().includes(query)
    })
  }

  return optionsArray
})

function extractDetails(key, label) {
  // Try to extract structured information from common formats
  // Example: "[123] - Product Name - $99.99" or similar patterns
  const parts = label.split(' - ')

  if (parts.length >= 2) {
    return {
      id: key,
      fullLabel: label,
      name: parts[1] || parts[0],
      price: parts[2] || '',
      extra: parts.length > 2 ? parts.slice(2).join(' - ') : ''
    }
  }

  return {
    id: key,
    fullLabel: label,
    name: label,
    price: '',
    extra: ''
  }
}

function getTagLabel(key) {
  const label = props.options[key] || key
  const parts = label.split(' - ')

  // Show product name primarily, with price if available
  if (parts.length >= 2) {
    const name = parts[1] || parts[0]
    const price = parts[2] || ''
    return price ? name + ' (' + price + ')' : name
  }

  return label
}

const selectedLabels = computed(function() {
  return selectedOptions.value
})

function toggleOption(key) {
  const index = selectedOptions.value.indexOf(key)

  if (index === -1) {
    selectedOptions.value.push(key)
  } else {
    selectedOptions.value.splice(index, 1)
  }

  emit('input', [...selectedOptions.value])
}

function removeOption(key, event) {
  event.stopPropagation()
  const index = selectedOptions.value.indexOf(key)
  if (index !== -1) {
    selectedOptions.value.splice(index, 1)
    emit('input', [...selectedOptions.value])
  }
}

function selectAll() {
  const allKeys = Object.keys(props.options)
  selectedOptions.value = [...allKeys]
  emit('input', [...selectedOptions.value])
}

function deselectAll() {
  selectedOptions.value = []
  emit('input', [])
}

function clearAll() {
  selectedOptions.value = []
  emit('input', [])
  searchQuery.value = ''
}

function toggleDropdown() {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

function closeDropdown() {
  isOpen.value = false
  searchQuery.value = ''
}

// Mount click outside listener
onMounted(function() {
  document.addEventListener('click', handleClickOutside)
})

// Clean up click outside listener
onUnmounted(function() {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div class="coupolic-input-container">
    <label :for="name" class="coupolic-input-label">{{ label }}</label>
    <CardBox :width=75>
      <div ref="multiselectRef" class="custom-multiselect" :class="{ 'is-disabled': disabled, 'is-open': isOpen }">
        <div
          class="multiselect-trigger"
          @click="toggleDropdown"
          :class="{ 'is-disabled': disabled }"
        >
          <div class="selected-values">
            <div v-if="selectedOptions.length === 0" class="placeholder">{{ placeholder }}</div>
            <div v-else class="selected-labels">
              <div
                v-for="key in selectedLabels"
                :key="key"
                class="selected-tag"
              >
                <span class="tag-content">{{ getTagLabel(key) }}</span>
                <span
                  class="tag-remove"
                  @click.stop="removeOption(key, $event)"
                  v-if="!disabled"
                >×</span>
              </div>
            </div>
          </div>
          <div class="multiselect-arrow">
            <svg v-if="!isOpen" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6 9L1 4H11L6 9Z" fill="currentColor"/>
            </svg>
            <svg v-else width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>

        <div v-if="isOpen" class="multiselect-dropdown">
          <div class="multiselect-header">
            <div class="multiselect-actions">
              <button
                type="button"
                class="action-btn select-all-btn"
                @click.stop="selectAll"
                :disabled="selectedOptions.length === Object.keys(options).length"
              >
                Select All
              </button>
              <button
                type="button"
                class="action-btn deselect-all-btn"
                @click.stop="deselectAll"
                :disabled="selectedOptions.length === 0"
              >
                Deselect All
              </button>
              <button
                type="button"
                class="action-btn clear-all-btn"
                @click.stop="clearAll"
                :disabled="selectedOptions.length === 0"
              >
                Clear All
              </button>
            </div>
          </div>

          <div class="multiselect-search">
            <input
              v-model="searchQuery"
              type="text"
              class="search-input"
              :placeholder="placeholder"
              @click.stop
            />
          </div>

          <div class="multiselect-options">
            <div
              v-for="option in availableOptions"
              :key="option.key"
              class="multiselect-option"
              :class="{ 'is-selected': selectedOptions.includes(option.key) }"
              @click="toggleOption(option.key)"
            >
              <div class="option-checkbox">
                <div v-if="selectedOptions.includes(option.key)" class="checkbox-checked">✓</div>
              </div>
              <div class="option-content">
                <div class="option-label">{{ option.details.name || option.label }}</div>
                <div v-if="option.details.price" class="option-extra">{{ option.details.price }}</div>
                <div v-if="option.details.extra" class="option-extra">{{ option.details.extra }}</div>
              </div>
            </div>

            <div v-if="availableOptions.length === 0" class="no-results">
              No options found
            </div>
          </div>
        </div>

        <!-- Hidden select for form submission -->
        <select
          :name="name"
          :id="id"
          :disabled="disabled"
          multiple
          style="display: none;"
        >
          <option v-for="(label, key) in options" :key="key" :value="key" :selected="selectedOptions.includes(key)">
            {{ label }}
          </option>
        </select>
      </div>
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

.custom-multiselect {
  position: relative;
  width: 100%;
}

.multiselect-trigger {
  min-height: 38px;
  padding: 0.5rem 2rem 0.5rem 0.875rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-medium);
  background: var(--bg-primary);
  cursor: pointer;
  transition: all var(--transition-base);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.multiselect-trigger:hover:not(.is-disabled) {
  border-color: var(--primary-light);
}

.custom-multiselect.is-open .multiselect-trigger {
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
}

.multiselect-trigger.is-disabled {
  background: var(--bg-tertiary);
  cursor: not-allowed;
  opacity: 0.6;
}

.selected-values {
  flex: 1;
  min-width: 0;
}

.placeholder {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.selected-labels {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}

.selected-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.5rem;
  background: var(--accent-blue);
  color: white;
  border-radius: var(--radius-md);
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.3;
  max-width: 250px;
  word-wrap: break-word;
}

.tag-content {
  flex: 1;
  min-width: 0;
}

.tag-remove {
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: bold;
  opacity: 0.8;
  transition: all var(--transition-fast);
  flex-shrink: 0;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.tag-remove:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.3);
}

.multiselect-arrow {
  display: flex;
  align-items: center;
  color: var(--text-secondary);
  pointer-events: none;
}

.multiselect-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1000;
  margin-top: 0.25rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  max-height: 400px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.multiselect-header {
  padding: 0.5rem;
  border-bottom: 1px solid var(--border-light);
  background: var(--bg-tertiary);
}

.multiselect-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.action-btn {
  padding: 0.375rem 0.75rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-sm);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.action-btn:hover:not(:disabled) {
  background: var(--bg-secondary);
  border-color: var(--primary-light);
}

.action-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.select-all-btn:hover:not(:disabled) {
  background: var(--success-bg);
  color: var(--success);
  border-color: var(--success);
}

.deselect-all-btn:hover:not(:disabled) {
  background: var(--warning-bg);
  color: var(--warning);
  border-color: var(--warning);
}

.clear-all-btn:hover:not(:disabled) {
  background: var(--error-bg);
  color: var(--error);
  border-color: var(--error);
}

.multiselect-search {
  padding: 0.5rem;
  border-bottom: 1px solid var(--border-light);
}

.search-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-family: 'Inter', sans-serif;
  outline: none;
}

.search-input:focus {
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
}

.multiselect-options {
  flex: 1;
  overflow-y: auto;
  max-height: 250px;
}

.multiselect-options::-webkit-scrollbar {
  width: 8px;
}

.multiselect-options::-webkit-scrollbar-track {
  background: var(--bg-tertiary);
}

.multiselect-options::-webkit-scrollbar-thumb {
  background: var(--border-medium);
  border-radius: var(--radius-sm);
}

.multiselect-option {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  cursor: pointer;
  transition: all var(--transition-fast);
  border-bottom: 1px solid var(--border-light);
}

.multiselect-option:last-child {
  border-bottom: none;
}

.multiselect-option:hover {
  background: var(--bg-tertiary);
}

.multiselect-option.is-selected {
  background: var(--info-bg);
  color: var(--accent-blue);
}

.option-checkbox {
  flex-shrink: 0;
  width: 18px;
  height: 18px;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-sm);
  background: var(--bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-fast);
  margin-top: 2px;
}

.multiselect-option.is-selected .option-checkbox {
  background: var(--accent-blue);
  border-color: var(--accent-blue);
}

.checkbox-checked {
  color: white;
  font-size: 0.75rem;
  font-weight: bold;
}

.option-content {
  flex: 1;
  min-width: 0;
}

.option-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
  line-height: 1.3;
  margin-bottom: 0.125rem;
}

.option-extra {
  font-size: 0.75rem;
  color: var(--text-secondary);
  line-height: 1.2;
}

.multiselect-option.is-selected .option-label {
  color: var(--accent-blue);
}

.multiselect-option.is-selected .option-extra {
  color: var(--accent-blue);
  opacity: 0.8;
}

.no-results {
  padding: 1rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.875rem;
}
</style>