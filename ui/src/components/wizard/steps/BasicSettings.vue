<script setup>
import { inject } from 'vue'
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

function handleFieldChange(field, value) {
  wizardState.updateFormData(field, value)
  validation.validateField(field, value, wizardState.formData.value)
}
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
        :value="wizardState.formData.value.discount_type"
        @input="handleFieldChange('discount_type', $event)"
      />

      <Input
        type="tel"
        name="coupon_amount"
        label="Coupon Amount"
        subtitle="The value of the discount (e.g., 10 for 10% or $10)"
        :value="wizardState.formData.value.coupon_amount"
        @input="handleFieldChange('coupon_amount', $event)"
      />

      <CheckBox
        name="free_shipping"
        label="Free Shipping"
        subtitle="Check this box if the coupon grants free shipping"
        :value="wizardState.formData.value.free_shipping"
        @input="handleFieldChange('free_shipping', $event)"
      />

      <TextField
        :height="150"
        placeholder="Enter a description for these coupons (optional)..."
        label="Description"
        subtitle="This description will be applied to all generated coupons"
        name="description"
        :value="wizardState.formData.value.description"
        @input="handleFieldChange('description', $event)"
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
</style>