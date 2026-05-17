# Coupolic Comprehensive Coupon Generator System Design

**Date:** 2025-01-16
**Project:** Coupolic WooCommerce Coupon Generator
**Type:** Feature Enhancement & Redesign
**Status:** Approved for Implementation

## Executive Summary

This design document outlines a comprehensive step-by-step coupon generator system for WooCommerce using Vue.js frontend and WordPress backend. The system replaces existing basic functionality with an advanced wizard-based interface, detailed logging capabilities, and robust user management features.

## System Architecture Overview

### Frontend Architecture

**Technology Stack:**
- Vue 3 with Composition API (using long-form functions only)
- Vue Router with hash history mode
- WordPress REST API and AJAX handlers
- Custom component architecture with provide/inject pattern

**Component Hierarchy:**
```
CouponWizard.vue (Master Component)
├── WizardTabs.vue (Tab Navigation)
├── WizardNavigation.vue (Back/Next/Save Buttons)
└── Steps/
    ├── BasicSettings.vue
    ├── DateShipping.vue
    ├── UsageRestrictions.vue
    ├── SpendingRules.vue
    ├── GeneratorOptions.vue
    └── ReviewGenerate.vue
```

**State Management:**
- No external state management libraries (Pinia, Vuex)
- Centralized state in master component using Vue's provide/inject
- Custom composables for reusable logic (wizard state, validation, generation)

### Backend Architecture

**WordPress Integration:**
- Custom admin menu structure with subpages
- AJAX handlers for all operations
- Custom database table for optimized logging
- Role-based access control with configurable limits

**Database Schema:**
```sql
CREATE TABLE wp_coupolic_logs (
  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  batch_id varchar(50) NOT NULL,
  user_id bigint(20) UNSIGNED NOT NULL,
  user_login varchar(100) NOT NULL,
  generation_time datetime NOT NULL,
  coupon_count int(11) NOT NULL,
  success_count int(11) NOT NULL,
  failed_count int(11) DEFAULT 0,
  status varchar(20) NOT NULL,
  settings longtext NOT NULL,
  coupon_codes longtext,
  error_message text,
  cleanup_date datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY batch_id (batch_id),
  KEY user_id (user_id),
  KEY status (status),
  KEY cleanup_date (cleanup_date)
)
```

## Wizard Flow Design

### 6-Step Wizard Structure

**Step 1: Basic Settings**
- Discount type selector (percentage, fixed cart, fixed product)
- Coupon amount input with validation
- Description textarea (optional)
- Free shipping checkbox
- Validation: Required fields, amount must be positive number

**Step 2: Date & Shipping** (Milestone Save Point)
- Expiry date picker with minimum date validation
- Free shipping checkbox with WooCommerce integration check
- Auto-save draft to WordPress after completion
- Validation: Date must be future date if specified

**Step 3: Usage Restrictions**
- Product restrictions (include/exclude with Select2 multi-select)
- Category restrictions (include/exclude with multi-select)
- Brand restrictions (include/exclude with multi-select)
- Email restrictions with wildcard support (*@gmail.com)
- Each field with proper validation and error messages

**Step 4: Spending Rules** (Milestone Save Point)
- Minimum/maximum spend amounts with decimal validation
- Individual use checkbox
- Exclude sale items checkbox
- Auto-save draft after completion
- Validation: Max must be greater than min if both specified

**Step 5: Generator Options**
- Coupon prefix input (alphanumeric validation)
- Character count (5-20 characters, default 5)
- Quantity selector (respects user role limits)
- Real-time limit display based on user role
- Validation: Within user's permitted limits

**Step 6: Review & Generate**
- Summary of all settings with section edit buttons
- Final comprehensive validation check
- Generate button with progress indicator
- Success screen with coupon list and export options
- Error handling with rollback capability

### Navigation Features

**Tab Navigation:**
- Horizontal tabs showing all 6 steps
- Visual indicators for completed/in-progress/current steps
- Click any tab to jump to that step
- Disabled tabs for steps after current step

**Button Navigation:**
- Back button (disabled on step 1)
- Next button (changes to "Generate" on step 6)
- Save Draft button (available on all steps)
- Progress indicator showing step X of 6

**Data Persistence:**
- Auto-save milestones after steps 2 and 4
- Manual draft save available on any step
- Browser localStorage backup for accidental navigation
- Draft loading functionality on wizard start

## User Restriction System

### Role-Based Access Control

**Default User Limits:**
```php
$default_limits = array(
    'administrator' => array(
        'daily_limit' => -1,              // Unlimited
        'batch_limit' => -1,              // Unlimited
        'can_modify_limits' => true
    ),
    'shop_manager' => array(
        'daily_limit' => 500,
        'batch_limit' => 100,
        'can_modify_limits' => false
    ),
    'editor' => array(
        'daily_limit' => 100,
        'batch_limit' => 50,
        'can_modify_limits' => false
    )
);
```

**Limit Enforcement:**
- Daily limit tracking per user (resets at midnight)
- Per-batch limit enforcement
- Real-time limit display in generator options step
- Super admin can modify limits via settings page

**Permission Checks:**
- `manage_woocommerce` capability required
- Additional role-based limit verification
- User-specific override capability
- Detailed logging of all restriction violations

## Coupon Generation System

### Generation Process

**Transaction-Based Approach:**
```php
function generate_with_rollback($settings) {
    $batch_id = generate_unique_batch_id();
    $generated_coupons = array();
    $rollback_ids = array();

    try {
        foreach (range(1, $settings['quantity']) as $i) {
            $coupon_id = create_single_coupon($settings, $i);

            if (is_wp_error($coupon_id)) {
                throw new Exception($coupon_id->get_error_message());
            }

            $rollback_ids[] = $coupon_id;
            $generated_coupons[] = $coupon_id;

            update_generation_progress($batch_id, $i, $settings['quantity']);
        }

        log_generation_success($batch_id, $generated_coupons, $settings);
        return array('success' => true, 'batch_id' => $batch_id);

    } catch (Exception $e) {
        foreach ($rollback_ids as $coupon_id) {
            wp_delete_post($coupon_id, true);
        }

        log_generation_failure($batch_id, $e->getMessage(), $settings);
        return array('success' => false, 'error' => $e->getMessage());
    }
}
```

**Real-Time Progress Tracking:**
- Server-sent events or polling for progress updates
- Progress stored in wp_options with expiration
- Visual progress bar with percentage and current item
- Estimated time remaining calculation

**Code Collision Handling:**
- Automatic retry with different code (up to 10 attempts)
- Unique code generation with prefix support
- Database uniqueness verification
- Fallback to longer random strings if needed

### Validation System

**Hybrid Validation Approach:**

**Soft Validation (During Wizard):**
- Warning indicators for potential issues
- Allow progression with warnings
- Visual feedback for incomplete fields
- Helpful suggestions for common mistakes

**Hard Validation (Final Step):**
- Comprehensive data validation
- Block generation if critical errors exist
- Detailed error messages with field-specific guidance
- Group related errors together

**Validation Checks:**
- Required field completeness
- Numeric value ranges and validity
- Date logical consistency
- Product/category/brand ID existence
- Email format and wildcard validity
- User limit compliance
- WooCommerce integration checks

## Logging & Batch Management

### Detailed Logging System

**Log Entry Structure:**
```javascript
{
  batch_id: "CMP_1705314523",
  user: {
    id: 1,
    login: "admin",
    role: "administrator"
  },
  generation_time: "2025-01-15 14:23:45",
  duration: "2.3 seconds",
  coupon_count: 50,
  success_count: 50,
  failed_count: 0,
  status: "completed",
  settings: {
    // Complete coupon settings used
    discount_type: "percent",
    coupon_amount: 10,
    expiry_date: "2025-12-31",
    // ... all other settings
  },
  coupon_codes: [
    {id: 123, code: "SUMMER_ABC123", link: "edit-link"},
    // ... all generated coupons
  ],
  error_message: null,
  cleanup_date: "2025-04-15"
}
```

### Logs Interface

**Logs Table Features:**
- Sortable columns (batch ID, user, date, count, status)
- Real-time search across all fields
- Date range filtering
- Status filtering (completed/failed/partial)
- User filtering
- Pagination with configurable page sizes
- Bulk actions (delete selected batches)

**Batch Actions:**
- **View Details:** Expand row to show complete settings and coupon list
- **Delete Batch:** Remove log entry and all associated coupons
- **Export Batch:** Download as CSV or JSON
- **View Coupons:** Link to WooCommerce coupon edit screen

**Detailed Batch View:**
- Complete coupon settings in readable format
- List of all generated codes with edit links
- Generation statistics (duration, success rate)
- Error details for failed/partial batches
- Individual coupon management

### Batch Deletion System

**Safe Batch Deletion:**
```php
function delete_batch_with_coupons($batch_id) {
    $log_entry = get_log_by_batch_id($batch_id);
    $coupon_data = json_decode($log_entry->coupon_codes, true);

    foreach ($coupon_data as $coupon) {
        $coupon_id = $coupon['id'];
        if (get_post_type($coupon_id) === 'shop_coupon') {
            wp_delete_post($coupon_id, true); // Force delete
        }
    }

    global $wpdb;
    $wpdb->delete('wp_coupolic_logs', array('batch_id' => $batch_id));

    return array('success' => true, 'deleted_count' => count($coupon_data));
}
```

**Deletion Safety Features:**
- Confirmation dialog before deletion
- Display count of coupons to be deleted
- Verify coupon ownership before deletion
- Log the deletion action itself

## Code Style & Conventions

### Vue.js Code Style

**Function Syntax:**
```javascript
// ❌ AVOID - Arrow functions
const validateForm = () => {
  return data.amount > 0
}

// ✅ USE - Long-form functions
function validateForm() {
  return data.amount > 0
}
```

**Object Properties:**
```javascript
// ❌ AVOID - Shorthand properties
const config = {
  apiUrl,
  nonce
}

// ✅ USE - Full property definitions
const config = {
  apiUrl: apiUrl,
  nonce: nonce
}
```

**Array Methods:**
```javascript
// ❌ AVOID - Arrow function shortcuts
items.map(item => ({ id: item.id, name: item.name }))

// ✅ USE - Full function syntax
items.map(function(item) {
  return {
    id: item.id,
    name: item.name
  }
})
```

### WordPress Code Style

**Security:**
- All AJAX requests must verify nonces
- Capability checks before operations
- Data sanitization on input
- Output escaping for display
- Prepared statements for database operations

**Error Handling:**
- WP_Error objects for failures
- Detailed error messages
- Proper HTTP status codes
- User-friendly error displays
- Comprehensive error logging

## File Structure

### Frontend Files

```
ui/src/
├── components/
│   ├── wizard/
│   │   ├── CouponWizard.vue         # Master component
│   │   ├── WizardTabs.vue           # Tab navigation
│   │   ├── WizardNavigation.vue     # Back/Next buttons
│   │   └── steps/
│   │       ├── BasicSettings.vue
│   │       ├── DateShipping.vue
│   │       ├── UsageRestrictions.vue
│   │       ├── SpendingRules.vue
│   │       ├── GeneratorOptions.vue
│   │       └── ReviewGenerate.vue
│   ├── logs/
│   │   ├── LogsView.vue             # Main logs component
│   │   ├── LogsTable.vue            # Data table
│   │   ├── LogDetails.vue           # Detail modal
│   │   └── LogsSettings.vue         # Admin configuration
│   └── shared/
│       ├── ProgressBar.vue          # Generation progress
│       └── CouponSummary.vue        # Review step summary
├── composables/
│   ├── useWizardState.js            # Wizard state management
│   ├── useValidation.js             # Validation logic
│   └── useCouponGeneration.js       # Generation API calls
├── router/
│   └── index.js                     # Updated with new routes
└── main.js                          # Entry point
```

### Backend Files

```
includes/
├── class-coupolic-admin.php         # Updated (existing)
├── class-coupolic-logs.php          # NEW - Logs management
├── class-coupolic-generator.php     # Updated (existing)
├── class-coupolic-api.php           # NEW - AJAX handlers
└── class-coupolic-install.php       # NEW - Database setup
```

## Implementation Priority

### Phase 1: Foundation (Priority 1)
- Create custom database table installation
- Setup WordPress admin menu structure
- Create base PHP classes
- Implement user restriction system

### Phase 2: Backend API (Priority 2)
- AJAX handlers for wizard operations
- Draft save/load functionality
- Generation API with progress tracking
- Logging infrastructure

### Phase 3: Frontend Foundation (Priority 3)
- Update router with new routes
- Create master wizard component
- Build navigation components
- Setup provide/inject system

### Phase 4: Wizard Steps (Priority 4)
- Build individual step components
- Implement validation logic
- Connect to backend APIs
- Milestone save functionality

### Phase 5: Generation System (Priority 5)
- Real-time generation system
- Progress tracking UI
- Rollback functionality
- Success/error handling

### Phase 6: Logs Interface (Priority 6)
- Logs viewing interface
- Batch management
- Export functionality
- Cleanup system

### Phase 7: Polish & Testing (Priority 7)
- Error handling refinement
- User experience improvements
- Security validation
- Performance optimization
- Cross-browser testing

## AJAX Endpoints

### Wizard Operations
- `coupolic_save_draft` - Save wizard progress
- `coupolic_load_draft` - Load saved draft
- `coupolic_delete_draft` - Delete saved draft

### Generation
- `coupolic_generate_coupons` - Start generation process
- `coupolic_check_progress` - Poll generation progress
- `coupolic_cancel_generation` - Cancel active generation

### Validation
- `coupolic_validate_settings` - Validate coupon settings
- `coupolic_check_user_limits` - Verify user limits

### Logs
- `coupolic_get_logs` - Fetch logs with filters
- `coupolic_get_batch_details` - Get detailed batch info
- `coupolic_delete_batch` - Delete entire batch
- `coupolic_export_batch` - Export batch data

### Settings
- `coupolic_get_settings` - Get system settings
- `coupolic_save_settings` - Save system settings
- `coupolic_get_user_limits` - Get user-specific limits

## Security Considerations

### Frontend Security
- All AJAX requests include nonce verification
- Input sanitization on all form fields
- Output escaping for displayed data
- XSS prevention in templates

### Backend Security
- Capability checks before operations
- Rate limiting on generation requests
- Input validation and sanitization
- Prepared SQL statements
- Audit logging for sensitive operations

### User Restrictions
- Role-based access control
- Per-user generation limits
- Daily quota enforcement
- Audit trail for all operations

## Performance Considerations

### Database Optimization
- Indexed columns for common queries
- Efficient pagination for large datasets
- Automatic cleanup of old logs
- Optimized SQL queries

### Frontend Performance
- Lazy loading of step components
- Debounced validation inputs
- Efficient reactivity with proper computed properties
- Code splitting for production builds

### Generation Performance
- Batch processing for large quantities
- Progress updates without blocking
- Efficient code generation algorithms
- Database transaction optimization

## Testing Strategy

### Unit Testing
- Vue component testing with Vitest
- PHP unit testing with PHPUnit
- Validation function testing
- Utility function testing

### Integration Testing
- AJAX endpoint testing
- WordPress integration testing
- Database operation testing
- User restriction testing

### User Acceptance Testing
- Complete wizard flow testing
- Error scenario testing
- Cross-browser compatibility
- Mobile responsiveness testing

## Success Criteria

### Functional Requirements
- ✅ 6-step wizard with all specified fields
- ✅ Role-based user restrictions with configurable limits
- ✅ Transaction-based generation with rollback capability
- ✅ Detailed logging with custom database table
- ✅ Batch management and deletion functionality
- ✅ Real-time progress tracking during generation
- ✅ Hybrid validation approach (warnings + hard validation)
- ✅ Long-form function syntax throughout codebase

### Non-Functional Requirements
- ✅ Responsive design for all screen sizes
- ✅ Cross-browser compatibility (Chrome, Firefox, Safari, Edge)
- ✅ Accessibility compliance (WCAG 2.1 AA)
- ✅ Performance: Generate 100 coupons in < 10 seconds
- ✅ Security: No XSS, CSRF, or SQL injection vulnerabilities
- ✅ Usability: Intuitive navigation with clear feedback

## Conclusion

This design provides a comprehensive, user-friendly coupon generator system that balances powerful functionality with ease of use. The wizard-based interface guides users through complex configuration options while maintaining flexibility for experienced users. The detailed logging system provides complete audit trails and batch management capabilities.

The hybrid validation approach ensures data quality without being overly restrictive, and the transaction-based generation system with rollback capability ensures data integrity. The role-based access control with configurable limits provides security while allowing administrative flexibility.

All code will follow long-form function conventions as specified, and the modular architecture will facilitate future enhancements and maintenance.

---

**Next Steps:** Proceed to implementation planning phase.
