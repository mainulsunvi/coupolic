<script setup>
import { ref, onMounted, computed } from 'vue'
import { Icon } from '@iconify/vue'

const logs = ref([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const totalRecords = ref(0)

// Filters
const statusFilter = ref('')
const searchQuery = ref('')
const dateFilter = ref('')

// Selected batch for details
const selectedBatch = ref(null)
const showDetailsModal = ref(false)
const loadingDetails = ref(false)
const batchDetails = ref(null)

// Search debounce
let searchTimeout = null

const perPage = 20

// Computed filtered logs
const filteredLogs = computed(function() {
  let filtered = logs.value

  if (statusFilter.value) {
    filtered = filtered.filter(function(log) {
      return log.status === statusFilter.value
    })
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(function(log) {
      return log.batch_id.toLowerCase().includes(query) ||
             log.user_login.toLowerCase().includes(query)
    })
  }

  return filtered
})

function debounceSearch() {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(function() {
    applyFilters()
  }, 300)
}

function loadLogs() {
  loading.value = true

  const data = new FormData()
  data.append('action', 'coupolic_get_logs')
  data.append('nonce', coupolic.nonce)
  data.append('page', currentPage.value)
  data.append('per_page', perPage)

  // Add filters
  if (statusFilter.value) {
    data.append('status', statusFilter.value)
  }

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      logs.value = result.data.logs || []
      totalPages.value = result.data.pages || 1
      totalRecords.value = result.data.total || 0
    }
  })
  .catch(function(error) {
    console.error('Failed to load logs:', error)
  })
  .finally(function() {
    loading.value = false
  })
}

function viewBatchDetails(batchId) {
  selectedBatch.value = batchId
  showDetailsModal.value = true
  loadingDetails.value = true
  batchDetails.value = null // Reset previous details

  console.log('Loading details for batch:', batchId)

  const data = new FormData()
  data.append('action', 'coupolic_get_batch_details')
  data.append('nonce', coupolic.nonce)
  data.append('batch_id', batchId)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    console.log('Batch details response:', result)

    if (result.success) {
      batchDetails.value = result.data
      console.log('Batch details loaded:', batchDetails.value)
    } else {
      console.error('Failed to load batch details:', result.data?.message || 'Unknown error')
      alert(result.data?.message || 'Failed to load batch details')
    }
  })
  .catch(function(error) {
    console.error('Failed to load batch details:', error)
    alert('Failed to load batch details: ' + error.message)
  })
  .finally(function() {
    loadingDetails.value = false
  })
}

function closeDetailsModal() {
  showDetailsModal.value = false
  selectedBatch.value = null
  batchDetails.value = null
}

function exportBatch(batchId) {
  const data = new FormData()
  data.append('action', 'coupolic_export_batch')
  data.append('nonce', coupolic.nonce)
  data.append('batch_id', batchId)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success && result.data.download_url) {
      // Create download link
      const link = document.createElement('a')
      link.href = result.data.download_url
      link.download = 'coupons_batch_' + batchId + '.csv'
      link.click()
    } else {
      alert('Failed to export batch')
    }
  })
  .catch(function(error) {
    console.error('Failed to export batch:', error)
    alert('Failed to export batch')
  })
}

function deleteBatch(batchId) {
  if (!confirm('Are you sure you want to delete this batch and all associated logs? This action cannot be undone.')) {
    return
  }

  const data = new FormData()
  data.append('action', 'coupolic_delete_batch')
  data.append('nonce', coupolic.nonce)
  data.append('batch_id', batchId)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      alert(result.data.message || 'Batch deleted successfully')
      loadLogs() // Reload logs
    } else {
      alert('Failed to delete batch')
    }
  })
  .catch(function(error) {
    console.error('Failed to delete batch:', error)
    alert('Failed to delete batch')
  })
}

function getStatusBadgeClass(status) {
  switch(status) {
    case 'completed':
      return 'status-success'
    case 'failed':
      return 'status-error'
    case 'partial':
      return 'status-warning'
    default:
      return 'status-default'
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const year = date.getFullYear()
  return month + '-' + day + '-' + year
}

function changePage(page) {
  currentPage.value = page
  loadLogs()
}

function applyFilters() {
  currentPage.value = 1
  loadLogs()
}

function resetFilters() {
  statusFilter.value = ''
  searchQuery.value = ''
  dateFilter.value = ''
  currentPage.value = 1
  loadLogs()
}

function formatSettingKey(key) {
  // Convert snake_case or camelCase to readable format
  return key
    .replace(/_/g, ' ')
    .replace(/([A-Z])/g, ' $1')
    .replace(/^\w/, c => c.toUpperCase())
    .trim()
}

function formatSettingValue(value) {
  if (Array.isArray(value)) {
    return value.join(', ')
  }
  if (typeof value === 'boolean') {
    return value ? 'Yes' : 'No'
  }
  if (value === null || value === undefined) {
    return 'N/A'
  }
  return String(value)
}

onMounted(function() {
  loadLogs()
})
</script>

<template>
  <div class="logs-container">
    <!-- Unified Header -->
    <div class="logs-header">
      <div class="header-top">
        <div class="header-title">
          <h1>Logs & History</h1>
          <p class="header-description">View and manage your coupon generation history</p>
        </div>
        <button @click="loadLogs" class="refresh-btn" :disabled="loading" :class="{ 'is-loading': loading }">
          <Icon icon="proicons:arrow-sync" width="18" height="18" class="refresh-icon" />
          <span>{{ loading ? 'Refreshing...' : 'Refresh' }}</span>
        </button>
      </div>

      <div class="header-filters">
        <div class="search-wrapper">
          <Icon icon="proicons:search" width="16" height="16" class="search-icon" />
          <input
            v-model="searchQuery"
            type="text"
            class="search-input"
            placeholder="Search by batch ID or user..."
            @keyup.enter="applyFilters"
            @input="debounceSearch"
          />
        </div>

        <div class="filter-selectors">
          <select v-model="statusFilter" class="status-filter" @change="applyFilters">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
            <option value="partial">Partial</option>
          </select>
        </div>

        <div class="filter-actions">
          <button @click="resetFilters" class="btn-reset" :disabled="!searchQuery && !statusFilter" :class="{ 'has-active-filters': searchQuery || statusFilter }">
            <Icon icon="proicons:arrow-undo" width="16" height="16" />
            <span>Reset</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && logs.length === 0" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading logs...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && logs.length === 0" class="empty-state">
      <div class="empty-icon">
        <Icon icon="proicons:database" width="64" height="64" />
      </div>
      <h3>No logs yet</h3>
      <p>Once you generate coupons, your history will appear here.</p>
      <router-link to="/coupon-wizard" class="btn-create">
        <Icon icon="proicons:wand" width="18" height="18" />
        <span>Generate Your First Coupons</span>
      </router-link>
    </div>

    <!-- Logs Table -->
    <div v-else class="logs-table-container">
      <div class="table-header">
        <div class="table-info">
          <span class="records-count">{{ totalRecords }} total records</span>
          <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="logs-table">
          <thead>
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Date</th>
              <th>Coupons</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(log, index) in logs" :key="log.id" class="log-row">
              <td>
                <div class="row-number">
                  {{ (currentPage - 1) * perPage + index + 1 }}
                </div>
              </td>
              <td>
                <div class="user-info">
                  <Icon icon="proicons:user" width="14" height="14" />
                  <span>{{ log.user_login }}</span>
                </div>
              </td>
              <td>
                <div class="date-info">
                  <Icon icon="proicons:calendar" width="14" height="14" />
                  <span>{{ formatDate(log.generation_time) }}</span>
                </div>
              </td>
              <td>
                <div class="count-info">
                  <span class="count-number">{{ log.coupon_count }}</span>
                  <span class="count-label">coupons</span>
                </div>
              </td>
              <td>
                <span :class="['status-badge', getStatusBadgeClass(log.status)]">
                  {{ log.status }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    @click="viewBatchDetails(log.batch_id)"
                    class="action-btn view-btn"
                    title="View Details"
                  >
                    <Icon icon="proicons:eye" width="16" height="16" />
                  </button>
                  <button
                    @click="exportBatch(log.batch_id)"
                    class="action-btn export-btn"
                    title="Export CSV"
                  >
                    <Icon icon="proicons:arrow-download" width="16" height="16" />
                  </button>
                  <button
                    @click="deleteBatch(log.batch_id)"
                    class="action-btn delete-btn"
                    title="Delete Batch"
                  >
                    <Icon icon="proicons:delete" width="16" height="16" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="totalPages > 1">
        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="page-btn prev-btn"
        >
          <Icon icon="proicons:arrow-left" width="16" height="16" />
          <span>Previous</span>
        </button>

        <div class="page-numbers">
          <button
            v-for="page in totalPages"
            :key="page"
            @click="changePage(page)"
            :class="['page-number', { active: page === currentPage }]"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="changePage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="page-btn next-btn"
        >
          <span>Next</span>
          <Icon icon="proicons:arrow-right" width="16" height="16" />
        </button>
      </div>
    </div>

    <!-- Batch Details Modal -->
    <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Batch Details</h2>
          <button @click="closeDetailsModal" class="modal-close">
            <Icon icon="proicons:cancel" width="20" height="20" />
          </button>
        </div>

        <div class="modal-body">
          <div v-if="loadingDetails" class="modal-loading">
            <div class="loading-spinner"></div>
            <p>Loading batch details...</p>
          </div>

          <div v-else-if="!batchDetails" class="modal-error">
            <Icon icon="proicons:warning-circle" width="32" height="32" />
            <h3>Failed to Load Details</h3>
            <p>Unable to load batch details. Please try again.</p>
            <button @click="closeDetailsModal" class="btn-close-error">Close</button>
          </div>

          <div v-else class="batch-details">
            <div class="detail-section">
              <h3>Information</h3>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">Batch ID</span>
                  <span class="detail-value">{{ batchDetails.batch_id }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">User</span>
                  <span class="detail-value">{{ batchDetails.user_login }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Date</span>
                  <span class="detail-value">{{ formatDate(batchDetails.generation_time) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Status</span>
                  <span :class="['detail-value', 'status-badge', getStatusBadgeClass(batchDetails.status)]">
                    {{ batchDetails.status }}
                  </span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Generated</span>
                  <span class="detail-value">{{ batchDetails.success_count || 0 }} / {{ batchDetails.coupon_count || 0 }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Failed</span>
                  <span class="detail-value">{{ batchDetails.failed_count || 0 }}</span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h3>Generated Coupons ({{ batchDetails.coupons ? batchDetails.coupons.length : 0 }})</h3>
              <div class="coupons-list" v-if="batchDetails.coupons && batchDetails.coupons.length > 0">
                <div v-for="coupon in batchDetails.coupons" :key="coupon.id || coupon.code" class="coupon-item">
                  <div class="coupon-code">{{ coupon.code }}</div>
                  <a :href="coupon.link" target="_blank" class="coupon-link" v-if="coupon.link">
                    <Icon icon="proicons:external-link" width="12" height="12" />
                    Edit
                  </a>
                </div>
              </div>
              <div v-else class="no-coupons">
                <Icon icon="proicons:info" width="24" height="24" />
                <p>No coupons available for this batch</p>
              </div>
            </div>

            <div class="detail-section" v-if="batchDetails.error_message">
              <h3>Error Information</h3>
              <div class="error-message">
                <Icon icon="proicons:warning" width="16" height="16" />
                <span>{{ batchDetails.error_message }}</span>
              </div>
            </div>

            <div class="detail-section" v-if="batchDetails.settings && Object.keys(batchDetails.settings).length > 0">
              <h3>Generation Settings</h3>
              <div class="settings-grid">
                <div class="setting-item" v-for="(value, key) in batchDetails.settings" :key="key">
                  <span class="setting-label">{{ formatSettingKey(key) }}</span>
                  <span class="setting-value">{{ formatSettingValue(value) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="closeDetailsModal" class="btn-close-modal">
            <Icon icon="proicons:close" width="16" height="16" />
            Close
          </button>
          <button
            v-if="batchDetails && batchDetails.coupons && batchDetails.coupons.length > 0"
            @click="exportBatch(selectedBatch)"
            class="btn-export-modal"
          >
            <Icon icon="proicons:download" width="16" height="16" />
            Export CSV
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.logs-container {
  min-height: 100vh;
  background: var(--bg-tertiary);
  padding: 1.5rem;
}

/* Header */
.logs-header {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid var(--border-light);
}

.header-title h1 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.header-description {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin: 0;
}

.refresh-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: var(--accent-blue);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  white-space: nowrap;
}

.refresh-btn:hover:not(:disabled) {
  background: var(--accent-blue-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

.refresh-btn.is-loading .refresh-icon {
  animation: spin 1s linear infinite;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.refresh-btn span {
  font-size: 0.875rem;
}

/* Header Filters */
.header-filters {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-wrapper {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.625rem 0.875rem 0.625rem 2.5rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-family: 'Inter', sans-serif;
  background: var(--bg-secondary);
  color: var(--text-primary);
  outline: none;
  transition: all var(--transition-base);
}

.search-input:focus {
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
  background: var(--bg-primary);
}

.search-input::placeholder {
  color: var(--text-muted);
}

.filter-selectors {
  display: flex;
  gap: 0.75rem;
}

.status-filter {
  padding: 0.625rem 2rem 0.625rem 0.875rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-family: 'Inter', sans-serif;
  background: var(--bg-secondary);
  color: var(--text-primary);
  outline: none;
  cursor: pointer;
  transition: all var(--transition-base);
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
}

.status-filter:focus {
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 2px var(--info-bg);
  background-color: var(--bg-primary);
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
  height: 50px;
}

.btn-reset {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  padding: 0.625rem 1rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  background: var(--bg-secondary);
  color: var(--text-secondary);
  white-space: nowrap;
}

.btn-reset:hover:not(:disabled) {
  background: var(--bg-tertiary);
  border-color: var(--primary-light);
  transform: translateY(-1px);
}

.btn-reset.has-active-filters {
  background: var(--warning-bg);
  border-color: var(--warning);
  color: var(--warning);
}

.btn-reset.has-active-filters:hover:not(:disabled) {
  background: var(--warning);
  color: white;
}

.btn-reset:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-reset span {
  font-size: 0.875rem;
}

/* Loading & Empty States */
.loading-state, .empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  margin: 0 auto 1rem;
  border: 3px solid var(--border-light);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state .empty-icon {
  color: var(--text-muted);
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin: 0 0 1.5rem 0;
}

.btn-create {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--accent-blue);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all var(--transition-base);
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.btn-create:hover {
  background: var(--accent-blue-dark);
  text-decoration: none;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

/* Table */
.logs-table-container {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  overflow: hidden;
}

.table-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-light);
  background: var(--bg-secondary);
}

.table-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.records-count, .page-info {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.table-wrapper {
  overflow-x: auto;
}

.logs-table {
  width: 100%;
  border-collapse: collapse;
}

.logs-table th {
  background: var(--bg-secondary);
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid var(--border-light);
}

.logs-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border-light);
}

.log-row:hover {
  background: var(--bg-secondary);
}

.user-info, .date-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-primary);
  font-size: 0.875rem;
}

.row-number {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: var(--bg-tertiary);
  color: var(--text-primary);
  border-radius: 50%;
  font-size: 0.875rem;
  font-weight: 600;
  margin: 0 auto;
}

.count-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.count-number {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
}

.count-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-md);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-success {
  background: var(--success-bg);
  color: var(--success);
}

.status-error {
  background: var(--error-bg);
  color: var(--error);
}

.status-warning {
  background: var(--warning-bg);
  color: var(--warning);
}

.status-default {
  background: var(--bg-tertiary);
  color: var(--text-secondary);
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  background: var(--bg-primary);
  color: var(--text-secondary);
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  white-space: nowrap;
}

.action-btn:hover {
  border-color: var(--primary-light);
  color: var(--text-primary);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.view-btn:hover {
  border-color: var(--accent-blue);
  color: var(--accent-blue);
  background: var(--info-bg);
}

.export-btn:hover {
  border-color: var(--success);
  color: var(--success);
  background: var(--success-bg);
}

.delete-btn:hover {
  border-color: var(--error);
  color: var(--error);
  background: var(--error-bg);
}

.action-btn span {
  font-size: 0.8125rem;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-light);
  background: var(--bg-secondary);
}

.page-numbers {
  display: flex;
  gap: 0.375rem;
}

.page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  white-space: nowrap;
}

.page-btn:hover:not(:disabled) {
  border-color: var(--primary-light);
  background: var(--bg-secondary);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-btn span {
  font-size: 0.8125rem;
}

.page-number {
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  min-width: 36px;
}

.page-number:hover {
  border-color: var(--primary-light);
  background: var(--bg-secondary);
  transform: translateY(-1px);
}

.page-number.active {
  background: var(--accent-blue);
  color: white;
  border-color: var(--accent-blue);
  transform: scale(1.1);
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-lg);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-light);
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-base);
}

.modal-close:hover {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.modal-loading {
  text-align: center;
  padding: 2rem;
}

.modal-error {
  text-align: center;
  padding: 2rem;
  color: var(--error);
}

.modal-error Icon {
  margin-bottom: 1rem;
}

.modal-error h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.modal-error p {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin: 0 0 1.5rem 0;
}

.btn-close-error {
  padding: 0.625rem 1.25rem;
  background: var(--error);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
}

.btn-close-error:hover {
  background: var(--error-dark);
}

.batch-details {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-section h3 {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 1rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-value {
  font-size: 0.9375rem;
  color: var(--text-primary);
  font-weight: 500;
}

.coupons-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 0.5rem;
  max-height: 300px;
  overflow-y: auto;
  padding: 0.5rem;
  background: var(--bg-secondary);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
}

.coupon-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  background: var(--bg-primary);
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-light);
  font-size: 0.8125rem;
}

.coupon-code {
  font-family: 'Courier New', monospace;
  font-weight: 500;
  color: var(--accent-blue);
}

.coupon-link {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.75rem;
  transition: all var(--transition-base);
}

.coupon-link:hover {
  color: var(--accent-blue);
}

.no-coupons {
  text-align: center;
  padding: 2rem;
  color: var(--text-muted);
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: var(--error-bg);
  color: var(--error);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.setting-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.5rem;
  background: var(--bg-secondary);
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-light);
}

.setting-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.setting-value {
  font-size: 0.875rem;
  color: var(--text-primary);
  font-weight: 500;
  word-break: break-word;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-light);
}

.btn-close-modal, .btn-export-modal {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border: 1px solid var(--border-medium);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-base);
  min-width: 120px;
}

.btn-close-modal {
  background: var(--bg-secondary);
  color: var(--text-secondary);
}

.btn-close-modal:hover {
  background: var(--bg-tertiary);
  border-color: var(--primary-light);
  transform: translateY(-1px);
}

.btn-export-modal {
  background: var(--success);
  color: white;
  border-color: var(--success);
}

.btn-export-modal:hover {
  background: var(--success-dark);
  border-color: var(--success-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .logs-container {
    padding: 1rem;
  }

  .logs-header {
    padding: 1.25rem;
  }

  .header-top {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .header-filters {
    flex-direction: column;
    gap: 0.75rem;
  }

  .search-wrapper {
    min-width: 100%;
  }

  .filter-selectors {
    width: 100%;
  }

  .status-filter {
    width: 100%;
  }

  .filter-actions {
    width: 100%;
  }

  .btn-reset {
    width: 100%;
  }

  .table-wrapper {
    overflow-x: auto;
  }

  .logs-table {
    min-width: 600px;
  }

  .pagination {
    flex-direction: column;
    gap: 1rem;
  }

  .page-numbers {
    flex-wrap: wrap;
    justify-content: center;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .coupons-list {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>