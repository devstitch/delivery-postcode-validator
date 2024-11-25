<?php

namespace WZC\Includes;

defined('ABSPATH') || exit;

if (!class_exists('Activator_Plugin')) {
    class Activator_Plugin
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('admin_enqueue_scripts', [$this, 'wzc_load_admin_script']);
            add_action('wp_enqueue_scripts', [$this, 'wzc_load_front_script']);
            add_action('admin_init', [$this, 'wzc_check_woocommerce_plugin_active_state']);
        }

        /**
         * Load admin scripts and styles
         */
        public function wzc_load_admin_script()
        {
            wp_enqueue_style(
                'wzc_admin_style',
                WZC_DIR_URL . 'admin/assets/css/wzc_admin.css',
                [],
                WZC_VERSION
            );
        }

        /**
         * Load frontend scripts and styles
         */
        public function wzc_load_front_script()
        {
            // Enqueue CSS
            wp_enqueue_style(
                'wzc_front_style',
                WZC_DIR_URL . 'assets/css/wzc_front_style.css',
                [],
                WZC_VERSION
            );

            // Enqueue JS
            wp_enqueue_script(
                'wzc_front_script',
                WZC_DIR_URL . 'assets/js/wzc_front_script.js',
                ['jquery'],
                WZC_VERSION,
                true
            );

            // Localize scripts
            wp_localize_script(
                'wzc_front_script',
                'wzc_ajax_postajax',
                [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'security' => wp_create_nonce('wzc_ajax_nonce')
                ]
            );

            wp_localize_script(
                'wzc_front_script',
                'wzc_plugin_url',
                ['plugin_url' => esc_url(WZC_DIR_URL)]
            );

            $not_serviceable_text = get_option(
                'wzc_not_serviceable_txt',
                esc_html__('Unfortunately we do not ship to your pincode', 'wc-zip-checker')
            );

            wp_localize_script(
                'wzc_front_script',
                'wzc_not_srvcbl_txt',
                ['not_serving' => esc_html($not_serviceable_text)]
            );
        }

        /**
         * Check WooCommerce plugin state
         */
        public function wzc_check_woocommerce_plugin_active_state()
        {
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                // Use transient with expiration
                set_transient(
                    'wzc_woo_inactive_' . get_current_user_id(),
                    'true',
                    HOUR_IN_SECONDS
                );

                // Add admin notice
                add_action('admin_notices', [$this, 'wzc_woo_inactive_notice']);
            }
        }

        /**
         * Display WooCommerce inactive notice
         */
        public function wzc_woo_inactive_notice()
        {
            $class = 'notice notice-error';
            $message = esc_html__(
                'WC Zip Checker requires WooCommerce to be installed and active.',
                'wc-zip-checker'
            );

            printf(
                '<div class="%1$s"><p>%2$s</p></div>',
                esc_attr($class),
                wp_kses($message, ['a' => ['href' => [], 'target' => []]])
            );
        }

        /**
         * Create plugin tables on activation
         */
        public function wzc_create_table_store_pincode()
        {
            global $wpdb;

            // Set table name
            $tablename = $wpdb->prefix . 'wzc_postcode';

            // Check if table exists using wp_cache
            $table_exists = wp_cache_get('wzc_table_exists');

            if (false === $table_exists) {
                $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($tablename));
                $table_exists = $wpdb->get_var($query) === $tablename;
                wp_cache_set('wzc_table_exists', $table_exists);
            }

            if (!$table_exists) {
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';

                // Get WordPress charset and collate
                $charset_collate = $wpdb->get_charset_collate();

                // Create table SQL
                $sql = "CREATE TABLE IF NOT EXISTS {$tablename} (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    wpcc_pincode varchar(20) NOT NULL,
                    wpcc_days varchar(100) NOT NULL,
                    wpcc_cod varchar(10) NOT NULL DEFAULT '0',
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY pincode_idx (wpcc_pincode)
                ) {$charset_collate};";

                // Use dbDelta for better table creation/updating
                dbDelta($sql);

                // Clear the cache after table creation
                wp_cache_delete('wzc_table_exists');

                // Set installation flag
                update_option('wzc_db_version', WZC_VERSION);

                // Trigger installation hook
                do_action('wzc_installed');
            }
        }

        /**
         * Check if plugin table exists
         * 
         * @return bool
         */
        public function wzc_table_exists()
        {
            $table_exists = wp_cache_get('wzc_table_exists');

            if (false === $table_exists) {
                global $wpdb;
                $tablename = $wpdb->prefix . 'wzc_postcode';
                $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($tablename));
                $table_exists = $wpdb->get_var($query) === $tablename;
                wp_cache_set('wzc_table_exists', $table_exists);
            }

            return $table_exists;
        }

        /**
         * Clear plugin cache
         */
        public function wzc_clear_cache()
        {
            wp_cache_delete('wzc_table_exists');
            wp_cache_delete('wzc_settings');
            do_action('wzc_cache_cleared');
        }
    }
}
