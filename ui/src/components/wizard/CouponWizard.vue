<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'
import WizardTabs from './WizardTabs.vue'
import WizardNavigation from './WizardNavigation.vue'
import BasicSettings from './steps/BasicSettings.vue'
import DateShipping from './steps/DateShipping.vue'
import UsageRestrictions from './steps/UsageRestrictions.vue'
import SpendingRules from './steps/SpendingRules.vue'
import GeneratorOptions from './steps/GeneratorOptions.vue'
import ReviewGenerate from './steps/ReviewGenerate.vue'

// Initialize wizard state
const wizardState = useWizardState()
const validation = useValidation()
const router = useRouter()

// Load user limits on mount
onMounted(function() {
  loadUserLimits()
  loadExistingDraft()

  // Warn before leaving with unsaved changes
  window.addEventListener('beforeunload', handleBeforeUnload)
})

onUnmounted(function() {
  window.removeEventListener('beforeunload', handleBeforeUnload)
})

function handleBeforeUnload(event) {
  if (wizardState.hasUnsavedChanges.value) {
    event.preventDefault()
    event.returnValue = ''
  }
}

function loadUserLimits() {
  // Make AJAX request to get user limits
  const data = new FormData()
  data.append('action', 'coupolic_check_user_limits')
  data.append('nonce', coupolic.nonce)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      wizardState.setUserLimits(result.data.limits)
    }
  })
  .catch(function(error) {
    console.error('Failed to load user limits:', error)
  })
}

function loadExistingDraft() {
  // Check for saved draft in localStorage
  const savedDraft = localStorage.getItem('coupolic_draft')
  if (savedDraft) {
    try {
      const draftData = JSON.parse(savedDraft)
      wizardState.updateMultipleFields(draftData)
      wizardState.setDraftId(draftData.id)
    } catch (error) {
      console.error('Failed to parse saved draft:', error)
    }
  }
}

function saveDraft() {
  const data = new FormData()
  data.append('action', 'coupolic_save_draft')
  data.append('nonce', coupolic.nonce)

  Object.keys(wizardState.formData.value).forEach(function(key) {
    const value = wizardState.formData.value[key]
    if (Array.isArray(value)) {
      data.append(key, JSON.stringify(value))
    } else {
      data.append(key, value)
    }
  })

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      wizardState.setDraftId(result.data.draft_id)
      wizardState.clearUnsavedChanges()
      alert('Draft saved successfully!')
    } else {
      alert('Failed to save draft: ' + result.data.message)
    }
  })
  .catch(function(error) {
    console.error('Failed to save draft:', error)
    alert('Failed to save draft')
  })
}

function handleNextClick() {
  // Validate current step
  const currentStep = wizardState.currentStep.value
  const isValid = validation.validateStep(currentStep, wizardState.formData.value)

  if (!isValid) {
    alert('Please fix the errors before proceeding')
    return
  }

  // Check for milestone saves
  if (currentStep === 2 || currentStep === 4) {
    saveDraft()
  }

  wizardState.goToNextStep()
}

function handleBackClick() {
  wizardState.goToPreviousStep()
}

function handleStepClick(stepNumber) {
  // Only allow going back or to next step, not jumping ahead
  if (stepNumber <= wizardState.currentStep.value) {
    wizardState.setCurrentStep(stepNumber)
  } else {
    // Validate all steps up to the target step
    let canProceed = true
    for (let i = wizardState.currentStep.value; i < stepNumber; i++) {
      if (!validation.validateStep(i, wizardState.formData.value)) {
        canProceed = false
        break
      }
    }

    if (canProceed) {
      wizardState.setCurrentStep(stepNumber)
    } else {
      alert('Please complete and validate current step first')
    }
  }
}

function getCurrentStepComponent() {
  const stepComponents = {
    1: BasicSettings,
    2: DateShipping,
    3: UsageRestrictions,
    4: SpendingRules,
    5: GeneratorOptions,
    6: ReviewGenerate,
  }
  return stepComponents[wizardState.currentStep.value] || BasicSettings
}

function saveToLocalStorage() {
  localStorage.setItem('coupolic_draft', JSON.stringify({
    id: wizardState.draftId.value,
    ...wizardState.formData.value
  }))
}
</script>

<template>
  <div class="coupolic-wizard">
    <WizardTabs
      :current-step="wizardState.currentStep.value"
      :progress-percentage="wizardState.progressPercentage.value"
      @step-click="handleStepClick"
    />

    <div class="wizard-content">
      <component :is="getCurrentStepComponent()" />
    </div>

    <WizardNavigation
      :can-go-back="wizardState.canGoBack.value"
      :can-go-next="wizardState.canGoNext.value"
      :is-last-step="wizardState.isLastStep.value"
      :current-step="wizardState.currentStep.value"
      :has-unsaved-changes="wizardState.hasUnsavedChanges.value"
      @next="handleNextClick"
      @back="handleBackClick"
      @save-draft="saveDraft"
      @save-local="saveToLocalStorage"
    />
  </div>
</template>

<style scoped>
.coupolic-wizard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.wizard-content {
  min-height: 400px;
  margin: 20px 0;
}
</style>