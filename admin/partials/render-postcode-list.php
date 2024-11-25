<?php
$pincodeLists = new WZC_List_Table();
$pincodeLists->prepare_items();
?>
<div class="wrapper">
    <div class="wzc_container">
        <h2>Post/Zip Codes List</h2>
        <div class="wzcleftbox">
            <div class="cwzcinfobox">
                <form method="post" class="wzc_list_postcode">
                    <?php wp_nonce_field('wzc_delete_postcode_action', 'wzc_delete_postcode_field'); ?>

                    <div class="tablenav top">
                        <div class="alignleft actions">
                            <a href="?page=add-pin-code" class="button wzc_add_postcode">Add Pincode</a>
                        </div>
                        <div class="tablenav-pages one-page">
                            <div class="search-box">
                                <label class="screen-reader-text" for="post-search-input">Search Pincodes:</label>
                                <input type="search" id="post-search-input" name="s"
                                    value="<?php echo isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : ''; ?>"
                                    placeholder="Search pincodes...">
                                <input type="submit" id="search-submit" class="button" value="Search">
                            </div>
                        </div>
                        <br class="clear">
                    </div>

                    <?php $pincodeLists->display(); ?>
                </form>
            </div>
        </div>
    </div>
</div>