<script setup>
import { inject, computed, onMounted, watch } from 'vue'
import Input from '@/components/fields/Input.vue'
import Select from '@/components/fields/Select.vue'
import CheckBox from '@/components/fields/CheckBox.vue'
import TextField from '@/components/fields/TextField.vue'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'

const wizardState = useWizardState()
const validation = useValidation()

const discountTypes = {
  percent: 'Percentage Discount',
  fixed_cart: 'Fixed Cart Discount',
  fixed_product: 'Fixed Product Discount'
}

// Get errors for specific fields
const discountTypeError = computed(function() {
  return validation.errors.value.discount_type || ''
})

const couponAmountError = computed(function() {
  return validation.errors.value.coupon_amount || ''
})

const couponAmountWarning = computed(function() {
  return validation.warnings.value.coupon_amount || ''
})

// Check warnings whenever form data changes
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
  console.log('BasicSettings warnings:', validation.warnings.value)
  console.log('Coupon amount warning:', couponAmountWarning.value)
})
</script>

<template>
  <div class="step-container">
    <h2>Basic Settings</h2>
    <p class="step-description">Configure the basic discount settings for your coupons.</p>

    <form @submit.prevent="" class="coupon-form">
      <Select
        :options="discountTypes"
        name="discount_type"
        label="Discount Type"
        subtitle="The type of discount to apply (percentage, fixed cart, or fixed product)"
        :value="wizardState.formData.discount_type"
        :error="discountTypeError"
        @input="handleFieldChange('discount_type', $event)"
      />

      <Input
        type="tel"
        name="coupon_amount"
        label="Coupon Amount"
        subtitle="The value of the discount (e.g., 10 for 10% or $10)"
        :value="wizardState.formData.coupon_amount"
        :error="couponAmountError"
        :warning="couponAmountWarning"
        @input="handleFieldChange('coupon_amount', $event)"
      />

      <CheckBox
        name="free_shipping"
        label="Free Shipping"
        subtitle="Check this box if the coupon grants free shipping"
        :value="wizardState.formData.free_shipping"
        @input="handleFieldChange('free_shipping', $event)"
      />

      <TextField
        :height="150"
        placeholder="Enter a description for these coupons (optional)..."
        label="Description"
        subtitle="This description will be applied to all generated coupons"
        name="description"
        :value="wizardState.formData.description"
        @input="handleFieldChange('description', $event)"
      />
    </form>
  </div>
</template>

<style scoped>
.step-container {
  max-width: 900px;
  margin: 0 auto;
}

h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--text-primary);
}

.step-description {
  color: var(--text-secondary);
  margin-bottom: 2rem;
  font-size: 0.9375rem;
  line-height: 1.5;
}

.coupon-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
</style>