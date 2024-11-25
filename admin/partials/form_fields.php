<?php

$input_fileds = array(
  array(
    'type' => 'checkbox',
    'lable' => 'Enable Post/Zip Code Availability Check',
    'name' => 'wzc_enable_checkpincode',
    'value' => get_option('wzc_enable_checkpincode', 'on'),
    'attribute' => "",
    'class' => 'switch',
    'description' => ''
  ),
  array(
    'type' => 'text',
    'lable' => 'Check Availability At Heading',
    'name' => 'wzc_heading_txt',
    'value' => get_option('wzc_heading_txt', 'Check Availability'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => ''
  ),
  array(
    'type' => 'text',
    'lable' => 'Available Post/Zip Code Text',
    'name' => 'wzc_serviceable_txt',
    'value' => get_option('wzc_serviceable_txt', 'Shipping available at your location'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => ''
  ),

  array(
    'type' => 'text',
    'lable' => 'Not Available Post/Zip Code Text',
    'name' => 'wzc_not_serviceable_txt',
    'value' => get_option('wzc_not_serviceable_txt', 'Unfortunately we do not ship to your pincode'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => ''
  ),
  array(
    'type' => 'dropdown',
    'lable' => 'Check Post/Zip Code Form Position',
    'name' => 'wzc_check_pincode_pos',
    'value' => get_option('wzc_check_pincode_pos', ''),
    'attribute' => "",
    'options' => array(
      'after_atc' => 'After Add to Cart Button',
      'before_atc' => 'Before Add to Cart Button',
    ),
    'class' => 'wzcfields',



  ),
  array(
    'type' => 'text',
    'lable' => 'Check Button Text',
    'name' => 'wzc_btn_txt',
    'value' => get_option('wzc_btn_txt', 'Check'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => ''
  ),

  array(
    'type' => 'checkbox',
    'lable' => 'Show Delivery Date',
    'name' => 'wzc_del_date',
    'value' => get_option('wzc_del_date', 'on'),
    'attribute' => "",
    'class' => 'switch',
    'description' => ''
  ),
  array(
    'type' => 'text',
    'lable' => 'Delivery Date Text',
    'name' => 'wzc_delivery_date_txt',
    'value' => get_option('wzc_delivery_date_txt', 'Estimated delivery by'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => ''
  ),
  array(
    'type' => 'checkbox',
    'lable' => 'Show Cash On Delivery Option',
    'name' => 'wzc_cash_dilivery',
    'value' => get_option('wzc_cash_dilivery', 'on'),
    'attribute' => "",
    'class' => 'switch',
    'description' => ''
  ),
  array(
    'type' => 'text',
    'lable' => 'Cash On Delivery Label text',
    'name' => 'wzc_cash_on_delivery_txt',
    'value' => get_option('wzc_cash_on_delivery_txt', 'Cash On Delivery'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => ''
  ),
  array(
    'type' => 'checkbox',
    'lable' => 'Enable Return & Exchange',
    'name' => 'wzc_return_exchange',
    'value' => get_option('wzc_return_exchange', 'on'),
    'attribute' => "",
    'class' => 'switch',
    'description' => ''
  ),
  array(
    'type' => 'text',
    'lable' => 'Return/Exchange Label Text',
    'name' => 'wzc_return_exc_label_txt',
    'value' => get_option('wzc_return_exc_label_txt', 'Easy 7 days return & exchange available'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => ''
  ),

  array(
    'type' => 'checkbox',
    'lable' => 'Enable Order Countdown',
    'name' => 'wzc_order_countdown',
    'value' => get_option('wzc_order_countdown', 'on'),
    'attribute' => "",
    'class' => 'switch',
    'description' => ''
  ),
  array(
    'type' => 'text',
    'lable' => 'Countdown Label Text',
    'name' => 'wzc_countdown_label_txt',
    'value' => get_option('wzc_countdown_label_txt', 'Order within'),
    'attribute' => "",
    'class' => 'wzcfields',
    'description' => sprintf(
      'Shipping countdown time calculation starts from the current time to the next day at 12:00 AM. Your current timezone: %s. Current date and time: %s.',
      wp_timezone_string(),
      wp_date('F j, Y g:i a')
    )
  ),



);
