<script setup>
import { inject } from 'vue'
import Input from '@/components/fields/Input.vue'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'
import { computed } from 'vue'

const wizardState = useWizardState()
const validation = useValidation()

const userLimitText = computed(function() {
  const limits = wizardState.userLimits
  if (limits.daily_limit === -1) {
    return 'Unlimited coupons per day'
  } else {
    return `${limits.remaining_daily} coupons remaining today (${limits.daily_limit} daily limit)`
  }
})

function handleFieldChange(field, value) {
  wizardState.updateFormData(field, value)
  validation.validateField(field, value, wizardState.formData)
}
</script>

<template>
  <div class="step-container">
    <h2>Generator Options</h2>
    <p class="step-description">Configure how many coupons to generate and their format.</p>

    <div class="user-limits" v-if="wizardState.userLimits && wizardState.userLimits.daily_limit !== undefined">
      <h4>📊 Your Limits</h4>
      <p>{{ userLimitText }}</p>
      <p>Maximum per batch: {{ wizardState.userLimits.batch_limit === -1 ? 'Unlimited' : wizardState.userLimits.batch_limit }}</p>
    </div>

    <form @submit.prevent="" class="coupon-form">
      <Input
        type="text"
        name="prefix"
        label="Coupon Prefix"
        subtitle="Prefix for generated coupon codes (e.g., SUMMER_2025_)"
        placeholder="COUPON"
        :value="wizardState.formData.prefix"
        @input="handleFieldChange('prefix', $event)"
      />

      <Input
        type="number"
        name="character_count"
        label="Number of Characters"
        subtitle="Length of the random part of the coupon code (5-20 characters)"
        :value="wizardState.formData.character_count"
        @input="handleFieldChange('character_count', $event)"
        min="5"
        max="20"
      />

      <Input
        type="number"
        name="quantity"
        label="Number of Coupons"
        subtitle="How many coupons to generate in this batch"
        :value="wizardState.formData.quantity"
        @input="handleFieldChange('quantity', $event)"
        min="1"
        :max="wizardState.userLimits.batch_limit || 100"
      />

      <div class="preview-box">
        <h4>Preview</h4>
        <p>Example coupon code: <code>{{ wizardState.formData.prefix }}_XXXXX</code></p>
        <p>Will generate: <strong>{{ wizardState.formData.quantity }}</strong> coupons</p>
      </div>
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

.user-limits {
  padding: 16px;
  background: #fff3e0;
  border-radius: 8px;
  border-left: 4px solid #ff9800;
  margin-bottom: 24px;
}

.user-limits h4 {
  margin: 0 0 8px 0;
  color: #e65100;
  font-size: 14px;
}

.user-limits p {
  margin: 4px 0;
  font-size: 13px;
  color: #bf360c;
}

.coupon-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.preview-box {
  padding: 16px;
  background: #f5f5f5;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.preview-box h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #666;
}

.preview-box p {
  margin: 8px 0;
  font-size: 14px;
  color: #333;
}

.preview-box code {
  background: #fff;
  padding: 2px 6px;
  border-radius: 3px;
  font-family: monospace;
  border: 1px solid #ccc;
}
</style>