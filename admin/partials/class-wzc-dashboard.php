<?php
class WZC_Dashboard_Page
{
    private $menu_items = [];

    public function __construct()
    {
        $this->init_menu_items();
    }

    private function init_menu_items()
    {
        $this->menu_items = [
            [
                'title' => 'All Post/Zip Code',
                'description' => 'View all the available post/zip code list and update it.',
                'icon' => 'dashicons-location',
                'link' => 'view-pin-code',
                'button_text' => 'Click to View All'
            ],
            [
                'title' => 'Add New Post/Zip Code',
                'description' => 'You can add new post/zip code details manually one by one.',
                'icon' => 'dashicons-plus-alt',
                'link' => 'add-pin-code',
                'button_text' => 'Click to Add New'
            ],
            [
                'title' => 'Setting',
                'description' => 'Enable and disable option and update dynamic information.',
                'icon' => 'dashicons-admin-settings',
                'link' => 'pin-code-setting',
                'button_text' => 'Click to Change Setting'
            ],
            [
                'title' => 'Import Post/Zip Code',
                'description' => 'Import bulk post/zip code records using csv file.',
                'icon' => 'dashicons-database-import',
                'link' => 'pin-code-import',
                'button_text' => 'Click to Bulk Import'
            ]
        ];
    }

    /**
     * Get properly escaped admin URL
     *
     * @param string $page The page slug
     * @return string Escaped URL
     */
    private function get_admin_url($page)
    {
        return esc_url(admin_url(sprintf('admin.php?page=%s', sanitize_key($page))));
    }

    /**
     * Render the dashboard page
     */
    public function render()
    {
?>
        <div class="wzc-wrapper">
            <div class="wzc-container">
                <h1 class="wzc-title"><?php echo esc_html__('Post/Zip Code Checker Dashboard', 'wc-zip-checker'); ?></h1>

                <div class="wzc-dashboard">
                    <div class="wzc-grid">
                        <?php foreach ($this->menu_items as $item): ?>
                            <div class="wzc-card">
                                <div class="wzc-card-content">
                                    <div class="wzc-card-icon">
                                        <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                                    </div>
                                    <div class="wzc-card-text">
                                        <h3><?php echo esc_html($item['title']); ?></h3>
                                        <p><?php echo esc_html($item['description']); ?></p>
                                    </div>
                                    <div class="wzc-card-action">
                                        <a href="<?php echo esc_url($this->get_admin_url($item['link'])); ?>"
                                            class="wzc-button">
                                            <?php echo esc_html($item['button_text']); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}

// Initialize and render the dashboard
$dashboard = new WZC_Dashboard_Page();
$dashboard->render();
