<script setup>
import { ref, onMounted, computed } from 'vue'
import { Icon } from '@iconify/vue'

const settings = ref({
  user_limits: {
    administrator: {
      max_coupons_per_batch: 1000,
      max_coupons_per_day: 5000,
      max_coupons_total: 50000
    },
    shop_manager: {
      max_coupons_per_batch: 100,
      max_coupons_per_day: 500,
      max_coupons_total: 5000
    }
  },
  data_retention: {
    log_retention_days: 90,
    auto_delete_logs: false,
    cleanup_expired_coupons: false
  },
  generator_settings: {
    default_quantity: 10,
    default_prefix: 'COUPON',
    default_character_count: 5,
    allow_bulk_generation: true
  }
})

const loading = ref(false)
const saving = ref(false)
const saveMessage = ref('')
const saveError = ref('')

// Check if current user is administrator
const isAdmin = computed(function() {
  return coupolic.current_user_can === 'administrator' ||
         (coupolic.current_user_roles && coupolic.current_user_roles.includes('administrator'))
})

function loadSettings() {
  loading.value = true

  const data = new FormData()
  data.append('action', 'coupolic_get_settings')
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
      settings.value = result.data.settings
    } else {
      console.error('Failed to load settings')
    }
  })
  .catch(function(error) {
    console.error('Failed to load settings:', error)
  })
  .finally(function() {
    loading.value = false
  })
}

function saveSettings() {
  saving.value = true
  saveMessage.value = ''
  saveError.value = ''

  const data = new FormData()
  data.append('action', 'coupolic_save_settings')
  data.append('nonce', coupolic.nonce)

  // User limits
  data.append('admin_max_coupons', settings.value.user_limits.administrator.max_coupons_per_batch)
  data.append('admin_max_daily', settings.value.user_limits.administrator.max_coupons_per_day)
  data.append('admin_max_total', settings.value.user_limits.administrator.max_coupons_total)
  data.append('manager_max_coupons', settings.value.user_limits.shop_manager.max_coupons_per_batch)
  data.append('manager_max_daily', settings.value.user_limits.shop_manager.max_coupons_per_day)
  data.append('manager_max_total', settings.value.user_limits.shop_manager.max_coupons_total)

  // Data retention
  data.append('log_retention_days', settings.value.data_retention.log_retention_days)
  data.append('auto_delete_logs', settings.value.data_retention.auto_delete_logs ? '1' : '0')
  data.append('cleanup_expired_coupons', settings.value.data_retention.cleanup_expired_coupons ? '1' : '0')

  // Generator settings
  data.append('default_quantity', settings.value.generator_settings.default_quantity)
  data.append('default_prefix', settings.value.generator_settings.default_prefix)
  data.append('default_character_count', settings.value.generator_settings.default_character_count)
  data.append('allow_bulk_generation', settings.value.generator_settings.allow_bulk_generation ? '1' : '0')

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      saveMessage.value = result.data.message || 'Settings saved successfully!'
      setTimeout(function() {
        saveMessage.value = ''
      }, 3000)
    } else {
      saveError.value = result.data.message || 'Failed to save settings'
    }
  })
  .catch(function(error) {
    console.error('Failed to save settings:', error)
    saveError.value = 'Failed to save settings. Please try again.'
  })
  .finally(function() {
    saving.value = false
  })
}

function resetToDefaults() {
  if (confirm('Are you sure you want to reset all settings to defaults?')) {
    settings.value = {
      user_limits: {
        administrator: {
          max_coupons_per_batch: 1000,
          max_coupons_per_day: 5000,
          max_coupons_total: 50000
        },
        shop_manager: {
          max_coupons_per_batch: 100,
          max_coupons_per_day: 500,
          max_coupons_total: 5000
        }
      },
      data_retention: {
        log_retention_days: 90,
        auto_delete_logs: false,
        cleanup_expired_coupons: false
      },
      generator_settings: {
        default_quantity: 10,
        default_prefix: 'COUPON',
        default_character_count: 5,
        allow_bulk_generation: true
      }
    }
    saveSettings()
  }
}

onMounted(function() {
  loadSettings()
})
</script>

<template>
  <div class="settings-container">
    <div class="settings-header">
      <div class="header-content">
        <div class="breadcrumb">
          <a href="admin.php?page=coupolic" class="breadcrumb-link">
            <Icon icon="proicons:arrow-left" width="16" height="16" />
            Back to Coupon Generator
          </a>
        </div>
        <h1>Coupolic Settings</h1>
        <p class="header-description">Configure user limits, data retention, and generator defaults</p>
      </div>
    </div>

    <!-- Save Status Messages -->
    <div v-if="saveMessage" class="alert alert-success">
      <Icon icon="proicons:check-circle" width="20" height="20" />
      {{ saveMessage }}
    </div>

    <div v-if="saveError" class="alert alert-error">
      <Icon icon="proicons:error-warning" width="20" height="20" />
      {{ saveError }}
    </div>

    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading settings...</p>
    </div>

    <div v-else class="settings-content">
      <!-- User Limits Section -->
      <div class="setting-section">
        <div class="section-header">
          <div class="section-title">
            <Icon icon="proicons:users" width="20" height="20" />
            <h2>User Limits</h2>
          </div>
          <p class="section-description">Configure coupon generation limits per user role</p>
        </div>

        <div class="limits-grid">
          <!-- Administrator Limits -->
          <div class="role-limits">
            <div class="role-header">
              <Icon icon="proicons:shield" width="18" height="18" />
              <h3>Administrator</h3>
              <span class="role-badge">Full Access</span>
            </div>

            <div class="limit-fields">
              <div class="limit-field">
                <label>Max Per Batch</label>
                <input
                  type="number"
                  v-model="settings.user_limits.administrator.max_coupons_per_batch"
                  class="form-input"
                  :disabled="!isAdmin"
                  min="1"
                  max="10000"
                />
                <p class="field-description">Maximum coupons in single generation</p>
              </div>

              <div class="limit-field">
                <label>Daily Limit</label>
                <input
                  type="number"
                  v-model="settings.user_limits.administrator.max_coupons_per_day"
                  class="form-input"
                  :disabled="!isAdmin"
                  min="1"
                  max="100000"
                />
                <p class="field-description">Total coupons per day</p>
              </div>

              <div class="limit-field">
                <label>Total Limit</label>
                <input
                  type="number"
                  v-model="settings.user_limits.administrator.max_coupons_total"
                  class="form-input"
                  :disabled="!isAdmin"
                  min="1"
                  max="1000000"
                />
                <p class="field-description">Lifetime total coupon limit</p>
              </div>
            </div>
          </div>

          <!-- Shop Manager Limits -->
          <div class="role-limits">
            <div class="role-header">
              <Icon icon="proicons:person-circle" width="18" height="18" />
              <h3>Shop Manager</h3>
              <span class="role-badge manager">Limited</span>
            </div>

            <div class="limit-fields">
              <div class="limit-field">
                <label>Max Per Batch</label>
                <input
                  type="number"
                  v-model="settings.user_limits.shop_manager.max_coupons_per_batch"
                  class="form-input"
                  :disabled="!isAdmin"
                  min="1"
                  max="1000"
                />
                <p class="field-description">Maximum coupons in single generation</p>
              </div>

              <div class="limit-field">
                <label>Daily Limit</label>
                <input
                  type="number"
                  v-model="settings.user_limits.shop_manager.max_coupons_per_day"
                  class="form-input"
                  :disabled="!isAdmin"
                  min="1"
                  max="10000"
                />
                <p class="field-description">Total coupons per day</p>
              </div>

              <div class="limit-field">
                <label>Total Limit</label>
                <input
                  type="number"
                  v-model="settings.user_limits.shop_manager.max_coupons_total"
                  class="form-input"
                  :disabled="!isAdmin"
                  min="1"
                  max="100000"
                />
                <p class="field-description">Lifetime total coupon limit</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!isAdmin" class="permission-notice">
          <Icon icon="proicons:lock" width="16" height="16" />
          <span>Only administrators can modify user limits</span>
        </div>
      </div>

      <!-- Data Retention Section -->
      <div class="setting-section">
        <div class="section-header">
          <div class="section-title">
            <Icon icon="proicons:database" width="20" height="20" />
            <h2>Data Retention</h2>
          </div>
          <p class="section-description">Configure automatic cleanup and data storage policies</p>
        </div>

        <div class="retention-fields">
          <div class="field-group">
            <label for="log_retention_days">Log Retention Period (Days)</label>
            <input
              id="log_retention_days"
              type="number"
              v-model="settings.data_retention.log_retention_days"
              class="form-input"
              min="7"
              max="365"
            />
            <p class="field-description">Automatically delete logs older than this period. Default: 90 days</p>
          </div>

          <div class="checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="settings.data_retention.auto_delete_logs"
                class="checkbox-input"
              />
              <span>Auto-delete Old Logs</span>
            </label>
            <p class="field-description">Automatically remove logs that exceed retention period</p>
          </div>

          <div class="checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="settings.data_retention.cleanup_expired_coupons"
                class="checkbox-input"
              />
              <span>Cleanup Expired Coupons</span>
            </label>
            <p class="field-description">Automatically delete WooCommerce coupons that have expired</p>
          </div>
        </div>
      </div>

      <!-- Generator Defaults Section -->
      <div class="setting-section">
        <div class="section-header">
          <div class="section-title">
            <Icon icon="proicons:archive" width="20" height="20" />
            <h2>Generator Defaults</h2>
          </div>
          <p class="section-description">Default values for the coupon generator wizard</p>
        </div>

        <div class="defaults-grid">
          <div class="default-field">
            <label for="default_quantity">Default Quantity</label>
            <input
              id="default_quantity"
              type="number"
              v-model="settings.generator_settings.default_quantity"
              class="form-input"
              min="1"
              max="1000"
            />
            <p class="field-description">Default number of coupons to generate</p>
          </div>

          <div class="default-field">
            <label for="default_prefix">Default Prefix</label>
            <input
              id="default_prefix"
              type="text"
              v-model="settings.generator_settings.default_prefix"
              class="form-input"
              maxlength="10"
            />
            <p class="field-description">Default prefix for generated coupon codes</p>
          </div>

          <div class="default-field">
            <label for="default_character_count">Character Count</label>
            <input
              id="default_character_count"
              type="number"
              v-model="settings.generator_settings.default_character_count"
              class="form-input"
              min="3"
              max="20"
            />
            <p class="field-description">Number of random characters in coupon codes</p>
          </div>

          <div class="checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="settings.generator_settings.allow_bulk_generation"
                class="checkbox-input"
              />
              <span>Allow Bulk Generation</span>
            </label>
            <p class="field-description">Enable users to generate large batches at once</p>
          </div>
        </div>
      </div>

      <!-- Save and Reset Buttons -->
      <div class="save-section">
        <div class="button-group">
          <button
            @click="resetToDefaults"
            class="reset-button"
            :disabled="saving"
            type="button"
          >
            <Icon icon="proicons:arrow-undo-2" width="16" height="16" />
            Reset to Defaults
          </button>
          <button
            @click="saveSettings"
            class="save-button"
            :disabled="saving"
            type="button"
          >
            <Icon icon="proicons:save" width="16" height="16" />
            <span v-if="!saving">Save Settings</span>
            <span v-else>Saving...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.settings-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border-light);
}

.header-content h1 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.breadcrumb {
  margin-bottom: 0.75rem;
}

.breadcrumb-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: color var(--transition-base);
}

.breadcrumb-link:hover {
  color: var(--accent-blue);
}

.header-description {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin: 0;
}

.button-group {
  display: flex;
  gap: 1rem;
  align-items: center;
  justify-content: center;
}

/* Alert Messages */
.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: var(--radius-md);
  margin-bottom: 1.5rem;
  font-size: 0.9375rem;
  font-weight: 500;
}

.alert-success {
  background: var(--success-bg);
  color: var(--success);
  border: 1px solid var(--success);
}

.alert-error {
  background: var(--error-bg);
  color: var(--error);
  border: 1px solid var(--error);
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  margin: 0 0 1rem 0;
  border: 3px solid var(--border-light);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Settings Content */
.settings-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.setting-section {
  background: var(--bg-primary);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.section-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-light);
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.section-title h2 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.section-title Icon {
  color: var(--accent-blue);
}

.section-description {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 0;
}

/* Limits Grid */
.limits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
}

.role-limits {
  background: var(--bg-secondary);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 1.25rem;
}

.role-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-light);
}

.role-header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  flex: 1;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-badge.manager {
  background: var(--warning-bg);
  color: var(--warning);
}

.limit-fields {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.limit-field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.limit-field label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.permission-notice {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: var(--warning-bg);
  color: var(--warning);
  border-radius: var(--radius-sm);
  font-size: 0.8125rem;
  margin-top: 1rem;
}

/* Retention Fields */
.retention-fields {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.checkbox-input {
  width: 18px;
  height: 18px;
  accent-color: var(--accent-blue);
}

/* Defaults Grid */
.defaults-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.25rem;
}

.default-field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.default-field label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

/* Form Controls */
.form-input {
  padding: 0.625rem 0.75rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-family: 'Inter', sans-serif;
  background: var(--bg-primary);
  color: var(--text-primary);
  outline: none;
  transition: all var(--transition-base);
}

.form-input:focus {
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
}

.form-input:disabled {
  background: var(--bg-tertiary);
  color: var(--text-muted);
  cursor: not-allowed;
}

.field-description {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin: 0;
  line-height: 1.4;
}

/* Buttons */
.reset-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
}

.reset-button:hover:not(:disabled) {
  background: var(--bg-tertiary);
  border-color: var(--primary-light);
  transform: translateY(-1px);
}

.reset-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.save-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  background: var(--accent-blue);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
  min-width: 180px;
}

.save-button:hover:not(:disabled) {
  background: var(--accent-blue-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.save-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.save-section {
  display: flex;
  justify-content: center;
  padding: 2rem 0 0 0;
  border-top: 1px solid var(--border-light);
  margin-top: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
  .settings-container {
    padding: 1rem;
  }

  .settings-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .limits-grid {
    grid-template-columns: 1fr;
  }

  .defaults-grid {
    grid-template-columns: 1fr;
  }

  .button-group {
    flex-direction: column;
    width: 100%;
    gap: 0.75rem;
  }

  .reset-button,
  .save-button {
    width: 100%;
  }
}
</style>