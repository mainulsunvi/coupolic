<script setup>
import { inject, computed, onMounted, watch } from 'vue'
import DateTime from '@/components/fields/DateTime.vue'
import CheckBox from '@/components/fields/CheckBox.vue'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'

const wizardState = useWizardState()
const validation = useValidation()

// Get error and warning for expiry date
const expiryDateError = computed(function() {
  return validation.errors.value.expiry_date || ''
})

const expiryDateWarning = computed(function() {
  return validation.warnings.value?.expiry_date || ''
})

function checkAllWarnings() {
  validation.checkWarnings(wizardState.formData)
}

// Watch for changes in form data and check warnings
watch(function() {
  return wizardState.formData
}, function() {
  checkAllWarnings()
}, { deep: true })

function handleFieldChange(field, value) {
  wizardState.updateFormData(field, value)
  validation.validateField(field, value, wizardState.formData)
  checkAllWarnings()
}

// Check warnings on component mount
onMounted(function() {
  checkAllWarnings()
  // Debug logging
  console.log('DateShipping warnings:', validation.warnings.value)
  console.log('Expiry date warning:', expiryDateWarning.value)
})
</script>

<template>
  <div class="step-container">
    <h2>Date & Shipping</h2>
    <p class="step-description">Set expiration dates and free shipping options for your coupons.</p>

    <form @submit.prevent="" class="coupon-form">
      <DateTime
        name="expiry_date"
        label="Expiry Date"
        subtitle="The coupons will expire at 00:00:00 of this date (leave empty for no expiry)"
        :value="wizardState.formData.expiry_date"
        :error="expiryDateError"
        :warning="expiryDateWarning"
        @input="handleFieldChange('expiry_date', $event)"
      />

      <CheckBox
        name="free_shipping"
        label="Allow Free Shipping"
        subtitle="Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone."
        :value="wizardState.formData.free_shipping"
        @input="handleFieldChange('free_shipping', $event)"
      />
    </form>
  </div>
</template>

<style scoped>
.step-container {
  max-width: 800px;
  margin: 0 auto;
}

h2 {
  font-size: 24px;
  margin-bottom: 8px;
  color: #333;
}

.step-description {
  color: #666;
  margin-bottom: 24px;
  font-size: 14px;
}

.coupon-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.milestone-save {
  margin-top: 20px;
  padding: 12px;
  background: #e3f2fd;
  border-radius: 6px;
  border-left: 4px solid #2196F3;
}

.milestone-save p {
  margin: 0;
  font-size: 14px;
  color: #1565c0;
}
</style>