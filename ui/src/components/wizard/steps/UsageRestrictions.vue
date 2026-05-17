<script setup>
import { inject } from 'vue'
import Input from '@/components/fields/Input.vue'
import Select from '@/components/fields/Select.vue'
import MultiSelect from '@/components/fields/MultiSelect.vue'
import { useWizardState } from '@/composables/useWizardState.js'

const wizardState = useWizardState()

// Get product/category data from WordPress
let products = {}
let categories = {}
let brands = {}

if (typeof coupolic !== 'undefined' && coupolic.products) {
  try {
    const parsedProducts = JSON.parse(coupolic.products)
    products = parsedProducts.reduce(function(acc, product) {
      acc[product.id] = `[${product.id}] - ${product.title} - ${product.price}`
      return acc
    }, {})
  } catch (e) {
    console.warn('Failed to parse products data:', e)
  }
}

if (typeof coupolic !== 'undefined' && coupolic.categories) {
  try {
    const parsedCategories = JSON.parse(coupolic.categories)
    categories = parsedCategories.reduce(function(acc, category) {
      acc[category.id] = category.title
      return acc
    }, {})
  } catch (e) {
    console.warn('Failed to parse categories data:', e)
  }
}

if (typeof coupolic !== 'undefined' && coupolic.brands) {
  try {
    const parsedBrands = JSON.parse(coupolic.brands)
    brands = parsedBrands.reduce(function(acc, brand) {
      acc[brand.id] = brand.title
      return acc
    }, {})
  } catch (e) {
    console.warn('Failed to parse brands data:', e)
  }
}

function handleFieldChange(field, value) {
  wizardState.updateFormData(field, value)
}

// Convert string values to arrays for MultiSelect
function getArrayValue(value) {
  if (Array.isArray(value)) {
    return value
  }
  if (value && typeof value === 'string') {
    return value.split(',').map(function(v) { return v.trim() }).filter(function(v) { return v })
  }
  return []
}
</script>

<template>
  <div class="step-container">
    <h2>Usage Restrictions</h2>
    <p class="step-description">Set which products, categories, or emails can use these coupons.</p>

    <form @submit.prevent="" class="coupon-form">
      <div class="form-section">
        <h3>Product Restrictions</h3>
        <MultiSelect
          :options="products"
          name="product_ids"
          label="Products"
          subtitle="Select products this coupon applies to (leave empty for all products)"
          :value="getArrayValue(wizardState.formData.product_ids)"
          @input="handleFieldChange('product_ids', $event)"
          placeholder="Select products..."
        />
        <MultiSelect
          :options="products"
          name="exclude_product_ids"
          label="Exclude Products"
          subtitle="Select products this coupon should NOT apply to"
          :value="getArrayValue(wizardState.formData.exclude_product_ids)"
          @input="handleFieldChange('exclude_product_ids', $event)"
          placeholder="Select products to exclude..."
        />
      </div>

      <div class="form-section">
        <h3>Category Restrictions</h3>
        <MultiSelect
          :options="categories"
          name="product_categories"
          label="Product Categories"
          subtitle="Select categories this coupon applies to (leave empty for all categories)"
          :value="getArrayValue(wizardState.formData.product_categories)"
          @input="handleFieldChange('product_categories', $event)"
          placeholder="Select categories..."
        />
        <MultiSelect
          :options="categories"
          name="exclude_product_categories"
          label="Exclude Categories"
          subtitle="Select categories this coupon should NOT apply to"
          :value="getArrayValue(wizardState.formData.exclude_product_categories)"
          @input="handleFieldChange('exclude_product_categories', $event)"
          placeholder="Select categories to exclude..."
        />
      </div>

      <div class="form-section">
        <h3>Brand Restrictions</h3>
        <MultiSelect
          :options="brands"
          name="product_brands"
          label="Product Brands"
          subtitle="Select brands this coupon applies to (leave empty for all brands)"
          :value="getArrayValue(wizardState.formData.product_brands)"
          @input="handleFieldChange('product_brands', $event)"
          placeholder="Select brands..."
        />
        <MultiSelect
          :options="brands"
          name="exclude_product_brands"
          label="Exclude Brands"
          subtitle="Select brands this coupon should NOT apply to"
          :value="getArrayValue(wizardState.formData.exclude_product_brands)"
          @input="handleFieldChange('exclude_product_brands', $event)"
          placeholder="Select brands to exclude..."
        />
      </div>

      <div class="form-section">
        <h3>Email Restrictions</h3>
        <Input
          type="email"
          name="customer_email"
          label="Allowed Emails"
          subtitle="Comma-separated list of allowed emails. Use * for wildcards (e.g., *@gmail.com)"
          placeholder="No restrictions"
          :value="wizardState.formData.customer_email"
          @input="handleFieldChange('customer_email', $event)"
        />
      </div>
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
  gap: 1.5rem;
}

.form-section {
  padding: 1.5rem;
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
}

.form-section h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--text-primary);
  padding-bottom: 0.75rem;
}
</style>