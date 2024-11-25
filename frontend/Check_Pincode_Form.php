<?php

namespace WZC\frontend;

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Check_Pincode_Form')) {
    class Check_Pincode_Form
    {

        public function __construct()
        {

            if (get_option('wzc_enable_checkpincode', 'on') == 'on') {
                add_shortcode('wzc_check_pincode', array($this, 'wzc_check_pincode_shortcode'));
                if (get_option('wzc_check_pincode_pos', 'after_atc') == 'after_atc') {
                    add_action('woocommerce_after_add_to_cart_button', array($this, 'wzc_check_pincode_form_callback'), 10);
                } elseif (get_option('wzc_check_pincode_pos', 'after_atc') == 'before_atc') {
                    add_action('woocommerce_before_add_to_cart_button', array($this, 'wzc_check_pincode_form_callback'));
                }
                add_action('wp_ajax_wzc_check_location', array($this, 'AJAX_check_pincode'));
                add_action('wp_ajax_nopriv_wzc_check_location', array($this, 'AJAX_check_pincode'));
            }
        }

        /**
         * check Pincode Form before add to cart button
         */
        function wzc_check_pincode_form_callback()
        {


            $wzc_heading_txt = get_option('wzc_heading_txt', 'Check Availability At');

            $order_countdown = get_option('wzc_order_countdown', 'on');
            $order_countdown_txt = get_option('wzc_countdown_label_txt', 'Order within');

            $text = get_option('wzc_btn_txt', 'Check');
            $not_serviceable_text = get_option('wzc_not_serviceable_txt', 'Unfortunately we do not ship to your pincode');
            $edit_icon = WZC_DIR_PATH . '/assets/img/icon-edit.svg';

            if ($order_countdown == 'on') {
?>
                <script>
                    jQuery(document).ready(function($) {

                        function getCounterData() {
                            var hours = parseInt($('.countOrderTime .e-m-hours').text());
                            var minutes = parseInt($('.countOrderTime .e-m-minutes').text());
                            var seconds = parseInt($('.countOrderTime .e-m-seconds').text());
                            return seconds + (minutes * 60) + (hours * 3600);
                        }

                        function setCounterData(s) {

                            var hours = Math.floor((s % (60 * 60 * 24)) / (3600));
                            var minutes = Math.floor((s % (60 * 60)) / 60);
                            var seconds = Math.floor(s % 60);

                            $('.countOrderTime .e-m-hours').html(hours);
                            $('.countOrderTime .e-m-minutes').html(minutes);
                            $('.countOrderTime .e-m-seconds').html(seconds);

                        }

                        var count = getCounterData();
                        // console.log(count);
                        var timer = setInterval(function() {
                            count--;
                            if (count == 0) {
                                clearInterval(timer);
                                return;
                            }
                            setCounterData(count);
                        }, 1000);
                    });
                </script>
            <?php } ?>
            <div class="wzc_containerbox">
                <?php
                if ($order_countdown == 'on') {
                    $string = "+1 days";
                    $newdeldate = wp_date('Y-m-d', strtotime($string)) . ' 00:00:00';
                    $future = strtotime($newdeldate); //Future date.
                    $timefromdb = strtotime(wp_date('Y-m-d H:i:s'));
                    $timeleft = $future - $timefromdb;
                    $hrsleft = round(($timeleft % (60 * 60 * 24)) / (3600));
                    $minsleft = round(($timeleft % (60 * 60)) / 60);
                    $secleft = round($timeleft % 60);

                    if ($future > $timefromdb) {
                        printf(
                            '<p style="color:#222;font-weight:600;">%s <span class="countOrderTime"><span class="e-m-hours">%s</span> h <span class="e-m-minutes">%s</span> m <span class="e-m-seconds">%s</span> s. </span></p>',
                            esc_html($order_countdown_txt),
                            esc_html($hrsleft - 1),
                            esc_html($minsleft),
                            esc_html($secleft)
                        );
                    }
                }
                ?>
                <h3><?php esc_html_e($wzc_heading_txt, 'wc-zip-checker'); ?></h3>
                <div class="wzc_formbox <?php if (isset($_COOKIE['wzc_postcode'])) {
                                            echo "changepincode";
                                        } ?>">
                    <input type="tel"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                        name="wzccheck"
                        class="wzccheck"
                        placeholder="Enter pincode"
                        minlength="6"
                        maxlength="6"
                        value="<?php echo isset($_COOKIE['wzc_postcode']) && $_COOKIE['wzc_postcode'] !== 'no' ? esc_attr(sanitize_text_field(wp_unslash($_COOKIE['wzc_postcode']))) : ''; ?>">
                    <div class="tickbox" style="<?php if (isset($_COOKIE['wzc_postcode'])) {
                                                    echo 'display:block';
                                                } ?>"><svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" image-rendering="optimizeQuality" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" viewBox="0 0 6.827 6.827">
                            <circle cx="3.413" cy="3.413" r="3.413" fill="#23c5a0" />
                            <path fill="#fff" d="m2.57 3.907 2.267-1.8a.356.356 0 0 1 .574.319.354.354 0 0 1-.132.238L2.704 4.708l-.025.02a.356.356 0 0 1-.032.02l-.014.009a.355.355 0 0 1-.471-.139l-.04-.067h.001l-.662-1.149a.354.354 0 1 1 .614-.353l.496.858z" />
                        </svg></div>
                    <?php if (isset($_COOKIE['wzc_postcode'])) { ?>
                        <input type="button" name="wzcbtn" class="wzc_btn" value="Change">
                    <?php } else { ?>
                        <input type="button"
                            name="wzcbtn"
                            class="wzcbtn"
                            value="<?php echo esc_attr($text); ?>">
                    <?php    }
                    ?>

                </div>


                <div class="wzc_checkcode">
                    <div class="response_pin">
                        <p class="pincode-enterPincode">Please enter PIN code to check delivery time &amp; Pay on Delivery Availability</p>
                    </div>

                </div>


            </div>


<?php
        }

        /**
         * check Pincode in database using AJAX
         */
        function AJAX_check_pincode()
        {
            global $wpdb;
            $pincode = sanitize_text_field($_REQUEST['postcode']);

            $tablename = $wpdb->prefix . 'wzc_postcode';
            $cntSQL = "SELECT * FROM {$tablename} where wpcc_pincode='" . $pincode . "'";
            $record = $wpdb->get_results($cntSQL, OBJECT);
            $date = $record[0]->wpcc_days;
            $cod = $record[0]->wpcc_cod;
            $codtxt = "";

            $string = "+" . $date . " days";

            $deliverydate = gmdate('jS M', strtotime($string));
            $dayofweek = gmdate('D', strtotime($string));
            $deliverydate = $dayofweek . ', ' . $deliverydate;

            $totalrec = count($record);
            $showdate = get_option('wzc_del_date', 'on');

            $showcashondelivery = get_option('wzc_cash_dilivery', 'on');

            $showreturn = get_option('wzc_return_exchange', 'on');

            $del_avail_icon = WZC_DIR_PATH . '/assets/img/true.png';
            $cash_on_delivary_text = get_option('wzc_cash_on_delivery_txt', 'Cash On Delivery');
            $check_availability_text = get_option('wzc_heading_txt', 'Check Availability At');
            $serviceable_text = get_option('wzc_serviceable_txt', 'Shipping available at your location');
            $delivery_text = get_option('wzc_delivery_date_txt', 'Estimated delivery by');
            $easyReturnText = get_option('wzc_return_exc_label_txt', 'Easy 7 days return & exchange available');
            if ($showcashondelivery == 'on' && $cod == 1) {
                $codtxt = "<p>" . $cash_on_delivary_text . "</p>";
            } else {
                $codtxt = "";
            }
            $data = array();
            $data = array(
                'pincode'      => $pincode,
                'deliverydate' => $deliverydate,
                'totalrec'     => $totalrec,

            );
            $avai_msg = '';


            $expiry = strtotime('+7 day');
            setcookie('wzc_postcode', $pincode, $expiry, COOKIEPATH, COOKIE_DOMAIN);
            if ($totalrec == 1) {

                $avai_msg .= '<div class="wpcc_serviceavailtext"><span class="serviceavailtxt">' . $serviceable_text . '</span></div>';

                if ($showdate == "on") {
                    $avai_msg .= '<div class="wzc_avaitxt"><span class="wzc_delicons"><svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                         width="512.000000pt" height="512.000000pt" viewBox="0 0 512.000000 512.000000"
                         preserveAspectRatio="xMidYMid meet">

                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"
                        fill="#000000" stroke="none">
                        <path d="M558 4059 c-68 -35 -78 -71 -78 -279 l0 -180 -105 0 c-86 0 -113 -4
                        -145 -20 -45 -23 -80 -80 -80 -130 0 -50 35 -107 80 -130 37 -19 58 -20 449
                        -20 345 0 417 -2 449 -15 96 -39 101 -180 9 -249 -28 -21 -40 -21 -530 -26
                        -456 -5 -505 -7 -534 -23 -38 -20 -73 -82 -73 -127 0 -50 35 -107 80 -130 38
                        -19 58 -20 747 -20 l708 0 39 -23 c100 -57 100 -197 0 -254 l-39 -23 -708 0
                        c-689 0 -709 -1 -747 -20 -45 -23 -80 -80 -80 -130 0 -50 35 -107 80 -130 35
                        -18 59 -20 220 -20 l180 0 0 -240 c0 -352 0 -352 278 -360 l173 -5 22 -65 c30
                        -90 69 -152 137 -220 224 -224 586 -224 810 0 68 68 107 130 137 220 l22 65
                        761 0 761 0 22 -65 c30 -90 69 -152 137 -220 224 -224 586 -224 810 0 68 68
                        107 130 137 220 l22 65 153 5 c176 6 205 16 238 80 19 38 20 58 20 538 0 542
                        -2 562 -57 665 -36 64 -119 139 -197 176 -125 60 -133 61 -795 61 l-603 0 -40
                        22 c-79 45 -78 36 -78 563 l0 465 -1377 0 c-1352 -1 -1379 -1 -1415 -21z
                        m1052 -2184 c92 -46 160 -153 160 -250 0 -97 -68 -204 -159 -250 -121 -61
                        -296 1 -364 129 -31 58 -31 184 0 242 30 56 106 121 162 139 57 18 155 14 201
                        -10z m2650 0 c92 -46 160 -153 160 -250 0 -97 -68 -204 -159 -250 -121 -61
                        -296 1 -364 129 -31 58 -31 184 0 242 30 56 106 121 162 139 57 18 155 14 201
                        -10z"/>
                        <path d="M3650 3690 l0 -360 410 0 c226 0 410 3 410 6 0 13 -125 210 -184 289
                        -118 159 -233 260 -382 334 -63 31 -227 91 -250 91 -2 0 -4 -162 -4 -360z"/>
                        </g>
                        </svg></span>';


                    $avai_msg .= '<div class="wzc_avaddate"><p>' . $delivery_text . ' <span class="ddatecolor"> ' . $deliverydate . '</span> ';

                    $avai_msg .= '</p></div></div>';
                }

                if ($showcashondelivery == 'on' && $cod == "1") {

                    $avai_msg .= '<div class="wzc_dlvrytxt"><span class="wzc_delicons">
<?xml version="1.0" encoding="iso-8859-1"?>
<!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
     viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
<g>
    <g>
        <path d="M172.55,391.902c-0.13-0.64-0.32-1.27-0.57-1.88c-0.25-0.6-0.56-1.18-0.92-1.72c-0.36-0.55-0.78-1.06-1.24-1.52
            c-0.46-0.46-0.97-0.88-1.52-1.24c-0.54-0.36-1.12-0.67-1.73-0.92c-0.6-0.25-1.23-0.45-1.87-0.57c-1.29-0.26-2.62-0.26-3.9,0
            c-0.64,0.12-1.27,0.32-1.88,0.57c-0.6,0.25-1.18,0.56-1.72,0.92c-0.55,0.36-1.06,0.78-1.52,1.24c-0.46,0.46-0.88,0.97-1.24,1.52
            c-0.37,0.54-0.67,1.12-0.92,1.72c-0.25,0.61-0.45,1.24-0.57,1.88c-0.13,0.64-0.2,1.3-0.2,1.95c0,0.65,0.07,1.31,0.2,1.95
            c0.12,0.64,0.32,1.27,0.57,1.87c0.25,0.61,0.55,1.19,0.92,1.73c0.36,0.55,0.78,1.06,1.24,1.52c0.46,0.46,0.97,0.88,1.52,1.24
            c0.54,0.361,1.12,0.671,1.72,0.921c0.61,0.25,1.24,0.45,1.88,0.57c0.64,0.13,1.3,0.2,1.95,0.2c0.65,0,1.31-0.07,1.95-0.2
            c0.64-0.12,1.27-0.32,1.87-0.57c0.61-0.25,1.19-0.561,1.73-0.921c0.55-0.36,1.06-0.78,1.52-1.24c0.46-0.46,0.88-0.97,1.24-1.52
            c0.36-0.54,0.67-1.12,0.92-1.73c0.25-0.6,0.44-1.23,0.57-1.87s0.2-1.3,0.2-1.95S172.68,392.542,172.55,391.902z"/>
    </g>
</g>
<g>
    <g>
        <path d="M459.993,394.982c-0.039-0.1-0.079-0.199-0.121-0.297c-9.204-21.537-30.79-29.497-56.336-20.772l-69.668,19.266
            c-4.028-12.198-14.075-22.578-28.281-27.85c-0.088-0.032-0.176-0.064-0.265-0.094l-76.581-25.992
            c-6.374-8.239-26.34-29.321-63.723-29.321c-26.125,0-49.236,17.922-62.458,37.457H10c-5.523,0-10,4.477-10,10v126.077
            c0,5.523,4.477,10,10,10h59.457c5.523,0,10-4.477,10-10v-8.634h27.883c5.523,0,10-4.477,10-10v-2.878
            c16.254,1.418,21.6,4.501,36.528,13.109c11.48,6.62,28.831,16.625,60.077,30.674c0.145,0.065,0.292,0.127,0.439,0.185
            c5.997,2.359,17.72,6.065,32.173,6.065c10.06,0,21.445-1.797,33.131-7.094l153.991-55.136c0.274-0.098,0.544-0.208,0.808-0.33
            C449.204,442.646,471.135,423.563,459.993,394.982z M59.457,473.455H20V367.378h39.457V473.455z M97.34,454.821H79.457v-87.443
            H97.34V454.821z M426.496,431.074l-153.922,55.111c-0.135,0.048-0.318,0.12-0.451,0.174c-0.135,0.055-0.27,0.113-0.403,0.174
            c-21.437,9.852-41.814,3.954-49.8,0.849c-30.182-13.581-46.291-22.87-58.061-29.657c-16.364-9.436-24.249-13.984-46.519-15.823
            V361.36c9.479-15.536,27.861-31.439,47.679-31.439c33.986,0,48.387,22.105,48.953,22.997c1.221,1.986,3.098,3.483,5.305,4.232
            l79.475,26.974c12.693,4.764,19.401,15.634,16.318,26.474c-1.423,5.006-4.711,9.158-9.257,11.691
            c-4.507,2.511-9.717,3.132-14.683,1.758l-89.593-28.392c-5.268-1.669-10.886,1.247-12.554,6.512
            c-1.669,5.265,1.247,10.885,6.512,12.554l89.749,28.441c0.095,0.03,0.19,0.059,0.286,0.086c3.583,1.019,7.231,1.523,10.857,1.523
            c6.638,0,13.203-1.691,19.161-5.011c9.213-5.133,15.875-13.547,18.759-23.692c0.23-0.81,0.434-1.62,0.611-2.43l75.083-20.8
            c10.844-3.704,25.079-5.039,31.417,9.558C447.978,419.533,430.928,428.96,426.496,431.074z"/>
    </g>
</g>
<g>
    <g>
        <path d="M359.06,131.543c-0.13-0.64-0.32-1.27-0.58-1.88c-0.25-0.6-0.55-1.18-0.92-1.72c-0.36-0.55-0.78-1.06-1.24-1.52
            c-0.46-0.46-0.97-0.88-1.52-1.24c-0.54-0.36-1.12-0.67-1.72-0.92c-0.61-0.25-1.24-0.45-1.87-0.57c-1.29-0.26-2.62-0.26-3.91,0
            c-0.64,0.12-1.27,0.32-1.87,0.57c-0.61,0.25-1.19,0.56-1.73,0.92c-0.55,0.36-1.06,0.78-1.52,1.24c-0.46,0.46-0.88,0.97-1.24,1.52
            c-0.36,0.54-0.67,1.12-0.92,1.72c-0.25,0.61-0.45,1.24-0.57,1.88c-0.13,0.64-0.2,1.3-0.2,1.95c0,0.65,0.07,1.31,0.2,1.95
            c0.12,0.64,0.32,1.27,0.57,1.87c0.25,0.61,0.56,1.19,0.92,1.73c0.36,0.55,0.78,1.06,1.24,1.52c0.46,0.46,0.97,0.88,1.52,1.24
            c0.54,0.36,1.12,0.67,1.73,0.92c0.6,0.25,1.23,0.44,1.87,0.57s1.3,0.2,1.95,0.2c0.65,0,1.31-0.07,1.96-0.2
            c0.63-0.13,1.26-0.32,1.87-0.57c0.6-0.25,1.18-0.56,1.72-0.92c0.55-0.36,1.06-0.78,1.52-1.24c0.46-0.46,0.88-0.97,1.24-1.52
            c0.37-0.54,0.67-1.12,0.92-1.73c0.26-0.6,0.45-1.23,0.58-1.87c0.13-0.64,0.19-1.3,0.19-1.95
            C359.25,132.843,359.19,132.183,359.06,131.543z"/>
    </g>
</g>
<g>
    <g>
        <path d="M502,33.891h-59.457c-5.523,0-10,4.477-10,10v8.634H404.66c-5.523,0-10,4.477-10,10v2.878
            c-16.254-1.419-21.6-4.501-36.527-13.109c-11.48-6.62-28.831-16.625-60.078-30.674c-0.145-0.066-0.291-0.127-0.44-0.185
            c-10.171-4.002-36.828-11.876-65.299,1.027l-40.24,14.408L158.157,2.952c-3.905-3.905-10.237-3.905-14.142,0L32.657,114.309
            c-3.602,3.603-4.293,9.85,0,14.143l190.287,190.287c3.045,3.046,10.175,3.967,14.143,0l101.665-101.664
            c2.643,0.228,5.386,0.351,8.229,0.351c26.126,0,49.236-17.922,62.457-37.456H502c5.523,0,10-4.477,10-10V43.891
            C512,38.368,507.523,33.891,502,33.891z M151.085,24.165l22.792,22.792c-6.775,4.19-14.608,6.432-22.792,6.432
            c-8.185,0-16.017-2.241-22.792-6.432L151.085,24.165z M76.663,144.173L53.871,121.38l22.792-22.792
            c4.19,6.775,6.432,14.608,6.432,22.792C83.095,129.564,80.854,137.397,76.663,144.173z M230.016,297.525l-22.788-22.788
            c13.913-8.586,31.661-8.586,45.575,0L230.016,297.525z M267.211,260.331c-22.098-16.03-52.292-16.03-74.39,0L91.07,158.579
            c7.809-10.74,12.025-23.641,12.025-37.199c0-13.559-4.215-26.459-12.025-37.199l22.817-22.816
            c10.74,7.809,23.64,12.025,37.199,12.025c13.559,0,26.459-4.216,37.199-12.025l21.629,21.629
            c-4.667,0.689-9.218,2.227-13.462,4.592c-7.168,3.994-12.792,9.975-16.294,17.211c-11.28,2.089-21.723,7.55-29.915,15.741
            c-22.225,22.226-22.225,58.389,0.001,80.615c11.112,11.112,25.709,16.669,40.307,16.669c14.597,0,29.195-5.556,40.308-16.669
            c7.23-7.23,12.295-16.116,14.832-25.8l33.764,11.459c-3.801,17.608,0.092,36.132,10.593,50.682L267.211,260.331z M206.413,162.018
            c0.088,0.032,0.176,0.064,0.265,0.094l19.996,6.787c-1.51,6.815-4.927,13.081-9.957,18.112c-14.428,14.426-37.904,14.428-52.33,0
            c-14.428-14.427-14.428-37.902,0-52.33c3.48-3.482,7.587-6.203,12.062-8.048C178.295,141.995,189.356,155.688,206.413,162.018z
             M304.457,223.084c-3.86-6.29-6.044-13.469-6.389-20.796c4.79,3.463,10.644,6.856,17.636,9.549L304.457,223.084z M394.659,165.983
            c-9.478,15.538-27.86,31.441-47.678,31.441c-3.708,0-7.183-0.264-10.432-0.734c-0.013-0.002-0.026-0.004-0.039-0.006
            c-21.596-3.137-33.213-15.411-37.042-20.271c-0.204-0.3-1.073-1.437-1.202-1.626c-1.165-2.082-3.075-3.756-5.511-4.583
            l-79.508-26.985c-12.688-4.762-19.395-15.627-16.321-26.463c0.002-0.007,0.004-0.014,0.006-0.021
            c0.003-0.008,0.005-0.017,0.007-0.025c1.429-4.99,4.711-9.129,9.247-11.656c4.506-2.511,9.715-3.134,14.683-1.757l89.593,28.391
            c5.266,1.671,10.886-1.247,12.554-6.512c1.668-5.265-1.247-10.885-6.512-12.554l-71.255-22.58l-0.622-0.622
            c-0.006-0.006-0.012-0.013-0.019-0.019l-36.89-36.89l31.708-11.354c0.107-0.039,0.239-0.088,0.345-0.131
            c0.027-0.011,0.079-0.031,0.105-0.042c0.136-0.055,0.27-0.113,0.403-0.174c21.436-9.852,41.812-3.955,49.799-0.849
            c30.183,13.581,46.293,22.87,58.063,29.657c16.364,9.437,24.249,13.984,46.518,15.823V165.983z M432.543,159.968H414.66V72.525
            h17.883V159.968z M492,159.968h-39.457V53.891H492V159.968z"/>
    </g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
</svg>
</span><div class="wzc_avacod">' . $codtxt . '</div></div>';
                }
                if ($showreturn == "on") {
                    $avai_msg .= '<div class="wzc_dlvrytxt"><span class="wzc_delicons"><svg viewBox="0 0 24 24" class="pincode-serviceabilityIcon"><g fill="#535766"><path d="M15.19 8.606V4.3a.625.625 0 00-.622-.625H6.384V.672a.624.624 0 00-.407-.588.62.62 0 00-.687.178L.367 6.048a.628.628 0 000 .812l4.923 5.778a.626.626 0 00.687.182.624.624 0 00.407-.588V9.228h8.184a.62.62 0 00.621-.622zm-1.244-.625H5.762a.625.625 0 00-.621.625v1.938l-3.484-4.09L5.14 2.362V4.3c0 .344.28.625.621.625h8.184v3.056z"></path><path d="M22.708 13.028L17.785 7.25a.616.616 0 00-.687-.178.624.624 0 00-.407.587v3.003H8.507a.625.625 0 00-.622.625v4.304c0 .343.28.625.622.625h8.184v3.003a.624.624 0 00.621.625.626.626 0 00.473-.219l4.923-5.781a.632.632 0 000-.816zm-4.774 4.497v-1.937a.625.625 0 00-.622-.625H9.13v-3.054h8.183a.625.625 0 00.622-.625V9.347l3.484 4.09-3.484 4.088z"></path></g></svg></span>' . $easyReturnText . '</div>';
                }

                $avai_msg .= '';
            }

            $data['avai_msg'] = $avai_msg;

            echo json_encode($data);

            exit();
        }

        function wzc_check_pincode_shortcode($atts, $content = null)
        {

            ob_start();

            $this->wzc_check_pincode_form_callback();

            $content = ob_get_clean();

            return $content;
        }
    }
}
