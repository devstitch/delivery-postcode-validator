<?php

namespace WZC\Admin;

defined('ABSPATH') || exit;

class WZC_Admin
{
    private $tablename;

    public function __construct()
    {
        global $wpdb;
        $this->tablename = $wpdb->prefix . 'wzc_postcode';

        add_action('admin_menu', [$this, 'register_wzc_menu']);
        add_action('admin_init', [$this, 'handle_form_submissions']);
    }

    /**
     * Register admin menu and submenu pages
     */
    public function register_wzc_menu()
    {
        $icon = $this->get_menu_icon();

        add_menu_page(
            'Post/Zip Code Checker',
            'Postcode Validator',
            'manage_options',
            'ccpin-code',
            [$this, 'wzc_dashboard'],
            $icon,
            80
        );

        $submenu_pages = [
            [
                'parent_slug' => 'ccpin-code',
                'page_title' => 'Dashboard',
                'menu_title' => 'Dashboard',
                'capability' => 'manage_options',
                'menu_slug' => 'ccpin-code',
                'callback' => [$this, 'wzc_dashboard']
            ],
            [
                'parent_slug' => 'ccpin-code',
                'page_title' => 'All Post/Zip Code',
                'menu_title' => 'All Post/Zip Code',
                'capability' => 'manage_options',
                'menu_slug' => 'view-pin-code',
                'callback' => [$this, 'wzc_list_postcode']
            ],
            [
                'parent_slug' => 'ccpin-code',
                'page_title' => __('Add New Post/Zip Code', 'wc-zip-checker'),
                'menu_title' => __('Add New', 'wc-zip-checker'),
                'capability' => 'manage_options',
                'menu_slug' => 'add-pin-code',
                'callback' => [$this, 'wzc_add_postcode']
            ],
            [
                'parent_slug' => 'ccpin-code',
                'page_title' => __('Settings', 'wc-zip-checker'),
                'menu_title' => __('Settings', 'wc-zip-checker'),
                'capability' => 'manage_options',
                'menu_slug' => 'pin-code-setting',
                'callback' => [$this, 'wzc_setting']
            ],
            [
                'parent_slug' => 'ccpin-code',
                'page_title' => __('Import Post/Zip Code', 'wc-zip-checker'),
                'menu_title' => __('Import Post/Zip Code', 'wc-zip-checker'),
                'capability' => 'manage_options',
                'menu_slug' => 'pin-code-import',
                'callback' => [$this, 'wzc_import_postcode']
            ]
        ];

        foreach ($submenu_pages as $page) {
            add_submenu_page(
                $page['parent_slug'],
                $page['page_title'],
                $page['menu_title'],
                $page['capability'],
                $page['menu_slug'],
                $page['callback']
            );
        }
    }

    /**
     * Get SVG menu icon
     */
    private function get_menu_icon()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z" fill="currentColor"/>
            <path d="M12 6c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zm0 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z" fill="currentColor"/>
        </svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Load view files
     */
    public function wzc_dashboard()
    {
        $this->load_view('class-wzc-dashboard.php');
    }

    public function wzc_add_postcode()
    {
        $this->load_view('class-add-edit-pincode.php');
    }

    public function wzc_list_postcode()
    {
        $this->load_view('render-postcode-list.php');
    }

    public function wzc_import_postcode()
    {
        $this->load_view('import-pincodes.php');
    }

    public function wzc_setting()
    {
        $this->load_view('setting.php');
    }

    /**
     * Helper method to load views
     */
    private function load_view($file)
    {
        $file_path = WZC_DIR_PATH . 'admin/partials/' . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    /**
     * Handle all form submissions
     */
    public function handle_form_submissions()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';

        switch ($action) {
            case 'wzc_settings_save':
                $this->handle_settings_save();
                break;

            case 'wzc_add_postcode':
                $this->handle_add_postcode();
                break;

            case 'wzc_update_postcode':
                $this->handle_update_postcode();
                break;

            case 'wzc_import_postcodes':
                $this->handle_import_postcodes();
                break;

            case 'wzc_delete':
                $this->handle_delete_postcode();
                break;
        }

        if (isset($_REQUEST['all_record_delete'])) {
            $this->handle_delete_all_postcodes();
        }
    }

    /**
     * Handle settings save
     */
    private function handle_settings_save()
    {
        if (!$this->verify_nonce('wzc_nonce_field', 'wzc_nonce_action')) {
            return;
        }

        include WZC_DIR_PATH . 'admin/partials/form_fields.php';

        foreach ($input_fileds as $field) {
            if (!isset($field['type'])) {
                continue;
            }

            $field_name = $field['name'];

            switch ($field['type']) {
                case 'checkbox':
                    $value = isset($_REQUEST[$field_name]) ? 'on' : 'off';
                    break;

                case 'text':
                case 'dropdown':
                    $value = isset($_REQUEST[$field_name]) ? sanitize_text_field($_REQUEST[$field_name]) : '';
                    break;

                default:
                    continue 2;
            }

            update_option($field_name, $value);
        }
    }

    /**
     * Handle adding new postcode
     */
    private function handle_add_postcode()
    {
        if (!$this->verify_nonce('wzc_add_postcode_field', 'wzc_add_postcode_action')) {
            return;
        }

        global $wpdb;

        $pincode = sanitize_text_field($_POST['txtpincode']);
        $ddate = sanitize_text_field($_POST['txtdelivery']);
        $cod = isset($_POST['txtcod']) && $_POST['txtcod'] !== '' ? sanitize_text_field($_POST['txtcod']) : '0';

        if (empty($pincode) || empty($ddate)) {
            return;
        }

        // Check if pincode exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->tablename} WHERE wpcc_pincode = %d",
            $pincode
        ));

        if ($exists) {
            wp_redirect(admin_url("admin.php?page=add-pin-code&add=exists&txtpincode={$pincode}&txtdelivery={$ddate}&txtcod={$cod}"));
            exit;
        }

        $wpdb->insert($this->tablename, [
            'wpcc_pincode' => $pincode,
            'wpcc_days' => $ddate,
            'wpcc_cod' => $cod
        ]);

        wp_redirect(admin_url('admin.php?page=add-pin-code&add=success'));
        exit;
    }

    /**
     * Handle updating postcode
     */
    private function handle_update_postcode()
    {
        if (!$this->verify_nonce('wzc_update_postcode_field', 'wzc_update_postcode_action')) {
            return;
        }

        global $wpdb;

        $id = sanitize_text_field($_REQUEST['txtid']);
        $pincode = sanitize_text_field($_REQUEST['txtpincode']);
        $ddate = sanitize_text_field($_REQUEST['txtdelivery']);
        $cod = isset($_POST['txtcod']) && $_POST['txtcod'] !== '' ? sanitize_text_field($_POST['txtcod']) : '0';

        if (empty($pincode) || empty($ddate)) {
            return;
        }

        // Check if new pincode exists for different ID
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->tablename} WHERE wpcc_pincode = %d AND id != %d",
            $pincode,
            $id
        ));

        if ($exists) {
            wp_redirect(admin_url("admin.php?page=add-pin-code&action=edit_pincode&id={$id}&update=exists"));
            exit;
        }

        $wpdb->update(
            $this->tablename,
            [
                'wpcc_pincode' => $pincode,
                'wpcc_days' => $ddate,
                'wpcc_cod' => $cod
            ],
            ['id' => $id]
        );

        wp_redirect(admin_url("admin.php?page=add-pin-code&action=edit_pincode&id={$id}&update=success"));
        exit;
    }

    /**
     * Handle importing postcodes
     */
    private function handle_import_postcodes()
    {
        if (!$this->verify_nonce('wzc_import_postcodes_field', 'wzc_import_postcodes_action') || !isset($_POST['butimport'])) {
            return;
        }

        if (empty($_FILES['import_file']['name']) || pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION) !== 'csv') {
            wp_redirect(admin_url('admin.php?page=pin-code-import&import=error'));
            exit;
        }

        $totalInserted = $this->process_csv_import($_FILES['import_file']['tmp_name']);

        wp_redirect(admin_url("admin.php?page=pin-code-import&import=success&records={$totalInserted}"));
        exit;
    }

    /**
     * Process CSV import
     */
    private function process_csv_import($file)
    {
        global $wpdb;
        $totalInserted = 0;

        if (($handle = fopen($file, 'r')) !== FALSE) {
            // Skip header row
            fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== FALSE) {
                $data = array_map(function ($value) {
                    return mb_convert_encoding(trim($value), 'UTF-8', 'ISO-8859-1');
                }, $data);

                if (empty($data[0])) {
                    continue;
                }

                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->tablename} WHERE wpcc_pincode = %s",
                    $data[0]
                ));

                if ($exists) {
                    continue;
                }

                $result = $wpdb->insert($this->tablename, [
                    'wpcc_pincode' => $data[0],
                    'wpcc_days' => $data[1],
                    'wpcc_cod' => $data[2] ?? '0'
                ]);

                if ($result) {
                    $totalInserted++;
                }
            }
            fclose($handle);
        }

        return $totalInserted;
    }

    /**
     * Handle deleting single postcode
     */
    private function handle_delete_postcode()
    {
        if (!$this->verify_nonce('_wpnonce', 'my_nonce')) {
            return;
        }

        global $wpdb;

        $id = sanitize_text_field($_REQUEST['id']);

        $wpdb->delete($this->tablename, ['id' => $id]);

        wp_redirect(admin_url('admin.php?page=view-pin-code&delete=success&_wpnonce=deletepin'));
        exit;
    }

    /**
     * Handle deleting all postcodes
     */
    private function handle_delete_all_postcodes()
    {
        if (!$this->verify_nonce('wzc_delete_postcode_field', 'wzc_delete_postcode_action')) {
            return;
        }

        global $wpdb;

        $wpdb->query("DELETE FROM {$this->tablename}");

        wp_redirect(admin_url('admin.php?page=view-pin-code&delete=success&_wpnonce=deleteallpin'));
        exit;
    }

    /**
     * Verify nonce
     */
    private function verify_nonce($field, $action)
    {
        if (!isset($_REQUEST[$field]) || !wp_verify_nonce($_REQUEST[$field], $action)) {
            wp_die('Sorry, your nonce did not verify.');
            return false;
        }
        return true;
    }
}
