<script setup>
import { inject, computed, ref } from 'vue'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'

const wizardState = useWizardState()
const validation = useValidation()
const isGenerating = ref(false)
const generationProgress = ref({ current: 0, total: 0, percentage: 0, message: '' })

const settingsSummary = computed(function() {
  return [
    {
      category: 'Basic Settings',
      fields: [
        { label: 'Discount Type', value: wizardState.formData.value.discount_type },
        { label: 'Coupon Amount', value: wizardState.formData.value.coupon_amount },
        { label: 'Free Shipping', value: wizardState.formData.value.free_shipping ? 'Yes' : 'No' },
        { label: 'Description', value: wizardState.formData.value.description || 'None' }
      ]
    },
    {
      category: 'Date & Shipping',
      fields: [
        { label: 'Expiry Date', value: wizardState.formData.value.expiry_date || 'No expiry' }
      ]
    },
    {
      category: 'Generator Options',
      fields: [
        { label: 'Prefix', value: wizardState.formData.value.prefix },
        { label: 'Character Count', value: wizardState.formData.value.character_count },
        { label: 'Quantity', value: wizardState.formData.value.quantity }
      ]
    }
  ]
})

function generateCoupons() {
  isGenerating.value = true

  const data = new FormData()
  data.append('action', 'coupolic_generate_coupons')
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
      alert('Coupons generated successfully! Batch ID: ' + result.data.batch_id)
      // Navigate to success page or show results
    } else {
      alert('Generation failed: ' + result.data.message)
    }
  })
  .catch(function(error) {
    console.error('Generation error:', error)
    alert('Failed to generate coupons')
  })
  .finally(function() {
    isGenerating.value = false
  })
}
</script>

<template>
  <div class="step-container">
    <h2>Review & Generate</h2>
    <p class="step-description">Review your settings and generate your coupons.</p>

    <div class="summary-section" v-for="section in settingsSummary" :key="section.category">
      <h3>{{ section.category }}</h3>
      <div class="summary-grid">
        <div v-for="field in section.fields" :key="field.label" class="summary-item">
          <span class="summary-label">{{ field.label }}:</span>
          <span class="summary-value">{{ field.value }}</span>
        </div>
      </div>
    </div>

    <div class="validation-warnings" v-if="validation.hasWarnings.value">
      <h4>⚠️ Warnings</h4>
      <ul>
        <li v-for="(warning, key) in validation.warnings.value" :key="key">
          {{ warning }}
        </li>
      </ul>
    </div>

    <div class="generate-section">
      <div class="generate-info">
        <h4>Ready to Generate</h4>
        <p>You're about to generate <strong>{{ wizardState.formData.value.quantity }}</strong> coupons with the settings above.</p>
        <p>This action will create actual WooCommerce coupons that can be used immediately.</p>
      </div>

      <button
        @click="generateCoupons"
        class="generate-button"
        :disabled="isGenerating"
        type="button"
      >
        <span v-if="!isGenerating">🚀 Generate {{ wizardState.formData.value.quantity }} Coupons</span>
        <span v-else>⏳ Generating...</span>
      </button>
    </div>

    <div v-if="isGenerating" class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: generationProgress.percentage + '%' }"></div>
      </div>
      <p class="progress-text">{{ generationProgress.message }}</p>
      <p class="progress-stats">{{ generationProgress.current }} / {{ generationProgress.total }} coupons</p>
    </div>
  </div>
</template>

<style scoped>
.step-container {
  max-width: 900px;
  margin: 0 auto;
}

h2 {
  font-size: 24px;
  margin-bottom: 8px;
  color: #333;
}

.step-description {
  color: #666;
  margin-bottom: 32px;
  font-size: 14px;
}

.summary-section {
  margin-bottom: 32px;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
}

.summary-section h3 {
  font-size: 18px;
  margin-bottom: 16px;
  color: #444;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 8px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.summary-label {
  font-size: 12px;
  color: #666;
  font-weight: 500;
}

.summary-value {
  font-size: 14px;
  color: #333;
  font-weight: 600;
}

.validation-warnings {
  padding: 16px;
  background: #fff3e0;
  border-radius: 8px;
  border-left: 4px solid #ff9800;
  margin-bottom: 24px;
}

.validation-warnings h4 {
  margin: 0 0 8px 0;
  color: #e65100;
  font-size: 14px;
}

.validation-warnings ul {
  margin: 0;
  padding-left: 20px;
}

.validation-warnings li {
  margin: 4px 0;
  font-size: 14px;
  color: #bf360c;
}

.generate-section {
  padding: 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  color: white;
  margin-bottom: 24px;
}

.generate-info h4 {
  margin: 0 0 12px 0;
  font-size: 18px;
}

.generate-info p {
  margin: 8px 0;
  font-size: 14px;
  opacity: 0.9;
}

.generate-button {
  width: 100%;
  padding: 16px;
  background: white;
  color: #667eea;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 20px;
}

.generate-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.generate-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.progress-container {
  padding: 20px;
  background: #f5f5f5;
  border-radius: 8px;
  margin-top: 20px;
}

.progress-bar {
  height: 8px;
  background: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 12px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4CAF50, #45a049);
  transition: width 0.3s ease;
}

.progress-text {
  margin: 8px 0;
  font-size: 14px;
  color: #666;
  text-align: center;
}

.progress-stats {
  margin: 4px 0 0 0;
  font-size: 12px;
  color: #888;
  text-align: center;
}
</style>