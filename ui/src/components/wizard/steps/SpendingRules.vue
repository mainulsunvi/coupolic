<script setup>
import { inject } from 'vue'
import Input from '@/components/fields/Input.vue'
import CheckBox from '@/components/fields/CheckBox.vue'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'

const wizardState = useWizardState()
const validation = useValidation()

function handleFieldChange(field, value) {
  wizardState.updateFormData(field, value)
  validation.validateField(field, value, wizardState.formData)
}
</script>

<template>
  <div class="step-container">
    <h2>Spending Rules</h2>
    <p class="step-description">Set minimum and maximum spending amounts for coupon usage.</p>

    <form @submit.prevent="" class="coupon-form">
      <Input
        type="tel"
        name="minimum_amount"
        label="Minimum Spend"
        subtitle="Minimum order subtotal required to use the coupon (leave empty for no minimum)"
        placeholder="No minimum"
        :value="wizardState.formData.minimum_amount"
        @input="handleFieldChange('minimum_amount', $event)"
      />

      <Input
        type="tel"
        name="maximum_amount"
        label="Maximum Spend"
        subtitle="Maximum order subtotal allowed to use the coupon (leave empty for no maximum)"
        placeholder="No maximum"
        :value="wizardState.formData.maximum_amount"
        @input="handleFieldChange('maximum_amount', $event)"
      />

      <CheckBox
        name="individual_use"
        label="Individual Use Only"
        subtitle="Check this box if the coupon cannot be used in conjunction with other coupons"
        :value="wizardState.formData.individual_use"
        @input="handleFieldChange('individual_use', $event)"
      />

      <CheckBox
        name="exclude_sale_items"
        label="Exclude Sale Items"
        subtitle="Check this box if the coupon should not apply to items that are already on sale"
        :value="wizardState.formData.exclude_sale_items"
        @input="handleFieldChange('exclude_sale_items', $event)"
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