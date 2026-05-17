<script setup>
const props = defineProps({
  currentStep: {
    type: Number,
    required: true,
  },
  progressPercentage: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['step-click'])

const steps = [
  { number: 1, name: 'Basic Settings' },
  { number: 2, name: 'Date & Shipping' },
  { number: 3, name: 'Usage Restrictions' },
  { number: 4, name: 'Spending Rules' },
  { number: 5, name: 'Generator Options' },
  { number: 6, name: 'Review & Generate' },
]

function getStepClass(stepNumber) {
  if (stepNumber === props.currentStep) {
    return 'step-active'
  } else if (stepNumber < props.currentStep) {
    return 'step-completed'
  } else {
    return 'step-pending'
  }
}

function handleStepClick(stepNumber) {
  emit('step-click', stepNumber)
}
</script>

<template>
  <div class="wizard-tabs">
    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
    </div>

    <div class="tabs-container">
      <div
        v-for="step in steps"
        :key="step.number"
        :class="['tab-item', getStepClass(step.number)]"
        @click="handleStepClick(step.number)"
      >
        <div class="tab-icon">
          <span v-if="step.number < currentStep">✓</span>
          <span v-else>{{ step.number }}</span>
        </div>
        <div class="tab-content">
          <div class="step-number">Step {{ step.number }}</div>
          <div class="step-name">{{ step.name }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.wizard-tabs {
  margin-bottom: 2rem;
}

.progress-bar {
  height: 4px;
  background: var(--bg-tertiary);
  margin-bottom: 2rem;
  overflow: hidden;
  display: none;
}

.progress-fill {
  height: 100%;
  background: var(--accent-blue);
  transition: width 0.3s ease;
}

.tabs-container {
  display: flex;
  align-items: flex-start;
  gap: 0;
}

.tab-item {
  flex: 1;
  text-align: center;
  position: relative;
  padding: 0 1rem;
  cursor: pointer;
}

.tab-item:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 16px;
  right: -50%;
  width: 100%;
  height: 2px;
  background: var(--border-light);
  z-index: 0;
}

.tab-item.step-completed:not(:last-child)::after {
  background: var(--success);
}

.tab-item.step-active:not(:last-child)::after {
  background: linear-gradient(to right, var(--success) 50%, var(--border-light) 50%);
}

.tab-icon {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--bg-primary);
  border: 2px solid var(--border-medium);
  margin: 0 auto 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-secondary);
  transition: all var(--transition-base);
}

.tab-item.step-active .tab-icon {
  background: var(--accent-blue);
  border-color: var(--accent-blue);
  color: white;
  font-weight: 600;
}

.tab-item.step-completed .tab-icon {
  background: var(--success);
  border-color: var(--success);
  color: white;
}

.tab-content {
  position: relative;
  z-index: 1;
}

.step-number {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-bottom: 0.25rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tab-item.step-active .step-number {
  color: var(--accent-blue);
}

.tab-item.step-completed .step-number {
  color: var(--success);
}

.step-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-secondary);
  line-height: 1.3;
}

.tab-item.step-active .step-name {
  color: var(--text-primary);
  font-weight: 600;
}

.tab-item.step-completed .step-name {
  color: var(--text-primary);
}

.tab-item.step-pending {
  opacity: 0.5;
  cursor: default;
}

@media (max-width: 768px) {
  .tabs-container {
    flex-wrap: wrap;
    gap: 1rem;
  }

  .tab-item {
    flex: 0 0 calc(33.333% - 0.667rem);
    min-width: 0;
  }

  .tab-item:not(:last-child)::after {
    display: none;
  }
}
</style>