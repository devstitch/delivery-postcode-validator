<?php
class WZC_Bulk_Import_Handler
{
    private $notices = [];
    private $sample_file_path;

    public function __construct()
    {
        $this->sample_file_path = WZC_DIR_URL . 'pincode_sample.csv';
        $this->init();
    }

    public function init()
    {
        // Check for import status messages
        $this->check_import_status();
    }

    private function check_import_status()
    {
        if (isset($_GET['import'])) {
            switch ($_GET['import']) {
                case 'error':
                    $this->add_notice('error', 'Import failed, invalid file extension or something bad happened.');
                    break;
                case 'success':
                    $records = isset($_GET['records']) ? absint($_GET['records']) : 0;
                    $this->add_notice('success', sprintf('Total Records inserted: %d', $records));
                    break;
            }
        }
    }

    private function add_notice($type, $message)
    {
        $this->notices[] = [
            'type' => $type,
            'message' => $message
        ];
    }

    private function render_notices()
    {
        foreach ($this->notices as $notice) {
            printf(
                '<div class="wzc_notice_%s"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
        }
    }

    public function render()
    {
?>
        <div class="wrapper">
            <div class="wzc_container">
                <h2>Bulk Import Post/Zip Codes</h2>
                <div class="wzc_import_container">
                    <div class="wzc_import_box">
                        <?php $this->render_notices(); ?>

                        <form method="post" enctype="multipart/form-data" class="wzc_import">
                            <?php
                            wp_nonce_field('wzc_import_postcodes_action', 'wzc_import_postcodes_field');
                            $this->render_form_content();
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private function render_form_content()
    {
    ?>
        <div class="wzc_importbox">
            <h3>Bulk import post/Zip codes via csv</h3>
            <input type="file"
                name="import_file"
                required
                accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
            <input type="hidden" name="action" value="wzc_import_postcodes">
            <input type="submit" name="butimport" value="Import">
        </div>

        <a href="<?php echo esc_url($this->sample_file_path); ?>"
            download
            class="wzc_demo_file">
            Download sample file
        </a>

        <p class="description">
            This is the sample file of pincodes for csv import.
        </p>

        <div class="wzc_note">
            <p>
                <strong>Note: </strong>
                In CSV File, you have to add only
                <span class="highlight">0</span> or
                <span class="highlight">1</span> value under
                <strong>Cash on Delivery</strong> column.
            </p>
            <p>
                <span class="highlight">0</span> = <strong>COD not enable</strong>
                <br>
                <span class="highlight">1</span> = <strong>COD enable</strong>
            </p>
        </div>
<?php
    }
}

// Initialize and render the form
$import_handler = new WZC_Bulk_Import_Handler();
$import_handler->render();
