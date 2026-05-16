<script setup>
import { ref, onMounted } from 'vue'

const settings = ref({
  log_retention: 90,
  cleanup_schedule: 'daily'
})

const loading = ref(false)

function loadSettings() {
  // In a real implementation, you'd fetch this from WordPress
  settings.value = {
    log_retention: parseInt(coupolic?.log_retention || 90),
    cleanup_schedule: coupolic?.cleanup_schedule || 'daily'
  }
}

function saveSettings() {
  loading.value = true

  const data = new FormData()
  data.append('action', 'coupolic_save_settings')
  data.append('nonce', coupolic.nonce)
  data.append('log_retention', settings.value.log_retention)
  data.append('cleanup_schedule', settings.value.cleanup_schedule)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      alert('Settings saved successfully!')
    } else {
      alert('Failed to save settings')
    }
  })
  .catch(function(error) {
    console.error('Failed to save settings:', error)
    alert('Failed to save settings')
  })
  .finally(function() {
    loading.value = false
  })
}

onMounted(function() {
  loadSettings()
})
</script>

<template>
  <div class="settings-container">
    <h1>Coupolic Settings</h1>
    <p class="settings-description">Configure system-wide settings for the coupon generator.</p>

    <div class="settings-form">
      <div class="setting-section">
        <h2>Log Management</h2>
        <p class="section-description">Control how long generation logs are stored and when they're cleaned up.</p>

        <div class="form-group">
          <label for="log_retention">Log Retention Period (days)</label>
          <input
            id="log_retention"
            type="number"
            v-model="settings.log_retention"
            min="7"
            max="365"
            class="form-input"
          />
          <p class="field-description">Logs older than this will be automatically deleted. Default: 90 days.</p>
        </div>

        <div class="form-group">
          <label for="cleanup_schedule">Cleanup Schedule</label>
          <select
            id="cleanup_schedule"
            v-model="settings.cleanup_schedule"
            class="form-select"
          >
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
          <p class="field-description">How often to run the cleanup process.</p>
        </div>
      </div>

      <div class="setting-section">
        <h2>User Limits</h2>
        <p class="section-description">Configure generation limits per user role. Only super admins can modify these.</p>

        <div class="limits-info">
          <p>⚠️ User limits can only be modified by users with administrator privileges.</p>
          <p>Current limits are configured in WordPress settings.</p>
        </div>
      </div>

      <div class="save-section">
        <button
          @click="saveSettings"
          class="save-button"
          :disabled="loading"
          type="button"
        >
          <span v-if="!loading">💾 Save Settings</span>
          <span v-else>⏳ Saving...</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.settings-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  font-size: 28px;
  margin-bottom: 8px;
  color: #333;
}

.settings-description {
  color: #666;
  margin-bottom: 32px;
  font-size: 14px;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.setting-section {
  padding: 24px;
  background: #f9f9f9;
  border-radius: 8px;
}

.setting-section h2 {
  font-size: 20px;
  margin-bottom: 8px;
  color: #444;
}

.section-description {
  color: #666;
  margin-bottom: 20px;
  font-size: 14px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #333;
  font-size: 14px;
}

.form-input, .form-select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: #4CAF50;
}

.field-description {
  margin-top: 8px;
  font-size: 13px;
  color: #666;
}

.limits-info {
  padding: 16px;
  background: #fff3e0;
  border-radius: 6px;
  border-left: 4px solid #ff9800;
}

.limits-info p {
  margin: 8px 0;
  font-size: 14px;
  color: #bf360c;
}

.save-section {
  text-align: center;
}

.save-button {
  padding: 12px 32px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.save-button:hover:not(:disabled) {
  background: #45a049;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.save-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>