<?php
class WZC_Settings_Form
{
    private $nonce_action = 'wzc_nonce_action';
    private $nonce_field = 'wzc_nonce_field';
    private $input_fields = [];

    public function __construct()
    {
        $this->load_form_fields();
    }

    private function load_form_fields()
    {
        include WZC_DIR_PATH . 'admin/partials/form_fields.php';
        $this->input_fields = $input_fileds ?? [];
    }

    private function render_field($field)
    {
        if (!isset($field['type'])) {
            return '';
        }

        $method_name = "render_{$field['type']}_field";
        if (method_exists($this, $method_name)) {
            return $this->$method_name($field);
        }
        return '';
    }

    private function render_checkbox_field($field)
    {
        $checked = ($field['value'] ?? '') === 'on' ? 'checked' : '';
        return sprintf(
            '<div class="wzc-field-row">
                <div class="wzc-field-label">
                    <label for="%s">%s</label>
                </div>
                <div class="wzc-field-input">
                    <label class="%s">
                        <input type="checkbox" id="%s" %s name="%s" %s>
                        <span class="slider round"></span>
                    </label>
                    <p class="field-description">%s</p>
                </div>
            </div>',
            esc_attr($field['name'] ?? ''),
            esc_html($field['lable'] ?? ''),
            esc_attr($field['class'] ?? ''),
            esc_attr($field['name'] ?? ''),
            esc_attr($field['attribute'] ?? ''),
            esc_attr($field['name'] ?? ''),
            $checked,
            esc_html($field['description'] ?? '')
        );
    }

    private function render_text_field($field)
    {
        return sprintf(
            '<div class="wzc-field-row %s">
                <div class="wzc-field-label">
                    <label for="%s">%s</label>
                </div>
                <div class="wzc-field-input">
                    <input type="text" id="%s" %s name="%s" value="%s" class="regular-text">
                    <p class="field-description">%s</p>
                </div>
            </div>',
            esc_attr($field['class'] ?? ''),
            esc_attr($field['name'] ?? ''),
            esc_html($field['lable'] ?? ''),
            esc_attr($field['name'] ?? ''),
            esc_attr($field['attribute'] ?? ''),
            esc_attr($field['name'] ?? ''),
            esc_attr($field['value'] ?? ''),
            esc_html($field['description'] ?? '')
        );
    }

    private function render_html_field($field)
    {
        return sprintf(
            '<div class="wzc-field-row %s wzc-section-header">
                <div class="wzc-field-full">
                    <h2>%s</h2>
                    <p class="field-description">%s</p>
                </div>
            </div>',
            esc_attr($field['class'] ?? ''),
            esc_html($field['lable'] ?? ''),
            esc_html($field['description'] ?? '')
        );
    }

    private function render_dropdown_field($field)
    {
        $options = '';
        if (isset($field['options']) && is_array($field['options'])) {
            foreach ($field['options'] as $key => $value) {
                $selected = ($field['value'] ?? '') === $key ? 'selected' : '';
                $options .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($key),
                    $selected,
                    esc_html($value)
                );
            }
        }

        return sprintf(
            '<div class="wzc-field-row">
                <div class="wzc-field-label">
                    <label for="%s">%s</label>
                </div>
                <div class="wzc-field-input">
                    <select id="%s" %s name="%s" class="regular-text">
                        %s
                    </select>
                    <p class="field-description">%s</p>
                </div>
            </div>',
            esc_attr($field['name'] ?? ''),
            esc_html($field['lable'] ?? ''),
            esc_attr($field['name'] ?? ''),
            esc_attr($field['attribute'] ?? ''),
            esc_attr($field['name'] ?? ''),
            $options,
            esc_html($field['description'] ?? '')
        );
    }

    public function render()
    {
?>
        <div class="wrap wzc-settings-wrapper">
            <div class="wzc-settings-container">
                <header class="wzc-header">
                    <h1><?php esc_html_e('Basic Settings', 'wc-zip-checker'); ?></h1>
                </header>

                <div class="wzc-settings-box">
                    <form method="post" class="wzc-settings-form">
                        <?php wp_nonce_field($this->nonce_action, $this->nonce_field); ?>

                        <div class="wzc-fields-container">
                            <?php
                            foreach ($this->input_fields as $field) {
                                $rendered_field = $this->render_field($field);
                                if (!empty($rendered_field)) {
                                    echo wp_kses(
                                        $rendered_field,
                                        [
                                            'div' => ['class' => []],
                                            'label' => ['class' => [], 'for' => []],
                                            'input' => [
                                                'type' => [],
                                                'id' => [],
                                                'name' => [],
                                                'value' => [],
                                                'class' => [],
                                                'checked' => []
                                            ],
                                            'select' => [
                                                'id' => [],
                                                'name' => [],
                                                'class' => []
                                            ],
                                            'option' => [
                                                'value' => [],
                                                'selected' => []
                                            ],
                                            'span' => ['class' => []],
                                            'p' => ['class' => []],
                                            'h2' => []
                                        ]
                                    );
                                }
                            }
                            ?>
                        </div>

                        <div class="wzc-form-submit">
                            <input type="hidden" name="action" value="wzc_settings_save">
                            <button type="submit" name="wzc_txtadd_design" class="button button-primary button-large">
                                <?php esc_html_e('Save Changes', 'wc-zip-checker'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            .wzc-settings-wrapper {
                margin: 20px 0;
            }

            .wzc-settings-container {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .wzc-header {
                padding: 20px 25px;
                border-bottom: 1px solid #e5e5e5;
                background: #f8f9fa;
                border-radius: 8px 8px 0 0;
            }

            .wzc-header h1 {
                margin: 0;
                color: #1d2327;
                font-size: 1.5rem;
                font-weight: 600;
                line-height: 1.3;
            }

            .wzc-settings-box {
                padding: 25px;
            }

            .wzc-field-row {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 25px;
                padding-bottom: 25px;
                border-bottom: 1px solid #f0f0f0;
            }

            .wzc-field-row:last-child {
                border-bottom: none;
            }

            .wzc-field-label {
                flex: 0 0 300px;
                padding-right: 20px;
            }

            .wzc-field-label label {
                font-weight: 600;
                color: #1d2327;
                display: block;
                margin-bottom: 8px;
            }

            .wzc-field-input {
                flex: 1;
                min-width: 200px;
            }

            .wzc-section-header {
                margin: 30px 0 20px;
                padding: 15px 0;
                background: #f8f9fa;
                border-radius: 6px;
            }

            .wzc-section-header h2 {
                margin: 0;
                padding: 0 15px;
                font-size: 1.2rem;
                color: #1d2327;
            }

            .field-description {
                margin: 8px 0 0;
                color: #646970;
                font-size: 13px;
                font-style: italic;
                line-height: 1.5;
            }

            /* Form inputs styling */
            .regular-text {
                width: 100%;
                max-width: 400px;
                padding: 8px 12px;
                border: 1px solid #d0d5dd;
                border-radius: 6px;
                font-size: 14px;
                line-height: 1.5;
                transition: border-color 0.15s ease-in-out;
            }

            .regular-text:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
                outline: none;
            }

            select.regular-text {
                height: 40px;
                padding-right: 30px;
                background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%23667085' stroke-width='1.66667' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 12px center;
                background-size: 10px 6px;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
            }

            /* Toggle Switch Styling */
            .switch {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 24px;
            }

            .switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .2s;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .2s;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .slider.round {
                border-radius: 24px;
            }

            .slider.round:before {
                border-radius: 50%;
            }

            input:checked+.slider {
                background-color: #2271b1;
            }

            input:checked+.slider:before {
                transform: translateX(20px);
            }

            .wzc-form-submit {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e5e5e5;
            }

            @media screen and (max-width: 782px) {
                .wzc-field-row {
                    flex-direction: column;
                }

                .wzc-field-label {
                    flex: 0 0 100%;
                    padding-right: 0;
                    margin-bottom: 10px;
                }

                .wzc-field-input {
                    flex: 0 0 100%;
                }

                .regular-text {
                    max-width: 100%;
                }
            }
        </style>
<?php
    }
}

// Initialize and render the settings form
$settings_form = new WZC_Settings_Form();
$settings_form->render();
