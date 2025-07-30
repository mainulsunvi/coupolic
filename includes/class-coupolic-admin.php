<?php
/**
 * Admin functionality for Coupolic
 *
 * @package Coupolic
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Admin class
 */
class Coupolic_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_coupolic_generate_coupons', array( $this, 'ajax_generate_coupons' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            esc_html__( 'Coupolic', 'coupolic' ),
            esc_html__( 'Coupolic', 'coupolic' ),
            'manage_options',
            'coupolic',
            array( $this, 'render_admin_page' ),
            'dashicons-tickets-alt',
            58
        );
        // add_submenu_page(
        //     'coupolic',
        //     esc_html__( 'Bulk Coupon Generator', 'coupolic' ),
        //     esc_html__( 'Bulk Coupons', 'coupolic' ),
        //     'manage_woocommerce',
        //     'coupolic-bulk-generator',
        //     array( $this, 'render_admin_page' )
        // );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_coupolic' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'coupolic-admin',
            COUPOLIC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            COUPOLIC_VERSION
        );

        wp_enqueue_script(
            'coupolic-admin',
            COUPOLIC_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            COUPOLIC_VERSION,
            true
        );

        wp_localize_script( 'coupolic-admin', 'coupolic', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'coupolic_generate_nonce' ),
            'messages' => array(
                'generating' => esc_html__( 'Generating coupons...', 'coupolic' ),
                'success'    => esc_html__( 'Coupons generated successfully!', 'coupolic' ),
                'error'      => esc_html__( 'An error occurred. Please try again.', 'coupolic' ),
            ),
        ) );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap coupolic-wrap">
            <h1><?php esc_html_e( 'Coupolic: Bulk Coupon Generator', 'coupolic' ); ?></h1>
            
            <div class="coupolic-form-wrapper">
                <form id="coupolic-generator-form" method="post">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="coupon_prefix"><?php esc_html_e( 'Coupon Prefix', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="coupon_prefix" name="coupon_prefix" class="regular-text" placeholder="DISCOUNT" />
                                <p class="description"><?php esc_html_e( 'Prefix for generated coupon codes (e.g., DISCOUNT_XXXXX)', 'coupolic' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="coupon_quantity"><?php esc_html_e( 'Number of Characters', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <input type="number" id="coupon_quantity" name="character_count" value="5" class="small-text" />
                                <p class="description"><?php esc_html_e( 'Number of characters in each coupon code (Default: 5)', 'coupolic' ); ?></p>
                            </td>
                        </tr> 
                        
                        <tr>
                            <th scope="row">
                                <label for="coupon_quantity"><?php esc_html_e( 'Number of Coupons', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <input type="number" id="coupon_quantity" name="coupon_quantity" min="1" max="100" value="10" class="small-text" />
                                <p class="description"><?php esc_html_e( 'Number of coupons to generate (max 100)', 'coupolic' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="discount_type"><?php esc_html_e( 'Discount Type', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <select id="discount_type" name="discount_type">
                                    <option value="fixed_cart"><?php esc_html_e( 'Fixed cart discount', 'coupolic' ); ?></option>
                                    <option value="percent"><?php esc_html_e( 'Percentage discount', 'coupolic' ); ?></option>
                                    <option value="fixed_product"><?php esc_html_e( 'Fixed product discount', 'coupolic' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="coupon_amount"><?php esc_html_e( 'Coupon Amount', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <input type="number" id="coupon_amount" name="coupon_amount" min="0" step="0.01" value="10" class="small-text" />
                                <p class="description"><?php esc_html_e( 'The amount or percentage of the discount', 'coupolic' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="usage_limit"><?php esc_html_e( 'Usage Limit per Coupon', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <input type="number" id="usage_limit" name="usage_limit" min="0" value="1" class="small-text" />
                                <p class="description"><?php esc_html_e( 'How many times each coupon can be used (0 = unlimited)', 'coupolic' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="expiry_date"><?php esc_html_e( 'Expiry Date', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <input type="date" id="expiry_date" name="expiry_date" />
                                <p class="description"><?php esc_html_e( 'Leave empty for no expiry', 'coupolic' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="minimum_amount"><?php esc_html_e( 'Minimum Spend', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <input type="number" id="minimum_amount" name="minimum_amount" min="0" step="0.01" class="small-text" />
                                <p class="description"><?php esc_html_e( 'Minimum spend required to use the coupon', 'coupolic' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="individual_use"><?php esc_html_e( 'Individual Use', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="individual_use" name="individual_use" value="yes" />
                                    <?php esc_html_e( 'Check this box if the coupon cannot be used in conjunction with other coupons', 'coupolic' ); ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="coupon_description"><?php esc_html_e( 'Coupon Description', 'coupolic' ); ?></label>
                            </th>
                            <td>
                                <textarea id="coupon_description" name="coupon_description" rows="4" class="large-text"></textarea>
                                <p class="description"><?php esc_html_e( 'Enter a description for the coupon', 'coupolic' ); ?></p>
                            </td>
                        </tr>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="generate-coupons">
                            <?php esc_html_e( 'Generate Coupons', 'coupolic' ); ?>
                        </button>
                    </p>
                </form>
                
                <div id="coupolic-message" style="display:none;"></div>
                
                <div id="coupolic-results" style="display:none;">
                    <h2><?php esc_html_e( 'Generated Coupons', 'coupolic' ); ?></h2>
                    <div id="coupolic-coupon-list"></div>
                    <p>
                        <button type="button" class="button" id="export-coupons">
                            <?php esc_html_e( 'Export to CSV', 'coupolic' ); ?>
                        </button>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for generating coupons
     */
    public function ajax_generate_coupons() {
        // Verify nonce
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'coupolic_generate_nonce' ) ) {
            wp_send_json_error( esc_html__( 'Security check failed', 'coupolic' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( esc_html__( 'Insufficient permissions', 'coupolic' ) );
        }

         if ( !current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( esc_html__( 'You do not have permission to generate coupons.', 'coupolic' ) );
        }

        // Get form data
        $data = array(
            'prefix'         => sanitize_text_field( $_POST['coupon_prefix'] ?? 'COUPON' ),
            'quantity'       => absint( $_POST['coupon_quantity'] ?? 10 ),
            'character_count' => absint( $_POST['character_count'] ?? 5 ),
            'discount_type'  => sanitize_text_field( $_POST['discount_type'] ?? 'fixed_cart' ),
            'amount'         => floatval( $_POST['coupon_amount'] ?? 10 ),
            'usage_limit'    => absint( $_POST['usage_limit'] ?? 1 ),
            'expiry_date'    => sanitize_text_field( $_POST['expiry_date'] ?? '' ),
            'minimum_amount' => floatval( $_POST['minimum_amount'] ?? 0 ),
            'individual_use' => isset( $_POST['individual_use'] ) && $_POST['individual_use'] === 'yes',
            'description'     => sanitize_textarea_field( $_POST['coupon_description'] ?? '' ),
        );

        // Validate quantity
        if ( $data['quantity'] > 100 ) {
            $data['quantity'] = 100;
        }

        if ( $data['character_count'] < 1 ) {
            $data['character_count'] = 5;
        }

        // Generate coupons
        $generator = new Coupolic_Generator();
        $result = $generator->generate_bulk_coupons( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( $result );
    }
}