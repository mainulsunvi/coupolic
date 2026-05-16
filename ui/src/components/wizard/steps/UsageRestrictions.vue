<script setup>
import { inject } from 'vue'
import Input from '@/components/fields/Input.vue'
import Select from '@/components/fields/Select.vue'
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
</script>

<template>
  <div class="step-container">
    <h2>Usage Restrictions</h2>
    <p class="step-description">Set which products, categories, or emails can use these coupons.</p>

    <form @submit.prevent="" class="coupon-form">
      <div class="form-section">
        <h3>Product Restrictions</h3>
        <Select
          :options="products"
          name="product_ids"
          label="Products"
          subtitle="Select products this coupon applies to (leave empty for all products)"
          :value="wizardState.formData.value.product_ids"
          @input="handleFieldChange('product_ids', $event)"
        />
        <Select
          :options="products"
          name="exclude_product_ids"
          label="Exclude Products"
          subtitle="Select products this coupon should NOT apply to"
          :value="wizardState.formData.value.exclude_product_ids"
          @input="handleFieldChange('exclude_product_ids', $event)"
        />
      </div>

      <div class="form-section">
        <h3>Category Restrictions</h3>
        <Select
          :options="categories"
          name="product_categories"
          label="Product Categories"
          subtitle="Select categories this coupon applies to (leave empty for all categories)"
          :value="wizardState.formData.value.product_categories"
          @input="handleFieldChange('product_categories', $event)"
        />
        <Select
          :options="categories"
          name="exclude_product_categories"
          label="Exclude Categories"
          subtitle="Select categories this coupon should NOT apply to"
          :value="wizardState.formData.value.exclude_product_categories"
          @input="handleFieldChange('exclude_product_categories', $event)"
        />
      </div>

      <div class="form-section">
        <h3>Brand Restrictions</h3>
        <Select
          :options="brands"
          name="product_brands"
          label="Product Brands"
          subtitle="Select brands this coupon applies to (leave empty for all brands)"
          :value="wizardState.formData.value.product_brands"
          @input="handleFieldChange('product_brands', $event)"
        />
        <Select
          :options="brands"
          name="exclude_product_brands"
          label="Exclude Brands"
          subtitle="Select brands this coupon should NOT apply to"
          :value="wizardState.formData.value.exclude_product_brands"
          @input="handleFieldChange('exclude_product_brands', $event)"
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
          :value="wizardState.formData.value.customer_email"
          @input="handleFieldChange('customer_email', $event)"
        />
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

.coupon-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-section {
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
}

.form-section h3 {
  font-size: 16px;
  margin-bottom: 16px;
  color: #444;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 8px;
}
</style>