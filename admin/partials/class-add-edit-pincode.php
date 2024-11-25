<?php

namespace WZC\Admin;

defined('ABSPATH') || exit;

class WCZC_Add_Edit_Pincode
{
    private $wpdb;
    private $table_name;
    private $messages = [
        'exists' => [
            'type' => 'error',
            'text' => 'Sorry, pincode already exists in records.'
        ],
        'success' => [
            'type' => 'success',
            'text' => 'Operation completed successfully.'
        ],
        'error' => [
            'type' => 'error',
            'text' => 'An error occurred. Please try again.'
        ],
        'invalid_pincode' => [
            'type' => 'error',
            'text' => 'Please enter a valid 5-digit pincode.'
        ],
        'invalid_nonce' => [
            'type' => 'error',
            'text' => 'Security check failed. Please try again.'
        ]
    ];

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'wzc_postcode';
        add_action('wp_ajax_validate_pincode', [$this, 'ajax_validate_pincode']);
    }

    public function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wc-zip-checker'));
        }

        wp_enqueue_style('wzc-admin-styles', plugins_url('css/admin-styles.css', __FILE__));
        wp_enqueue_script('wzc-admin-script', plugins_url('js/admin-script.js', __FILE__), ['jquery'], '1.0.0', true);
        wp_localize_script('wzc-admin-script', 'wzcAdmin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wzc_admin_nonce')
        ]);
?>
        <div class="wrap wzc-admin-wrap">
            <div class="wzc-admin-container">
                <?php
                if ($this->is_edit_mode()) {
                    $this->render_edit_form();
                } else {
                    $this->render_add_form();
                }
                ?>
            </div>
        </div>
    <?php
        $this->render_styles();
    }

    private function is_edit_mode()
    {
        if (!isset($_REQUEST['action'], $_REQUEST['id'])) {
            return false;
        }

        return sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit_pincode' &&
            absint($_REQUEST['id']) > 0;
    }

    private function render_edit_form()
    {
        $pincode_id = isset($_REQUEST['id']) ? absint($_REQUEST['id']) : 0;
        $record = $this->get_pincode_record($pincode_id);

        if (!$record) {
            $this->show_message('error');
            return;
        }

        $this->show_admin_notices();
    ?>
        <h2><?php esc_html_e('Update Post/Zip Code', 'wc-zip-checker'); ?></h2>
        <form method="post" class="wzc-admin-form" id="wzc-edit-form">
            <?php wp_nonce_field('wzc_update_postcode_action', 'wzc_update_postcode_field'); ?>
            <?php $this->render_form_fields($record); ?>
            <input type="hidden" name="action" value="wzc_update_postcode">
            <input type="hidden" name="txtid" value="<?php echo esc_attr($record->id); ?>">
            <div class="wzc-form-actions">
                <input type="submit" name="wzc_update_postcode" value="<?php esc_attr_e('Update', 'wc-zip-checker'); ?>" class="wzc-submit-button">
                <a href="<?php echo esc_url(admin_url('admin.php?page=view-pin-code')); ?>" class="button"><?php esc_html_e('Cancel', 'wc-zip-checker'); ?></a>
            </div>
        </form>
    <?php
    }

    private function render_add_form()
    {
        $this->show_admin_notices();
    ?>
        <h2><?php esc_html_e('Add New Post/Zip Code', 'wc-zip-checker'); ?></h2>
        <form method="post" class="wzc-admin-form" id="wzc-add-form">
            <?php wp_nonce_field('wzc_add_postcode_action', 'wzc_add_postcode_field'); ?>
            <?php $this->render_form_fields(); ?>
            <input type="hidden" name="action" value="wzc_add_postcode">
            <div class="wzc-form-actions">
                <input type="submit" name="wzc_add_postcode" value="<?php esc_attr_e('Add', 'wc-zip-checker'); ?>" class="wzc-submit-button">
                <a href="<?php echo esc_url(admin_url('admin.php?page=view-pin-code')); ?>" class="button"><?php esc_html_e('Cancel', 'wc-zip-checker'); ?></a>
            </div>
        </form>
    <?php
    }

    private function render_form_fields($record = null)
    {
        $pincode = $record ? $record->wpcc_pincode : (isset($_GET['txtpincode']) ? sanitize_text_field(wp_unslash($_GET['txtpincode'])) : '');

        $delivery_days = $record ? $record->wpcc_days : (isset($_GET['txtdelivery']) ? absint(wp_unslash($_GET['txtdelivery'])) : 0);

        $cod_enabled = $record ? $record->wpcc_cod == '1' : (isset($_GET['txtcod']) && sanitize_text_field(wp_unslash($_GET['txtcod'])) == '1');
    ?>
        <table class="wzc-form-table">
            <tr>
                <td><label for="txtpincode"><?php esc_html_e('Pincode *', 'wc-zip-checker'); ?></label></td>
                <td>
                    <input type="text"
                        id="txtpincode"
                        name="txtpincode"
                        value="<?php echo esc_attr($pincode); ?>"
                        required
                        class="wzc-form-input"
                        pattern="[0-9]{5}"
                        maxlength="5"
                        title="<?php esc_attr_e('Please enter a valid 5-digit pincode', 'wc-zip-checker'); ?>">
                    <div class="wzc-validation-message"></div>
                </td>
            </tr>

            <tr>
                <td><label for="txtdelivery"><?php esc_html_e('Delivery within days *', 'wc-zip-checker'); ?></label></td>
                <td>
                    <input type="number"
                        id="txtdelivery"
                        name="txtdelivery"
                        min="0"
                        max="30"
                        value="<?php echo esc_attr($delivery_days); ?>"
                        required
                        class="wzc-form-input">
                    <p class="wzc-form-note">
                        <?php esc_html_e('Note: Enter 0 for same-day delivery. Maximum allowed: 30 days.', 'wc-zip-checker'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <td><label for="codstatus"><?php esc_html_e('Cash on Delivery', 'wc-zip-checker'); ?></label></td>
                <td>
                    <div class="wzc-toggle-container">
                        <label class="wzc-toggle">
                            <input type="checkbox"
                                id="codstatus"
                                name="txtcod"
                                value="1"
                                <?php checked($cod_enabled); ?>>
                            <span class="wzc-toggle-slider"></span>
                        </label>
                        <span class="wzc-toggle-label">
                            <?php echo $cod_enabled ? esc_html__('Enabled', 'wc-zip-checker') : esc_html__('Disabled', 'wc-zip-checker'); ?>
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    <?php
    }

    public function ajax_validate_pincode()
    {
        check_ajax_referer('wzc_admin_nonce', 'nonce');

        if (!isset($_POST['pincode'])) {
            wp_send_json_error(['message' => $this->messages['invalid_pincode']['text']]);
        }

        $pincode = sanitize_text_field(wp_unslash($_POST['pincode']));
        $current_id = isset($_POST['current_id']) ? absint(wp_unslash($_POST['current_id'])) : 0;

        if (!preg_match('/^[0-9]{5}$/', $pincode)) {
            wp_send_json_error(['message' => $this->messages['invalid_pincode']['text']]);
        }

        $exists = $this->check_pincode_exists($pincode, $current_id);

        if ($exists) {
            wp_send_json_error(['message' => $this->messages['exists']['text']]);
        }

        wp_send_json_success(['message' => esc_html__('Pincode is valid', 'wc-zip-checker')]);
    }

    private function check_pincode_exists($pincode, $exclude_id = 0)
    {
        global $wpdb;
        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}wzc_postcode WHERE wpcc_pincode = %s AND id != %d",
                $pincode,
                $exclude_id
            )
        );
    }

    private function get_pincode_record($id)
    {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wzc_postcode WHERE id = %d",
                $id
            )
        );
    }

    private function show_admin_notices()
    {
        $status = '';

        if (isset($_GET['add'])) {
            $status = sanitize_text_field(wp_unslash($_GET['add']));
        } elseif (isset($_GET['update'])) {
            $status = sanitize_text_field(wp_unslash($_GET['update']));
        }

        if ($status && isset($this->messages[$status])) {
            $this->show_message($status);
        }
    }

    private function show_message($status)
    {
        if (!isset($this->messages[$status])) {
            return;
        }

        $message = $this->messages[$status];
        printf(
            '<div class="wzc-notice wzc-notice-%s"><p>%s</p></div>',
            esc_attr($message['type']),
            esc_html($message['text'])
        );
    }

    private function render_styles()
    {
    ?>
        <style>
            /* CSS styles remain unchanged */
            .wzc-admin-wrap {
                margin: 20px;
                max-width: 800px;
            }

            .wzc-admin-container {
                background: #fff;
                padding: 25px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .wzc-form-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .wzc-form-table td {
                padding: 15px 10px;
                vertical-align: top;
            }

            .wzc-form-table td:first-child {
                width: 200px;
                font-weight: 600;
            }

            .wzc-form-input {
                width: 100%;
                max-width: 300px;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .wzc-form-note {
                color: #666;
                font-size: 13px;
                margin-top: 5px;
            }

            .wzc-notice {
                padding: 12px 15px;
                margin: 0 0 20px;
                border-left: 4px solid;
                background: #fff;
            }

            .wzc-notice-success {
                border-color: #46b450;
                background: #f0f8f0;
            }

            .wzc-notice-error {
                border-color: #dc3232;
                background: #fff6f6;
            }

            .wzc-form-actions {
                margin-top: 20px;
                display: flex;
                gap: 10px;
            }

            .wzc-submit-button {
                background: #2271b1;
                border: none;
                color: #fff;
                padding: 7px 15px;
                border-radius: 3px;
                cursor: pointer;
            }

            .wzc-toggle-container {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .wzc-toggle {
                position: relative;
                display: inline-block;
                width: 50px;
                height: 24px;
            }

            .wzc-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .wzc-toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 24px;
            }

            .wzc-toggle-slider:before {
                position: absolute;
                content: "";
                height: 16px;
                width: 16px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            .wzc-toggle input:checked+.wzc-toggle-slider {
                background-color: #2271b1;
            }

            .wzc-toggle input:checked+.wzc-toggle-slider:before {
                transform: translateX(26px);
            }

            .wzc-validation-message {
                color: #dc3232;
                font-size: 13px;
                margin-top: 5px;
            }

            @media screen and (max-width: 782px) {
                .wzc-form-table td {
                    display: block;
                    padding: 15px 0;
                }

                .wzc-form-table td:first-child {
                    width: 100%;
                    padding-bottom: 5px;
                }
            }
        </style>

<?php
    }
}

// Initialize and render the form
$pincode_form = new WCZC_Add_Edit_Pincode();
$pincode_form->render();
