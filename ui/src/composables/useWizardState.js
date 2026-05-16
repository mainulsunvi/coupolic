import { provide, inject, ref, reactive, computed } from 'vue'

const WIZARD_STATE_KEY = 'wizardState'

export function useWizardState() {
  // Inject existing state or create new
  const existingState = inject(WIZARD_STATE_KEY, null)

  if (existingState) {
    return existingState
  }

  // Reactive state
  const currentStep = ref(1)
  const isGenerating = ref(false)
  const generationProgress = ref({
    current: 0,
    total: 0,
    status: '',
    message: '',
    percentage: 0,
  })

  // Form data using long-form functions
  const formData = reactive({
    // Basic settings
    discount_type: 'fixed_cart',
    coupon_amount: 0,
    description: '',
    free_shipping: false,

    // Date & shipping
    expiry_date: '',

    // Usage restrictions
    minimum_amount: '',
    maximum_amount: '',
    individual_use: false,
    exclude_sale_items: false,
    product_ids: [],
    exclude_product_ids: [],
    product_categories: [],
    exclude_product_categories: [],
    product_brands: [],
    exclude_product_brands: [],
    customer_email: '',

    // Generator options
    prefix: 'COUPON',
    character_count: 5,
    quantity: 10,

    // Usage limits
    usage_limit: 1,
    usage_limit_per_user: 0,
  })

  // User limits
  const userLimits = ref({
    daily_limit: 100,
    batch_limit: 50,
    remaining_daily: 100,
  })

  // Draft management
  const draftId = ref(null)
  const hasUnsavedChanges = ref(false)

  // Computed properties
  const canGoNext = computed(function() {
    return currentStep.value < 6
  })

  const canGoBack = computed(function() {
    return currentStep.value > 1
  })

  const isLastStep = computed(function() {
    return currentStep.value === 6
  })

  const progressPercentage = computed(function() {
    return Math.round((currentStep.value / 6) * 100)
  })

  // Methods using long-form functions
  function setCurrentStep(step) {
    if (step >= 1 && step <= 6) {
      currentStep.value = step
    }
  }

  function goToNextStep() {
    if (canGoNext.value) {
      currentStep.value++
    }
  }

  function goToPreviousStep() {
    if (canGoBack.value) {
      currentStep.value--
    }
  }

  function updateFormData(field, value) {
    if (field in formData) {
      formData[field] = value
      hasUnsavedChanges.value = true
    }
  }

  function updateMultipleFields(data) {
    Object.keys(data).forEach(function(key) {
      if (key in formData) {
        formData[key] = data[key]
      }
    })
    hasUnsavedChanges.value = true
  }

  function resetFormData() {
    Object.assign(formData, {
      discount_type: 'fixed_cart',
      coupon_amount: 0,
      description: '',
      free_shipping: false,
      expiry_date: '',
      minimum_amount: '',
      maximum_amount: '',
      individual_use: false,
      exclude_sale_items: false,
      product_ids: [],
      exclude_product_ids: [],
      product_categories: [],
      exclude_product_categories: [],
      product_brands: [],
      exclude_product_brands: [],
      customer_email: '',
      prefix: 'COUPON',
      character_count: 5,
      quantity: 10,
      usage_limit: 1,
      usage_limit_per_user: 0,
    })
    currentStep.value = 1
    hasUnsavedChanges.value = false
  }

  function setUserLimits(limits) {
    userLimits.value = limits
  }

  function setGenerating(status) {
    isGenerating.value = status
  }

  function updateGenerationProgress(progress) {
    generationProgress.value = progress
  }

  function setDraftId(id) {
    draftId.value = id
  }

  function clearUnsavedChanges() {
    hasUnsavedChanges.value = false
  }

  // Provide state for child components
  const state = {
    // State
    currentStep,
    formData,
    isGenerating,
    generationProgress,
    userLimits,
    draftId,
    hasUnsavedChanges,

    // Computed
    canGoNext,
    canGoBack,
    isLastStep,
    progressPercentage,

    // Methods
    setCurrentStep,
    goToNextStep,
    goToPreviousStep,
    updateFormData,
    updateMultipleFields,
    resetFormData,
    setUserLimits,
    setGenerating,
    updateGenerationProgress,
    setDraftId,
    clearUnsavedChanges,
  }

  provide(WIZARD_STATE_KEY, state)

  return state
}