<script setup>
import { inject, computed, ref, onUnmounted, watch, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useWizardState } from '@/composables/useWizardState.js'
import { useValidation } from '@/composables/useValidation.js'

const wizardState = useWizardState()
const validation = useValidation()
const isGenerating = ref(false)
const generationProgress = ref({ current: 0, total: 0, percentage: 0, message: '' })
const generationResult = ref(null)
const showResult = ref(false)
const progressCheckInterval = ref(null)

// Watch for form data changes to update warnings
watch(function() {
  return wizardState.formData
}, function() {
  validation.checkWarnings(wizardState.formData)
}, { deep: true })

// Check for warnings when component loads
onMounted(function() {
  validation.checkWarnings(wizardState.formData)
})

// Store coupons as they're generated for real-time preview
const generatedCoupons = ref([])

const settingsSummary = computed(function() {
  console.log('Computing settings summary, formData:', wizardState.formData)
  console.log('Validation warnings:', validation.warnings.value)
  console.log('Has warnings:', validation.hasWarnings.value)

  return [
    {
      category: 'Basic Settings',
      fields: [
        { label: 'Discount Type', value: wizardState.formData.discount_type },
        { label: 'Coupon Amount', value: wizardState.formData.coupon_amount },
        { label: 'Free Shipping', value: wizardState.formData.free_shipping ? 'Yes' : 'No' },
        { label: 'Description', value: wizardState.formData.description || 'None' }
      ]
    },
    {
      category: 'Date & Shipping',
      fields: [
        { label: 'Expiry Date', value: wizardState.formData.expiry_date || 'No expiry' }
      ]
    },
    {
      category: 'Generator Options',
      fields: [
        { label: 'Prefix', value: wizardState.formData.prefix },
        { label: 'Character Count', value: wizardState.formData.character_count },
        { label: 'Quantity', value: wizardState.formData.quantity }
      ]
    }
  ]
})

function generateCoupons() {
  isGenerating.value = true
  generationResult.value = null
  showResult.value = false
  generatedCoupons.value = []

  const data = new FormData()
  data.append('action', 'coupolic_generate_coupons')
  data.append('nonce', coupolic.nonce)

  Object.keys(wizardState.formData).forEach(function(key) {
    const value = wizardState.formData[key]
    if (Array.isArray(value)) {
      // Convert arrays to comma-separated strings for better backend compatibility
      const stringValue = value.join(',')
      data.append(key, stringValue)
      console.log('Sending array field:', key, '=>', stringValue)
    } else {
      data.append(key, value)
    }
  })

  // Debug logging
  console.log('Form data being sent:', Object.fromEntries(data))
  console.log('Product restrictions:', wizardState.formData.product_ids)
  console.log('Category restrictions:', wizardState.formData.product_categories)

  // Start progress simulation immediately while request is processing
  startProgressSimulation()

  // Start generation request
  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      // Stop simulation and show final results
      stopProgressSimulation()
      generationResult.value = result.data
      showResult.value = true

      console.log('Generation result:', result.data)

      // Store the coupons list properly
      if (result.data.coupons && Array.isArray(result.data.coupons)) {
        generationResult.value.couponsList = result.data.coupons.map(function(coupon) {
          return {
            code: coupon.code,
            coupon_code: coupon.code,
            id: coupon.id,
            link: coupon.link,
            discount_type: wizardState.formData.discount_type,
            amount: wizardState.formData.coupon_amount,
            coupon_amount: wizardState.formData.coupon_amount,
            expiry_date: wizardState.formData.expiry_date || 'Never',
            usage_limit: wizardState.formData.usage_limit || 'Unlimited',
            description: wizardState.formData.description || ''
          }
        })
        console.log('Final coupons list:', generationResult.value.couponsList.length, 'items')
      }

      // Set progress to 100%
      generationProgress.value = {
        current: result.data.count || wizardState.formData.quantity,
        total: wizardState.formData.quantity,
        percentage: 100,
        message: 'Generation completed successfully!'
      }

      isGenerating.value = false
    } else {
      console.error('Generation failed:', result.data.message)
      stopProgressSimulation()
      isGenerating.value = false
    }
  })
  .catch(function(error) {
    console.error('Generation error:', error)
    stopProgressSimulation()
    isGenerating.value = false
  })
}

// Simulated progress during the request
let simulationInterval = null

function startProgressSimulation() {
  const totalCount = wizardState.formData.quantity || 10
  let currentCount = 0

  simulationInterval = setInterval(function() {
    if (currentCount < totalCount) {
      currentCount++
      const percentage = Math.round((currentCount / totalCount) * 80) // Only go up to 80% during simulation

      generationProgress.value = {
        current: currentCount,
        total: totalCount,
        percentage: percentage,
        message: 'Generating coupon ' + currentCount + ' of ' + totalCount + '...'
      }

      // Add simulated coupon previews
      const prefix = wizardState.formData.prefix || 'COUPON'
      const charCount = wizardState.formData.character_count || 5
      const randomStr = Math.random().toString(36).substring(2, 2 + charCount).toUpperCase()

      generatedCoupons.value.push({
        code: prefix + randomStr,
        discount_type: wizardState.formData.discount_type,
        amount: wizardState.formData.coupon_amount,
        coupon_amount: wizardState.formData.coupon_amount,
        expiry_date: wizardState.formData.expiry_date || 'Never',
        usage_limit: wizardState.formData.usage_limit || 'Unlimited',
        description: wizardState.formData.description || ''
      })
    }
  }, 300) // Add a new coupon every 300ms
}

function stopProgressSimulation() {
  if (simulationInterval) {
    clearInterval(simulationInterval)
    simulationInterval = null
  }
}

function startProgressTracking() {
  // Start checking progress immediately, we'll get the batch_id from the response
  // Check progress every 500ms for real-time updates
  progressCheckInterval.value = setInterval(function() {
    checkProgress()
  }, 500)
}

function stopProgressTracking() {
  if (progressCheckInterval.value) {
    clearInterval(progressCheckInterval.value)
    progressCheckInterval.value = null
  }

  // Also stop simulation if it's running
  stopProgressSimulation()

  // Clear the progress option from database to prevent stale data
  if (generationResult.value && generationResult.value.batch_id) {
    const data = new FormData()
    data.append('action', 'coupolic_cleanup_progress')
    data.append('nonce', coupolic.nonce)
    data.append('batch_id', generationResult.value.batch_id)

    fetch(coupolic.ajax_url, {
      method: 'POST',
      body: data
    }).catch(function(error) {
      console.log('Progress cleanup failed:', error)
    })
  }
}

function checkProgress() {
  // Only check progress if we have a batch_id from the generation result
  if (!generationResult.value || !generationResult.value.batch_id) {
    // If we don't have a batch_id yet, show initializing progress
    const totalCount = wizardState.formData.quantity || 10
    generationProgress.value = {
      current: 0,
      total: totalCount,
      percentage: 0,
      message: 'Initializing generation...'
    }
    return
  }

  // Make AJAX request to check progress and get results
  const data = new FormData()
  data.append('action', 'coupolic_get_generation_results')
  data.append('nonce', coupolic.nonce)
  data.append('batch_id', generationResult.value.batch_id)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success && result.data.progress) {
      const progress = result.data.progress

      // Update progress with real data from backend
      generationProgress.value = {
        current: progress.current || 0,
        total: progress.total || wizardState.formData.quantity || 10,
        percentage: Math.round(progress.percentage || 0),
        message: progress.message || 'Generating coupons...'
      }

      // Update with real generated coupons from backend if available
      if (progress.generated_coupons && Array.isArray(progress.generated_coupons)) {
        updateRealCouponPreviews(progress.generated_coupons)
      } else {
        // Fallback to simulated previews if backend doesn't send real data
        updateCouponPreviews(progress.current)
      }

      // Check if generation is completed
      if (result.data.status === 'completed' && result.data.coupons) {
        // Generation completed - show final results
        showFinalResults(result.data)
        stopProgressTracking()
      } else if (result.data.status === 'failed') {
        // Generation failed
        console.error('Generation failed:', progress.message)
        stopProgressTracking()
        isGenerating.value = false
      }
    }
  })
  .catch(function(error) {
    console.error('Progress check error:', error)
  })
}

function showFinalResults(data) {
  showResult.value = true
  isGenerating.value = false

  // Store the final coupons
  if (data.coupons && Array.isArray(data.coupons)) {
    generationResult.value.couponsList = data.coupons.map(function(coupon) {
      return {
        code: coupon.code,
        coupon_code: coupon.code,
        id: coupon.id,
        link: coupon.link,
        discount_type: wizardState.formData.discount_type,
        amount: wizardState.formData.coupon_amount,
        coupon_amount: wizardState.formData.coupon_amount,
        expiry_date: wizardState.formData.expiry_date || 'Never',
        usage_limit: wizardState.formData.usage_limit || 'Unlimited',
        description: wizardState.formData.description || ''
      }
    })
  }

  // Update generation result with final data
  generationResult.value.total_count = data.count || data.coupons.length
  generationResult.value.generated_at = data.generated_at

  console.log('Generation completed:', generationResult.value.couponsList.length, 'coupons')
}

function updateRealCouponPreviews(backendCoupons) {
  // Use real coupon data from backend
  generatedCoupons.value = backendCoupons.map(function(coupon) {
    return {
      code: coupon.code,
      id: coupon.id,
      link: coupon.link,
      discount_type: wizardState.formData.discount_type,
      amount: wizardState.formData.coupon_amount,
      coupon_amount: wizardState.formData.coupon_amount,
      expiry_date: wizardState.formData.expiry_date || 'Never',
      usage_limit: wizardState.formData.usage_limit || 'Unlimited',
      description: wizardState.formData.description || ''
    }
  })
}

function updateCouponPreviews(currentCount) {
  const targetCount = currentCount
  const currentCouponCount = generatedCoupons.value.length

  // Add more coupon previews to match the current progress
  if (targetCount > currentCouponCount) {
    const couponsToAdd = targetCount - currentCouponCount
    const prefix = wizardState.formData.prefix || 'COUPON'
    const charCount = wizardState.formData.character_count || 5

    for (let i = 0; i < couponsToAdd; i++) {
      const randomStr = Math.random().toString(36).substring(2, 2 + charCount).toUpperCase()
      generatedCoupons.value.push({
        code: prefix + randomStr,
        discount_type: wizardState.formData.discount_type,
        amount: wizardState.formData.coupon_amount,
        coupon_amount: wizardState.formData.coupon_amount,
        expiry_date: wizardState.formData.expiry_date || 'Never',
        usage_limit: wizardState.formData.usage_limit || 'Unlimited',
        description: wizardState.formData.description || ''
      })
    }
  }
}

function downloadCoupons() {
  console.log('Download clicked, generationResult:', generationResult.value)

  if (!generationResult.value) {
    console.error('No generation result available')
    return
  }

  if (!generationResult.value.couponsList || generationResult.value.couponsList.length === 0) {
    console.error('No coupon data available for download')
    return
  }

  // Create CSV content
  const coupons = generationResult.value.couponsList
  const headers = ['Code', 'Discount Type', 'Amount', 'Expiry Date', 'Usage Limit', 'Description', 'Edit Link']
  const csvContent = [
    headers.join(','),
    ...coupons.map(function(coupon) {
      return [
        coupon.code || coupon.coupon_code || 'N/A',
        coupon.discount_type || 'N/A',
        coupon.amount || coupon.coupon_amount || 'N/A',
        coupon.expiry_date || 'Never',
        coupon.usage_limit || 'Unlimited',
        coupon.description || '',
        coupon.link || ''
      ].map(function(field) {
        return '"' + (field || '').toString().replace(/"/g, '""') + '"'
      }).join(',')
    })
  ].join('\n')

  console.log('CSV created with', coupons.length, 'rows')

  // Create download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)

  const filename = 'coupons_batch_' + (generationResult.value.batch_id || Date.now()) + '.csv'
  link.setAttribute('href', url)
  link.setAttribute('download', filename)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)

  console.log('Download initiated:', filename)
}

function resetGeneration() {
  showResult.value = false
  generationResult.value = null
  generatedCoupons.value = []
  wizardState.resetFormData()
  wizardState.setCurrentStep(1)
}

// Cleanup on component unmount
onUnmounted(function() {
  stopProgressTracking()
  stopProgressSimulation()
})
</script>

<template>
  <div class="step-container">
    <h2>Review & Generate</h2>
    <p class="step-description">Review your settings and generate your coupons.</p>

    <!-- Generation Results -->
    <div v-if="showResult && generationResult" class="result-section">
      <div class="success-banner">
        <div class="success-icon">✓</div>
        <div class="success-content">
          <h3>Coupons Generated Successfully!</h3>
          <p>Batch ID: {{ generationResult.batch_id }}</p>
        </div>
      </div>

      <div class="result-details">
        <div class="detail-row">
          <span class="detail-label">Total Coupons:</span>
          <span class="detail-value">{{ generationResult.total_count || wizardState.formData.quantity }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Batch ID:</span>
          <span class="detail-value">{{ generationResult.batch_id }}</span>
        </div>
        <div class="detail-row" v-if="generationResult.generated_at">
          <span class="detail-label">Generated At:</span>
          <span class="detail-value">{{ new Date(generationResult.generated_at * 1000).toLocaleString() }}</span>
        </div>
      </div>

      <div class="result-actions">
        <button @click="downloadCoupons" class="action-btn download-btn">
          <span>Download CSV</span>
        </button>
        <button @click="resetGeneration" class="action-btn reset-btn">
          <span>Create New Batch</span>
        </button>
        <router-link to="/logs" class="action-btn logs-btn">
          <span>View All Logs</span>
        </router-link>
      </div>

      <div v-if="generationResult.couponsList && generationResult.couponsList.length > 0" class="coupons-preview">
        <h4>Coupon Codes Preview</h4>
        <div class="coupons-grid-scroll">
          <div class="coupons-grid">
            <div v-for="coupon in generationResult.couponsList" :key="coupon.code" class="code-item">
              {{ coupon.code }}
            </div>
          </div>
        </div>
        <p class="preview-count">Total: {{ generationResult.couponsList.length }} coupons</p>
      </div>
    </div>

    <!-- Generation Form (show when no results) -->
    <div v-else>
      <div class="summary-section" v-for="section in settingsSummary" :key="section.category">
        <h3>{{ section.category }}</h3>
        <div class="summary-grid">
          <div v-for="field in section.fields" :key="field.label" class="summary-item">
            <span class="summary-label">{{ field.label }}:</span>
            <span class="summary-value">{{ field.value }}</span>
          </div>
        </div>
      </div>

      <div class="validation-warnings" v-if="validation.hasWarnings && Object.keys(validation.warnings.value).length > 0">
        <h4>Warnings</h4>
        <ul>
          <li v-for="(warningMessage, fieldKey) in validation.warnings.value" :key="fieldKey">
            {{ warningMessage }}
          </li>
        </ul>
      </div>

      <div class="generate-section">
        <div class="generate-info">
          <h4>Ready to Generate</h4>
          <p>You're about to generate <strong>{{ wizardState.formData.quantity }}</strong> coupons with the settings above.</p>
          <p>This action will create actual WooCommerce coupons that can be used immediately.</p>
        </div>

        <button
          @click="generateCoupons"
          class="generate-button"
          :disabled="isGenerating"
          type="button"
        >
          <span v-if="!isGenerating">Generate {{ wizardState.formData.quantity }} Coupons</span>
          <span v-else>Generating...</span>
        </button>
      </div>

      <div v-if="isGenerating" class="progress-container">
        <div class="progress-header">
          <h4>Generating Coupons</h4>
          <span class="progress-percentage">{{ generationProgress.percentage }}%</span>
        </div>

        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: generationProgress.percentage + '%' }"></div>
        </div>

        <div class="progress-info">
          <p class="progress-text">{{ generationProgress.message || 'Generating coupons...' }}</p>
          <p class="progress-stats">{{ generationProgress.current }} / {{ generationProgress.total }} coupons</p>
        </div>

        <!-- Real-time coupon preview during generation -->
        <div v-if="generatedCoupons.length > 0" class="realtime-preview">
          <h5>Live Preview ({{ generatedCoupons.length }} generated)</h5>
          <div class="coupons-grid-scroll">
            <div class="coupons-grid">
              <div v-for="coupon in generatedCoupons" :key="coupon.code" class="code-item generating">
                {{ coupon.code }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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

.summary-section {
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
}

.summary-section h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--text-primary);
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border-light);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.75rem;
  background: var(--bg-primary);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
}

.summary-label {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.summary-value {
  font-size: 0.9375rem;
  color: var(--text-primary);
  font-weight: 500;
}

.validation-warnings {
  padding: 1rem;
  background: var(--warning-bg);
  border-radius: var(--radius-md);
  border-left: 3px solid var(--warning);
  margin-bottom: 1.5rem;
}

.validation-warnings h4 {
  margin: 0 0 0.75rem 0;
  color: var(--warning);
  font-size: 0.9375rem;
  font-weight: 600;
}

.validation-warnings ul {
  margin: 0;
  padding-left: 1.25rem;
}

.validation-warnings li {
  margin: 0.375rem 0;
  font-size: 0.875rem;
  color: var(--warning);
  line-height: 1.4;
}

.generate-section {
  padding: 1.5rem;
  background: var(--accent-blue);
  border-radius: var(--radius-lg);
  color: white;
  margin-bottom: 1.5rem;
}

.generate-info h4 {
  margin: 0 0 0.75rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.generate-info p {
  margin: 0.5rem 0;
  font-size: 0.9375rem;
  line-height: 1.5;
  opacity: 0.95;
}

.generate-button {
  width: 100%;
  padding: 0.875rem;
  background: white;
  color: var(--accent-blue);
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
  margin-top: 1.5rem;
}

.generate-button:hover:not(:disabled) {
  opacity: 0.95;
}

.generate-button:active:not(:disabled) {
  transform: translateY(1px);
}

.generate-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.progress-container {
  padding: 1.5rem;
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  margin-top: 1.5rem;
  border: 1px solid var(--border-light);
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.progress-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
}

.progress-percentage {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--accent-blue);
  background: var(--info-bg);
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-md);
}

.progress-bar {
  height: 8px;
  background: var(--bg-tertiary);
  border-radius: var(--radius-md);
  overflow: hidden;
  margin-bottom: 1rem;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent-blue) 0%, var(--accent-blue-dark) 100%);
  transition: width 0.3s ease;
  border-radius: var(--radius-md);
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
}

.progress-info {
  text-align: center;
  margin-bottom: 1.5rem;
}

.progress-text {
  margin: 0.75rem 0;
  font-size: 0.9375rem;
  color: var(--text-primary);
  font-weight: 500;
}

.progress-stats {
  margin: 0.5rem 0 0 0;
  font-size: 0.8125rem;
  color: var(--text-secondary);
  font-weight: 500;
}

/* Results Section */
.result-section {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.success-banner {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--success-bg);
  border: 1px solid var(--success);
  border-radius: var(--radius-lg);
  margin-bottom: 1.5rem;
}

.success-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background: var(--success);
  color: white;
  border-radius: 50%;
  font-size: 1.5rem;
  font-weight: bold;
  flex-shrink: 0;
}

.success-content h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--success);
}

.success-content p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.result-details {
  padding: 1.5rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  margin-bottom: 1.5rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--border-light);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 500;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.detail-value {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.9375rem;
}

.result-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.action-btn {
  flex: 1;
  padding: 0.875rem;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.download-btn {
  background: var(--accent-blue);
  color: white;
}

.download-btn:hover {
  background: var(--accent-blue-dark);
}

.reset-btn {
  background: var(--bg-primary);
  color: var(--text-secondary);
  border: 1px solid var(--border-medium);
}

.reset-btn:hover {
  background: var(--bg-secondary);
  border-color: var(--primary-light);
}

.logs-btn {
  background: var(--bg-primary);
  color: var(--text-secondary);
  border: 1px solid var(--border-medium);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: all var(--transition-base);
}

.logs-btn:hover {
  background: var(--bg-secondary);
  border-color: var(--primary-light);
  color: var(--text-primary);
  text-decoration: none;
}

.coupons-preview {
  padding: 1.5rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
}

.coupons-preview h4 {
  margin: 0 0 1rem 0;
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
}

.preview-count {
  margin: 1rem 0 0 0;
  font-size: 0.8125rem;
  color: var(--text-secondary);
  font-weight: 500;
  text-align: center;
}

.coupons-grid-scroll {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  background: var(--bg-primary);
  padding: 0.75rem;
}

.coupons-grid-scroll::-webkit-scrollbar {
  width: 8px;
}

.coupons-grid-scroll::-webkit-scrollbar-track {
  background: var(--bg-tertiary);
  border-radius: var(--radius-sm);
}

.coupons-grid-scroll::-webkit-scrollbar-thumb {
  background: var(--border-medium);
  border-radius: var(--radius-sm);
}

.coupons-grid-scroll::-webkit-scrollbar-thumb:hover {
  background: var(--primary-light);
}

.coupons-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  align-items: start;
}

.code-item {
  padding: 0.75rem 0.5rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-family: 'Courier New', monospace;
  font-weight: 500;
  color: var(--text-primary);
  text-align: center;
  word-break: break-all;
  line-height: 1.3;
  transition: all var(--transition-base);
}

.code-item:hover {
  border-color: var(--accent-blue);
  background: var(--info-bg);
  color: var(--accent-blue);
}

/* Real-time preview during generation */
.realtime-preview {
  margin-top: 1.5rem;
  padding: 1.25rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  border-left: 3px solid var(--accent-blue);
}

.realtime-preview h5 {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.code-item.generating {
  animation: pulse 1.5s ease-in-out infinite;
  border-color: var(--accent-blue);
  background: var(--info-bg);
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(0.98);
  }
}

/* Responsive grid for mobile */
@media (max-width: 480px) {
  .coupons-grid {
    grid-template-columns: 1fr;
  }

  .coupons-grid-scroll {
    max-height: 200px;
  }
}
</style>