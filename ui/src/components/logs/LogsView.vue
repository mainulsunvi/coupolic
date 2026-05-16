<script setup>
import { ref, onMounted } from 'vue'

const logs = ref([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)

function loadLogs() {
  loading.value = true

  const data = new FormData()
  data.append('action', 'coupolic_get_logs')
  data.append('nonce', coupolic.nonce)
  data.append('page', currentPage.value)
  data.append('per_page', 20)

  fetch(coupolic.ajax_url, {
    method: 'POST',
    body: data
  })
  .then(function(response) {
    return response.json()
  })
  .then(function(result) {
    if (result.success) {
      logs.value = result.data.logs
      totalPages.value = result.data.pages
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
  // Implement batch details viewing
  console.log('View batch:', batchId)
}

function deleteBatch(batchId) {
  if (!confirm('Are you sure you want to delete this batch and all associated coupons?')) {
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
      alert(result.data.message)
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

onMounted(function() {
  loadLogs()
})
</script>

<template>
  <div class="logs-container">
    <h1>Coupolic Logs & History</h1>
    <p class="logs-description">View and manage your coupon generation history.</p>

    <div v-if="loading" class="loading">
      <p>Loading logs...</p>
    </div>

    <div v-else-if="logs.length === 0" class="empty-state">
      <h3>No logs yet</h3>
      <p>Once you generate coupons, your history will appear here.</p>
    </div>

    <div v-else class="logs-table">
      <table>
        <thead>
          <tr>
            <th>Batch ID</th>
            <th>User</th>
            <th>Date</th>
            <th>Count</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs" :key="log.id">
            <td><code>{{ log.batch_id }}</code></td>
            <td>{{ log.user_login }}</td>
            <td>{{ new Date(log.generation_time).toLocaleString() }}</td>
            <td>{{ log.coupon_count }}</td>
            <td>
              <span :class="['status-badge', 'status-' + log.status]">
                {{ log.status }}
              </span>
            </td>
            <td>
              <button @click="viewBatchDetails(log.batch_id)" class="action-button view-button">
                View
              </button>
              <button @click="deleteBatch(log.batch_id)" class="action-button delete-button">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination" v-if="totalPages > 1">
      <button
        v-for="page in totalPages"
        :key="page"
        @click="currentPage = page; loadLogs()"
        :class="['page-button', { active: page === currentPage }]"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.logs-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  font-size: 28px;
  margin-bottom: 8px;
  color: #333;
}

.logs-description {
  color: #666;
  margin-bottom: 24px;
  font-size: 14px;
}

.loading, .empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.logs-table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: #f5f5f5;
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #444;
  border-bottom: 2px solid #e0e0e0;
}

td {
  padding: 12px 16px;
  border-bottom: 1px solid #eee;
}

code {
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 3px;
  font-family: monospace;
  font-size: 12px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-completed {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-failed {
  background: #ffebee;
  color: #c62828;
}

.status-partial {
  background: #fff3e0;
  color: #e65100;
}

.action-button {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
  margin-right: 4px;
}

.view-button {
  background: #2196F3;
  color: white;
}

.delete-button {
  background: #f44336;
  color: white;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
}

.page-button {
  padding: 8px 12px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
}

.page-button.active {
  background: #4CAF50;
  color: white;
  border-color: #4CAF50;
}
</style>