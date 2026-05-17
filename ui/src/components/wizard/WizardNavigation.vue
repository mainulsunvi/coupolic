<script setup>
import { inject } from 'vue'
import { Icon } from '@iconify/vue'
import { RouterLink } from 'vue-router'

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
})

const emit = defineEmits(['back', 'next', 'generate'])

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
</script>

<template>
  <div class="wizard-navigation">
    <div class="navigation-left">
      <RouterLink to="/logs" class="nav-link history-link">
        <Icon icon="proicons:database" width="16" height="16" />
        View History
      </RouterLink>
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
  padding: 1rem 0;
  margin-top: 2rem;
}

.navigation-left,
.navigation-right {
  display: flex;
  gap: 0.75rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  background: var(--bg-primary);
  color: var(--text-secondary);
  text-decoration: none;
}

.history-link:hover {
  border-color: var(--accent-blue);
  color: var(--accent-blue);
  background: var(--info-bg);
  text-decoration: none;
}

.nav-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  background: var(--bg-primary);
  color: var(--text-primary);
}

.back-button:hover:not(:disabled) {
  background: var(--bg-secondary);
  border-color: var(--primary-light);
}

.next-button {
  background: var(--accent-blue);
  border-color: var(--accent-blue);
  color: white;
}

.next-button:hover:not(:disabled) {
  background: var(--accent-blue-dark);
  border-color: var(--accent-blue-dark);
}

.nav-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.nav-button:active:not(:disabled) {
  transform: translateY(1px);
}

@media (max-width: 768px) {
  .wizard-navigation {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem 0;
  }

  .navigation-left,
  .navigation-right {
    flex-direction: column;
    width: 100%;
  }

  .nav-button {
    width: 100%;
    justify-content: center;
  }
}
</style>