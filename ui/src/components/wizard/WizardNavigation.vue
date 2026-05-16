<script setup>
import { inject } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
  canGoBack: {
    type: Boolean,
    required: true,
  },
  canGoNext: {
    type: Boolean,
    required: true,
  },
  isLastStep: {
    type: Boolean,
    required: true,
  },
  currentStep: {
    type: Number,
    required: true,
  },
  hasUnsavedChanges: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['back', 'next', 'save-draft', 'save-local'])

function handleBackClick() {
  emit('back')
}

function handleNextClick() {
  if (props.isLastStep) {
    emit('generate')
  } else {
    emit('next')
  }
}

function handleSaveDraft() {
  emit('save-draft')
}

function handleSaveLocal() {
  emit('save-local')
}
</script>

<template>
  <div class="wizard-navigation">
    <div class="navigation-left">
      <button
        v-if="hasUnsavedChanges"
        @click="handleSaveLocal"
        class="nav-button save-local-button"
        type="button"
      >
        <Icon icon="proicons:save" width="16" height="16" />
        Save Locally
      </button>

      <button
        @click="handleSaveDraft"
        class="nav-button save-draft-button"
        type="button"
      >
        <Icon icon="proicons:cloud-save" width="16" height="16" />
        Save Draft
      </button>
    </div>

    <div class="navigation-right">
      <button
        v-if="canGoBack"
        @click="handleBackClick"
        class="nav-button back-button"
        type="button"
      >
        <Icon icon="proicons:arrow-left" width="16" height="16" />
        Back
      </button>

      <button
        v-if="canGoNext"
        @click="handleNextClick"
        class="nav-button next-button"
        type="button"
      >
        <Icon v-if="!isLastStep" icon="proicons:arrow-right" width="16" height="16" />
        <Icon v-else icon="proicons:check" width="16" height="16" />
        {{ isLastStep ? 'Generate Coupons' : 'Next' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.wizard-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
  margin-top: 30px;
  gap: 20px;
}

.navigation-left,
.navigation-right {
  display: flex;
  gap: 10px;
}

.nav-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-button {
  background: white;
  color: #666;
  border: 1px solid #ddd;
}

.back-button:hover:not(:disabled) {
  background: #f5f5f5;
  border-color: #ccc;
}

.next-button {
  background: #4CAF50;
  color: white;
}

.next-button:hover:not(:disabled) {
  background: #45a049;
}

.save-draft-button {
  background: #2196F3;
  color: white;
}

.save-draft-button:hover {
  background: #1976D2;
}

.save-local-button {
  background: #FF9800;
  color: white;
}

.save-local-button:hover {
  background: #F57C00;
}

.nav-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .wizard-navigation {
    flex-direction: column;
    align-items: stretch;
  }

  .navigation-left,
  .navigation-right {
    flex-direction: column;
  }

  .nav-button {
    width: 100%;
    justify-content: center;
  }
}
</style>