import { ref, computed } from 'vue'

export function useValidation() {
  const errors = ref({})
  const warnings = ref({})

  // Validation rules for each field
  const validationRules = {
    discount_type: {
      required: true,
      validate: function(value) {
        const validTypes = ['percent', 'fixed_cart', 'fixed_product']
        return validTypes.includes(value)
      },
      message: 'Please select a valid discount type',
    },
    coupon_amount: {
      required: true,
      validate: function(value) {
        const numValue = parseFloat(value)
        return !isNaN(numValue) && numValue > 0
      },
      message: 'Coupon amount must be greater than 0',
    },
    quantity: {
      required: true,
      validate: function(value) {
        return !isNaN(value) && parseInt(value) > 0
      },
      message: 'Quantity must be greater than 0',
    },
    prefix: {
      required: false,
      validate: function(value) {
        return value === '' || /^[A-Z0-9_-]+$/i.test(value)
      },
      message: 'Prefix can only contain letters, numbers, hyphens, and underscores',
    },
    character_count: {
      required: true,
      validate: function(value) {
        const count = parseInt(value)
        return !isNaN(count) && count >= 5 && count <= 20
      },
      message: 'Character count must be between 5 and 20',
    },
    expiry_date: {
      required: false,
      validate: function(value) {
        if (value === '') return true
        const selectedDate = new Date(value)
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        return selectedDate >= today
      },
      message: 'Expiry date must be today or in the future',
    },
    minimum_amount: {
      required: false,
      validate: function(value) {
        return value === '' || (!isNaN(value) && parseFloat(value) >= 0)
      },
      message: 'Minimum amount must be 0 or greater',
    },
    maximum_amount: {
      required: false,
      validate: function(value, formData) {
        if (value === '') return true
        if (isNaN(value) || parseFloat(value) <= 0) return false
        if (formData.minimum_amount && formData.minimum_amount !== '') {
          return parseFloat(value) > parseFloat(formData.minimum_amount)
        }
        return true
      },
      message: 'Maximum amount must be greater than minimum amount',
    },
  }

  // Validate single field
  function validateField(fieldName, value, formData = {}) {
    const rule = validationRules[fieldName]
    if (!rule) return true

    // Check required fields
    if (rule.required && (value === '' || value === null || value === undefined)) {
      errors.value[fieldName] = rule.message
      return false
    }

    // Skip validation for empty optional fields
    if (!rule.required && (value === '' || value === null || value === undefined)) {
      delete errors.value[fieldName]
      return true
    }

    // Run validation rule
    const isValid = rule.validate(value, formData)
    if (!isValid) {
      errors.value[fieldName] = rule.message
      return false
    }

    delete errors.value[fieldName]
    return true
  }

  // Validate all form data
  function validateAll(formData) {
    let isValid = true
    Object.keys(validationRules).forEach(function(fieldName) {
      const fieldValid = validateField(fieldName, formData[fieldName], formData)
      if (!fieldValid) {
        isValid = false
      }
    })
    return isValid
  }

  // Validate specific step
  function validateStep(stepNumber, formData) {
    const stepFields = {
      1: ['discount_type', 'coupon_amount'],
      2: ['expiry_date'],
      3: [], // Usage restrictions - all optional
      4: ['minimum_amount', 'maximum_amount'],
      5: ['prefix', 'character_count', 'quantity'],
      6: [], // Review step - no validation
    }

    const fields = stepFields[stepNumber] || []
    let isValid = true

    fields.forEach(function(fieldName) {
      const fieldValid = validateField(fieldName, formData[fieldName], formData)
      if (!fieldValid) {
        isValid = false
      }
    })

    return isValid
  }

  // Check for warnings (soft validation)
  function checkWarnings(formData) {
    const foundWarnings = {}

    // Warn if no expiry date set
    if (!formData.expiry_date || formData.expiry_date === '') {
      foundWarnings.expiry_date = 'Consider setting an expiry date for better security'
    }

    // Warn if no usage restrictions
    if ((!formData.minimum_amount || formData.minimum_amount === '') &&
        (!formData.product_ids || formData.product_ids.length === 0) &&
        (!formData.category_ids || formData.category_ids.length === 0)) {
      foundWarnings.restrictions = 'Consider adding usage restrictions for better control'
    }

    // Warn about large quantities
    if (formData.quantity && formData.quantity > 50) {
      foundWarnings.quantity = 'Large batch sizes may take longer to generate'
    }

    // Warn about high discount amounts
    if (formData.discount_type === 'percent' && formData.coupon_amount > 50) {
      foundWarnings.coupon_amount = 'High percentage discounts may impact profitability'
    }

    warnings.value = foundWarnings
    return foundWarnings
  }

  // Check warnings for specific field
  function checkFieldWarning(fieldName, formData) {
    checkWarnings(formData)
    return warnings.value[fieldName] || ''
  }

  // Clear all errors
  function clearErrors() {
    errors.value = {}
  }

  // Clear specific field error
  function clearFieldError(fieldName) {
    delete errors.value[fieldName]
  }

  // Computed properties
  const hasErrors = computed(function() {
    return Object.keys(errors.value).length > 0
  })

  const hasWarnings = computed(function() {
    return Object.keys(warnings.value).length > 0
  })

  const errorCount = computed(function() {
    return Object.keys(errors.value).length
  })

  return {
    // State
    errors,
    warnings,

    // Computed
    hasErrors,
    hasWarnings,
    errorCount,

    // Methods
    validateField,
    validateAll,
    validateStep,
    checkWarnings,
    checkFieldWarning,
    clearErrors,
    clearFieldError,
  }
}