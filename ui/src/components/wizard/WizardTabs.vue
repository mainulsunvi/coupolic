<script setup>
import { Icon } from '@iconify/vue'

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
  { number: 1, name: 'Basic Settings', icon: 'proicons:settings' },
  { number: 2, name: 'Date & Shipping', icon: 'proicons:calendar' },
  { number: 3, name: 'Usage Restrictions', icon: 'proicons:alert-rhombus' },
  { number: 4, name: 'Spending Rules', icon: 'proicons:wallet' },
  { number: 5, name: 'Generator Options', icon: 'proicons:ticket' },
  { number: 6, name: 'Review & Generate', icon: 'proicons:check-circle' },
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
          <Icon :icon="step.icon" width="20" height="20" />
        </div>
        <div class="tab-content">
          <div class="step-number">Step {{ step.number }}</div>
          <div class="step-name">{{ step.name }}</div>
        </div>
        <div class="tab-status">
          <span v-if="step.number < currentStep" class="completed-badge">✓</span>
          <span v-else-if="step.number === currentStep" class="current-badge">Active</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.wizard-tabs {
  margin-bottom: 30px;
}

.progress-bar {
  height: 4px;
  background-color: #e0e0e0;
  border-radius: 2px;
  margin-bottom: 20px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4CAF50, #45a049);
  transition: width 0.3s ease;
}

.tabs-container {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.tab-item {
  flex: 1;
  min-width: 150px;
  padding: 15px;
  background: #f5f5f5;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 2px solid transparent;
}

.tab-item:hover {
  background: #eeeeee;
}

.tab-item.step-active {
  background: #e8f5e9;
  border-color: #4CAF50;
}

.tab-item.step-completed {
  background: #f1f8f4;
  border-color: #45a049;
}

.tab-item.step-pending {
  opacity: 0.7;
}

.tab-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: white;
}

.step-active .tab-icon {
  background: #4CAF50;
  color: white;
}

.tab-content {
  flex: 1;
}

.step-number {
  font-size: 12px;
  color: #666;
  margin-bottom: 2px;
}

.step-name {
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.tab-status {
  display: flex;
  align-items: center;
}

.completed-badge {
  color: #4CAF50;
  font-weight: bold;
  font-size: 18px;
}

.current-badge {
  background: #4CAF50;
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

@media (max-width: 768px) {
  .tabs-container {
    flex-direction: column;
  }

  .tab-item {
    min-width: 100%;
  }
}
</style>