<?php

use WebsmsCountryList;

/**
 * WordPress settings API class
 *
 * @author smsBell\ WSW FONSEKA. 
 */

class Websms_Setting_Options
{
	/**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 */
	public static function init()
	{
		require_once plugin_dir_path(__DIR__) . '/includes/countrylist.php';

		if (is_plugin_active('easy-digital-downloads/easy-digital-downloads.php')) {
			require_once plugin_dir_path(__DIR__) . '/includes/edd.php';
		}

		if (is_plugin_active('learnpress/learnpress.php')) {
			require_once plugin_dir_path(__DIR__) . '/includes/learnpress.php';
		}

		if (is_plugin_active('woocommerce-bookings/woocommerce-bookings.php')) {
			require_once plugin_dir_path(__DIR__) . '/includes/woocommerce-booking.php';
			self::addActionForBookingStatus();
		}

		if (is_plugin_active('ultimate-member/ultimate-member.php')) //>= UM version 2.0.17 
		{
			add_filter('um_predefined_fields_hook', __CLASS__ . '::my_predefined_fields');
		}

		add_action('admin_menu', __CLASS__ . '::websms_lk_wc_submenu');

		add_action('verify_senderid_button', 				__CLASS__ . '::action_woocommerce_admin_field_verify_websms_lk_user');
		add_action('admin_post_save_websms_lk_settings',  __CLASS__ . '::save');
		if (is_plugin_active('woocommerce-warranty/woocommerce-warranty.php')) {
			add_action('wc_warranty_settings_tabs', __CLASS__ . '::Websms_warranty_tab');
			add_action('wc_warranty_settings_panels', __CLASS__ . '::Websms_warranty_settings_panels');
		}



		self::Websms_dashboard_setup();

		if (array_key_exists('option', $_GET) && $_GET['option']) {
			switch (trim($_GET['option'])) {
				case 'Websms-woocommerce-senderlist':
					echo WebsmscURLOTP::get_senderids($_GET['user'], $_GET['pwd']);
					exit();
					break;
				case 'Websms-woocommerce-creategroup':
					WebsmscURLOTP::creategrp();
					echo WebsmscURLOTP::group_list();
					break;
				case 'Websms-woocommerce-logout':
					echo self::logout();
					break;
			}
		}
	}



	/*add Websms phone button in ultimate form*/
	public static function my_predefined_fields($predefined_fields)
	{
		$fields = array('billing_phone' => array(
			'title' => 'Websms Phone',
			'metakey' => 'billing_phone',
			'type' => 'text',
			'label' => 'Mobile Number',
			'required' => 0,
			'public' => 1,
			'editable' => 1,
			'validate' => 'billing_phone',
			'icon' => 'um-faicon-mobile',
		));
		$predefined_fields = array_merge($predefined_fields, $fields);
		return $predefined_fields;
	}

	/*add action for booking statuses*/
	public static function addActionForBookingStatus()
	{
		$wcbk_order_statuses = WebsmsWcBooking::get_booking_statuses();
		foreach ($wcbk_order_statuses as $wkey => $booking_status) {
			add_action('woocommerce_booking_' . $booking_status, __CLASS__ . '::wcbkStatusChanged');
		}
	}
	/*trigger sms on status change of booking*/
	public static function wcbkStatusChanged($booking_id)
	{
		$output = WebsmsWcBooking::triggerSms($booking_id);
	}

	public static function websms_lk_wc_submenu()
	{
		add_submenu_page('woocommerce', 					'smsBell', 						'smsBell', 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');

		add_submenu_page('edit.php?post_type=download', 	'smsBell', 						'smsBell', 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');

		add_submenu_page('gf_edit_forms', 					__('smsBell', 'gravityforms'),	__('smsBell', 'gravityforms'), 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');

		add_submenu_page('ultimatemember', 				__('smsBell', 'ultimatemember'), __('smsBell', 'ultimatemember'), 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');

		add_submenu_page('wpcf7', 							__('smsBell', 'wpcf7'), 		__('smsBell', 'wpcf7'), 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');

		add_submenu_page('pie-register', 					__('smsBell', 'pie-register'), 	__('smsBell', 'pie-register'), 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');

		add_submenu_page('wpam-affiliates', 				__('smsBell', 'affiliates-manager'), __('smsBell', 'affiliates-manager'), 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');

		add_submenu_page('learn_press', 					__('smsBell', 'learnpress'), 	__('smsBell', 'learnpress'), 'manage_options', 'wsw.lk', __CLASS__ . '::settings_tab');
	}

	public static function Websms_dashboard_setup()
	{
		add_action('dashboard_glance_items',  __CLASS__ . '::Websms_add_dashboard_widgets', 10, 1);
	}
	//warranty

	public static function Websms_warranty_tab()
	{
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
?>
		<a href="admin.php?page=warranties-settings&tab=Websms_warranty" class="nav-tab <?php echo ($active_tab == 'Websms_warranty') ? 'nav-tab-active' : ''; ?>"><?php _e('smsBell', 'wc_warranty'); ?></a>
	<?php
	}

	public static function Websms_warranty_settings_panels()
	{
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : '';

		if ($active_tab == 'Websms_warranty') {
			include  plugin_dir_path(dirname(__FILE__)) . 'mod/forms/warranty-requests/Websms.php';
		}
	}
	//-/-warranty

	public static function show_admin_notice__success()
	{
	?>
		<div class="notice notice-warning is-dismissible">
			<p><a href="admin.php?page=wsw.lk"><?php _e('Login to smsBell', 'Websms'); ?></a> <?php _e('to configure SMS Notifications', 'Websms'); ?></p>
		</div>
	<?php
	}

	public static function get_wc_payment_dropdown($checkout_payment_plans)
	{
		if (!is_array($checkout_payment_plans))
			$checkout_payment_plans = self::get_all_gateways();

		$paymentPlans = WC()->payment_gateways->get_available_payment_gateways();
		echo '<select multiple size="5" name="Websms_general[checkout_payment_plans][]" id="checkout_payment_plans" class="multiselect chosen-select"  data-placeholder="Select Payment Gateways">';
		foreach ($paymentPlans as $paymentPlan) {
			echo '<option ';
			if (in_array($paymentPlan->id, $checkout_payment_plans)) echo 'selected';
			echo ' value="' . esc_attr($paymentPlan->id) . '">' . $paymentPlan->title . '</option>';
		}
		echo '</select>';
		echo '<script>jQuery(function() {jQuery(".chosen-select").chosen({width: "100%"});});</script>';
	}

	public static function get_wc_roles_dropdown($admin_bypass_otp_login)
	{
		global $wp_roles;
		$roles = $wp_roles->roles;
		if (!is_array($admin_bypass_otp_login) && $admin_bypass_otp_login == 'on')
			$admin_bypass_otp_login = array('administrator');


		echo '<select multiple size="5" name="Websms_general[admin_bypass_otp_login][]" id="admin_bypass_otp_login" class="multiselect chosen-select"  data-placeholder="Select Roles OTP For login">';
		foreach ($roles as $role_key => $role) {
			echo '<option ';
			if (in_array($role_key, $admin_bypass_otp_login)) echo 'selected';
			echo ' value="' . esc_attr($role_key) . '">' . $role['name'] . '</option>';
		}
		echo '</select>';
	}

	public static function get_country_code_dropdown()
	{
		$default_country_code = bellsms_get_option('default_country_code', 'Websms_general');
		$content = '<select name="Websms_general[default_country_code]" id="default_country_code" onchange="choseMobPattern(this)">';
		$content .= '<option value="" data-pattern="' . WebsmsConstants::PATTERN_PHONE . '" ' . (($default_country_code == '') ? 'selected="selected"' : '') . '>Global</option>';
		foreach (WebsmsCountryList::getCountryCodeList() as $key => $country) {
			$content .= '<option value="' . $country['Country']['c_code'] . '"';
			$content .= ($country['Country']['c_code'] == $default_country_code) ? 'selected="selected"' : '';
			$content .= ' data-pattern="' . (!empty($country['Country']['pattern']) ? $country['Country']['pattern'] : WebsmsConstants::PATTERN_PHONE) . '">' . $country['Country']['name'] . '</option>';
		}
		$content .= '</select>';
		return $content;
	}

	public static function get_all_gateways()
	{
		$gateways = array();
		$paymentPlans = WC()->payment_gateways->get_available_payment_gateways();
		foreach ($paymentPlans as $paymentPlan) {
			$gateways[] =  $paymentPlan->id;
		}
		return $gateways;
	}

	public static function isUserAuthorised()
	{
		$islogged = true;
		$bellsms_API_key = bellsms_get_option('bellsms_API_key', 'Websms_gateway', '');
		$Websms_API_Token     = bellsms_get_option('Websms_API_Token', 'Websms_gateway', '');
		$Websms_Sender_ID    = bellsms_get_option('Websms_Sender_ID', 'Websms_gateway', '');
		$islogged = true;
		if ($bellsms_API_key != '' && $Websms_API_Token != '') {
			$islogged = true;
		}
		return $islogged;
	}

	public static function Websms_add_dashboard_widgets($items = array())
	{
		if (self::isUserAuthorised()) {
			$credits = json_decode(WebsmscURLOTP::get_credits(), true);
			if (is_array($credits['description']) && array_key_exists('routes', $credits['description'])) {
				foreach ($credits['description']['routes'] as $credit) {
					$items[] = sprintf('<a href="%1$s" class="Websms-credit"><strong>%2$s SMS</strong> : %3$s</a>', admin_url('admin.php?page=wsw.lk'), ucwords($credit['route']), $credit['credits']) . '<br />';
				}
			}
		}
		return $items;
	}

	public static function logout()
	{
		if (delete_option('Websms_gateway'))
			return true;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses self::get_settings()
	 */
	public static function settings_tab()
	{
		//woocommerce_admin_fields( self::get_settings() ); 
		self::get_settings();
	}

	public static function save()
	{
		$_POST = bellsms_sanitize_array($_POST);
		self::save_settings($_POST);
	}

	public static function save_settings($options)
	{
		$order_statuses = is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();
		$wcbk_order_statuses = is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') ? WebsmsWcBooking::get_booking_statuses() : array();


		if (empty($_POST)) {
			return false;
		}



		$defaults = array(
			'Websms_gateway'       => array(
				'bellsms_API_key' => '',
				'Websms_API_Token'  => '',
				'Websms_api'  => '',
			),
			'Websms_message'       => array(
				'sms_admin_phone' => '',
				'group_auto_sync' => '',
				'sms_body_new_note'  => '',
				'sms_body_registration_msg'  => '',
				'sms_body_registration_admin_msg'  => '',
				'sms_otp_send'  => '',
			),
			'Websms_general'       => array(
				'buyer_checkout_otp' => 'off',
				'buyer_signup_otp' => 'off',
				'buyer_login_otp' => 'off',
				'buyer_notification_notes' => 'off',
				'allow_multiple_user' => 'off',
				'admin_bypass_otp_login' => array('administrator'),
				'checkout_show_otp_button' => 'off',
				'checkout_show_otp_guest_only' => 'off',
				'checkout_otp_popup' => 'off',
				'allow_query_sms' => 'on',
				'daily_bal_alert' => 'off',
				'enable_short_url' => 'off',
				'auto_sync' => 'off',
				'low_bal_alert' => 'off',
				'alert_email' => '',
				'checkout_payment_plans' => '',
				'otp_for_selected_gateways' => 'off',
				'otp_resend_timer' => '15',
				'max_otp_resend_allowed' => '4',
				'otp_verify_btn_text' => 'Click here to verify your Phone',
				'default_country_code' => '91',
				'sa_mobile_pattern' => '',
				'login_with_otp' => 'off',
				'validate_before_send_otp' => 'off',
				'registration_msg' => 'off',
				'admin_registration_msg' => 'off',
				'reset_password' => 'off',
				'register_otp_popup_enabled' => 'off',

			),


			'Websms_sync'       => array(
				'last_sync_userId' => '3'
			),
			'Websms_background_task'       => array(
				'last_updated_lBal_alert' => '',
			),
			'Websms_background_dBal_task'       => array(
				'last_updated_dBal_alert' => '',
			),
			'Websms_edd_general' => array(),
		);

		foreach ($order_statuses as $ks => $vs) {
			$prefix = 'wc-';
			if (substr($ks, 0, strlen($prefix)) == $prefix) {
				$ks = substr($ks, strlen($prefix));
			}
			$defaults['Websms_general']['admin_notification_' . $ks] = 'off';
			$defaults['Websms_general']['order_status'][$ks] = '';
			$defaults['Websms_message']['admin_sms_body_' . $ks] = '';
			$defaults['Websms_message']['sms_body_' . $ks] = '';
		}

		$edd_order_statuses = is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') ? edd_get_payment_statuses() : array();
		foreach ($edd_order_statuses as $ks => $vs) {
			$defaults['Websms_edd_general']['edd_admin_notification_' . $vs] = 'off';
			$defaults['Websms_edd_general']['edd_order_status_' . $vs] = 'off';
			$defaults['Websms_edd_message']['edd_admin_sms_body_' . $vs] = '';
			$defaults['Websms_edd_message']['edd_sms_body_' . $vs] = '';
		}

		foreach ($wcbk_order_statuses as $ks => $vs) {
			$defaults['Websms_wcbk_general']['wcbk_admin_notification_' . $vs] = 'off';
			$defaults['Websms_wcbk_general']['wcbk_order_status_' . $vs] = 'off';
			$defaults['Websms_wcbk_message']['wcbk_admin_sms_body_' . $vs] = '';
			$defaults['Websms_wcbk_message']['wcbk_sms_body_' . $vs] = '';
		}

		$defaults = apply_filters('sAlertDefaultSettings', $defaults); //added on 17-11-2018 uses affiliate-manager.php



		$_POST['Websms_general']['checkout_payment_plans'] = isset($_POST['Websms_general']['checkout_payment_plans']) ? maybe_serialize($_POST['Websms_general']['checkout_payment_plans']) : array();
		$options = array_replace_recursive($defaults, array_intersect_key($_POST, $defaults));

		foreach ($options as $name => $value) {
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if (!is_array($v)) {
						$value[$k] = stripcslashes($v);
					}
				}
			}
			update_option($name, $value);
		}
		//return true;
		wp_redirect(admin_url('admin.php?page=wsw.lk&m=1'));
		exit;
	}

	public static function getvariables()
	{
		$variables = array(
			'[order_id]' 				=> 'Order Id',
			'[order_status]' 			=> 'Order Status',
			'[order_amount]' 			=> 'Order amount',
			'[store_name]' 				=> 'Store Name',
			'[item_name]' 				=> 'Product Name',
			'[item_name_qty]' 			=> 'Product Name with Quantity',

			'[billing_first_name]' 		=> 'Billing First Name',
			'[billing_last_name]' 		=> 'Billing Last Name',
			'[billing_company]' 		=> 'Billing Company',
			'[billing_address_1]' 		=> 'Billing Address 1',
			'[billing_address_2]' 		=> 'Billing Address 2',
			'[billing_city]' 			=> 'Billing City',
			'[billing_state]' 			=> 'Billing State',
			'[billing_postcode]' 		=> 'Billing Postcode',
			'[billing_country]' 		=> 'Billing Country',
			'[billing_email]' 			=> 'Billing Email',
			'[billing_phone]' 			=> 'Billing Phone',

			'[shipping_first_name]'		=> 'Shipping First Name',
			'[shipping_last_name]' 		=> 'Shipping Last Name',
			'[shipping_company]' 		=> 'Shipping Company',
			'[shipping_address_1]' 		=> 'Shipping Address 1',
			'[shipping_address_2]' 		=> 'Shipping Address 2',
			'[shipping_city]' 			=> 'Shipping City',
			'[shipping_state]' 			=> 'Shipping State',
			'[shipping_postcode]' 		=> 'Shipping Postcode',
			'[shipping_country]' 		=> 'Shipping Country',

			'[order_currency]' 			=> 'Order Currency',
			'[payment_method]' 			=> 'Payment Method',
			'[payment_method_title]' 	=> 'Payment Method Title',
			'[shipping_method]' 		=> 'Shipping Method',
		);

		if (
			is_plugin_active('woocommerce-shipment-tracking/woocommerce-shipment-tracking.php') ||
			is_plugin_active('woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php')
		) {
			$wc_shipment_variables = array(
				'[tracking_number]' 		=> 'tracking number',
				'[tracking_provider]' 		=> 'tracking provider',
				'[tracking_link]' 			=> 'tracking link',
			);
			$variables = array_merge($variables, $wc_shipment_variables);
		}

		if (is_plugin_active('aftership-woocommerce-tracking/aftership.php')) {
			$wc_shipment_variables = array(
				'[aftership_tracking_number]' 		=> 'afshp tracking number',
				'[aftership_tracking_provider_name]' 		=> 'afshp tracking provider',
				//'[tracking_link]' 			=> 'tracking link',
			);
			$variables = array_merge($variables, $wc_shipment_variables);
		}

		if (is_plugin_active('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php')) {
			$wc_pdf_invoice = array(
				'[pdf_invoice_link]' 		=> 'pdf invoice link',
			);
			$variables = array_merge($variables, $wc_pdf_invoice);
		}

		if (is_plugin_active('claim-gst/claim-gst.php')) {
			$variables = array_merge($variables,  array(
				'[gstin]' => 'GST Number',
				'[gstin_holder_name]' => 'GST Holder Name',
				'[gstin_holder_address]' => 'GST Holder Address',
			));
		}

		$ret_string = '';
		foreach ($variables as $vk => $vv) {
			$ret_string .= sprintf("<a href='#' val='%s'>%s</a> | ", $vk, __($vv, WebsmsConstants::TEXT_DOMAIN));
		}
		return $ret_string;
	}

	/**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @return array Array of settings for @see woocommerce_admin_fields() function.
	 */
	public static function get_settings()
	{

		global $current_user;
		wp_get_current_user();

		$bellsms_API_key     			    	= bellsms_get_option('bellsms_API_key', 'Websms_gateway', '');
		$Websms_API_Token  			   	= bellsms_get_option('Websms_API_Token', 'Websms_gateway', '');
		$Websms_Sender_ID  			   	= bellsms_get_option('Websms_Sender_ID', 'Websms_gateway', '');
		$Websms_api   			       	= bellsms_get_option('Websms_api', 'Websms_gateway', '');
		$hasWoocommerce 					= is_plugin_active('woocommerce/woocommerce.php');
		$hasWPmembers 						= is_plugin_active('wp-members/wp-members.php');
		$hasUltimate 						= (is_plugin_active('ultimate-member/ultimate-member.php') || is_plugin_active('ultimate-member/index.php')) ? true : false;
		$hasWoocommerceBookings 			= (is_plugin_active('woocommerce-bookings/woocommerce-bookings.php')) ? true : false;
		$hasWPAM 							= (is_plugin_active('affiliates-manager/boot-strap.php')) ? true : false;
		$hasLearnPress 						= (is_plugin_active('learnpress/learnpress.php')) ? true : false;
		$sms_admin_phone 					= bellsms_get_option('sms_admin_phone', 'Websms_message', '');
		$group_auto_sync 					= bellsms_get_option('group_auto_sync', 'Websms_general', '');
		$sms_body_on_hold 					= bellsms_get_option('sms_body_on-hold', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_SMS_ON_HOLD);
		$sms_body_processing 				= bellsms_get_option('sms_body_processing', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_SMS_PROCESSING);
		$sms_body_completed 				= bellsms_get_option('sms_body_completed', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_SMS_COMPLETED);
		$sms_body_cancelled 				= bellsms_get_option('sms_body_cancelled', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_SMS_CANCELLED);
		$sms_body_new_note 					= bellsms_get_option('sms_body_new_note', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_NOTE);
		$sms_body_registration_msg 			= bellsms_get_option('sms_body_registration_msg', 'Websms_message', WebsmsMessages::DEFAULT_NEW_USER_REGISTER);
		$sms_body_registration_admin_msg 	= bellsms_get_option('sms_body_registration_admin_msg', 'Websms_message', WebsmsMessages::DEFAULT_ADMIN_NEW_USER_REGISTER);
		$sms_body_become_teacher_msg 		= bellsms_get_option('sms_body_become_teacher_msg', 'Websms_lpress_message', WebsmsMessages::DEFAULT_NEW_TEACHER_REGISTER);
		$sms_body_admin_become_teacher_msg 	= bellsms_get_option('sms_body_admin_become_teacher_msg', 'Websms_lpress_message', WebsmsMessages::DEFAULT_ADMIN_NEW_TEACHER_REGISTER);
		$sms_body_course_enroll_msg 		= bellsms_get_option('sms_body_course_enroll', 'Websms_lpress_message', WebsmsMessages::DEFAULT_USER_COURSE_ENROLL);
		$sms_body_course_enroll_admin_msg 	= bellsms_get_option('sms_body_course_enroll_admin_msg', 'Websms_lpress_message', WebsmsMessages::DEFAULT_ADMIN_COURSE_ENROLL);
		$sms_body_course_finished_msg 		= bellsms_get_option('sms_body_course_finished', 'Websms_lpress_message', WebsmsMessages::DEFAULT_USER_COURSE_FINISHED);
		$sms_body_course_finished_admin_msg = bellsms_get_option('sms_body_course_finished_admin_msg', 'Websms_lpress_message', WebsmsMessages::DEFAULT_ADMIN_COURSE_FINISHED);
		$sms_otp_send 						= bellsms_get_option('sms_otp_send', 'Websms_message', WebsmsMessages::DEFAULT_BUYER_OTP);
		$Websms_notification_status 		= bellsms_get_option('order_status', 'Websms_general', '');
		$Websms_notification_onhold 		= (is_array($Websms_notification_status) && array_key_exists('on-hold', $Websms_notification_status)) ? $Websms_notification_status['on-hold'] : 'on-hold';
		$Websms_notification_processing 	= (is_array($Websms_notification_status) && array_key_exists('processing', $Websms_notification_status)) ? $Websms_notification_status['processing'] : 'processing';
		$Websms_notification_completed 	= (is_array($Websms_notification_status) && array_key_exists('completed', $Websms_notification_status)) ? $Websms_notification_status['completed'] : 'completed';
		$Websms_notification_cancelled 	= (is_array($Websms_notification_status) && array_key_exists('cancelled', $Websms_notification_status)) ? $Websms_notification_status['cancelled'] : 'cancelled';
		$Websms_notification_checkout_otp = bellsms_get_option('buyer_checkout_otp', 'Websms_general', 'on');
		$Websms_notification_signup_otp 	= bellsms_get_option('buyer_signup_otp', 'Websms_general', 'on');
		$Websms_notification_login_otp 	= bellsms_get_option('buyer_login_otp', 'Websms_general', 'on');
		$Websms_notification_notes 		= bellsms_get_option('buyer_notification_notes', 'Websms_general', 'on');
		$Websms_notification_reg_msg 		= bellsms_get_option('registration_msg', 'Websms_general', 'on');
		$Websms_notification_reg_admin_msg = bellsms_get_option('admin_registration_msg', 'Websms_general', 'on');
		$become_teacher 					= bellsms_get_option('become_teacher', 'Websms_lpress_general', 'on');
		$admin_become_teacher 				= bellsms_get_option('admin_become_teacher', 'Websms_lpress_general', 'on');
		$student_notification_course_enroll = bellsms_get_option('course_enroll', 'Websms_lpress_general', 'on');
		$admin_notification_course_enroll 	= bellsms_get_option('admin_course_enroll', 'Websms_lpress_general', 'on');
		$student_notification_course_finished = bellsms_get_option('course_finished', 'Websms_lpress_general', 'on');
		$admin_notification_course_finished = bellsms_get_option('admin_course_finished', 'Websms_lpress_general', 'on');
		$Websms_allow_multiple_user 		= bellsms_get_option('allow_multiple_user', 'Websms_general', 'on');
		$admin_bypass_otp_login 			= maybe_unserialize(bellsms_get_option('admin_bypass_otp_login', 'Websms_general', array('administrator')));
		$checkout_show_otp_button 			= bellsms_get_option('checkout_show_otp_button', 'Websms_general', 'on');
		$checkout_show_otp_guest_only 		= bellsms_get_option('checkout_show_otp_guest_only', 'Websms_general', 'on');
		$enable_reset_password 				= bellsms_get_option('reset_password', 'Websms_general', 'off');
		$register_otp_popup_enabled 		= bellsms_get_option('register_otp_popup_enabled', 'Websms_general', 'off');
		$otp_resend_timer 					= bellsms_get_option('otp_resend_timer', 'Websms_general', '15');
		$max_otp_resend_allowed 			= bellsms_get_option('max_otp_resend_allowed', 'Websms_general', '4');
		$otp_verify_btn_text 				= bellsms_get_option('otp_verify_btn_text', 'Websms_general', 'Click here to verify your Phone');
		$default_country_code 				= bellsms_get_option('default_country_code', 'Websms_general', '');
		$sa_mobile_pattern 					= bellsms_get_option('sa_mobile_pattern', 'Websms_general', '');
		$checkout_otp_popup 				= bellsms_get_option('checkout_otp_popup', 'Websms_general', 'on');
		$login_with_otp 					= bellsms_get_option('login_with_otp', 'Websms_general', 'off');
		$Websms_allow_query_sms 			= bellsms_get_option('allow_query_sms', 'Websms_general', 'on');
		$daily_bal_alert 					= bellsms_get_option('daily_bal_alert', 'Websms_general', 'on');
		$enable_short_url 					= bellsms_get_option('enable_short_url', 'Websms_general', 'off');
		$auto_sync 							= bellsms_get_option('auto_sync', 'Websms_general', 'off');
		$low_bal_alert 						= bellsms_get_option('low_bal_alert', 'Websms_general', 'on');
		$low_bal_val 						= bellsms_get_option('low_bal_val', 'Websms_general', '1000');
		$alert_email 						= bellsms_get_option('alert_email', 'Websms_general', $current_user->user_email);
		$validate_before_send_otp			= bellsms_get_option('validate_before_send_otp', 'Websms_general', 'off');
		$checkout_payment_plans 			= maybe_unserialize(bellsms_get_option('checkout_payment_plans', 'Websms_general', NULL));
		$otp_for_selected_gateways 			= bellsms_get_option('otp_for_selected_gateways', 'Websms_general', 'off');
		$islogged 							= false;
		$hidden								= '';
		$credit_show						= 'hidden';
		if ($bellsms_API_key != '' && $Websms_API_Token != '') {
			$credits = json_decode(WebsmscURLOTP::get_credits(), true);
			if ($credits['status'] == 'success' || (is_array($credits['description']) && $credits['description']['desc'] == 'no senderid available for your account')) {
				$islogged = true;
				$hidden = 'hidden';
				$credit_show = '';
			}
		}

		$Websms_helper = ""
	?>
		<a href="https://wsw.lk/plugin">Welcome to WSW WebSMS WordPress Plugin</a>
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
			<div class="Websms_box Websms_settings_box" style="margin-top:30px;">
				<div class="Websms_nav_tabs">
					<?php

					$params = array(
						'hasWoocommerce' => $hasWoocommerce,
						'hasWPmembers' => $hasWPmembers,
						'hasUltimate' => $hasUltimate,
						'hasWPAM' => $hasWPAM,
						'credit_show' => $credit_show,
					);


					echo get_bellsms_template('views/websms_nav_tabs.php', $params);
					?>
				</div>
				<div>
					<div class="Websms_nav_box Websms_nav_global_box Websms_active general">
						<!--general tab-->
						<?php
						$params = array(
							'Websms_helper' => $Websms_helper,
							'bellsms_API_key' => $bellsms_API_key,
							'Websms_API_Token' => $Websms_API_Token,
							'Websms_Sender_ID' => $Websms_Sender_ID,
							'hidden' => $hidden,
							'Websms_api' => $Websms_api,
							'islogged' => $islogged,
						);
						echo get_bellsms_template('views/websms_general_tab.php', $params);
						?>
					</div>
					<!--/-general tab-->

					<div class="Websms_nav_box Websms_nav_css_box customertemplates">
						<!--customertemplates tab-->

						<?php
						$order_statuses = is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();
						?>
						<?php
						$params = array(
							'order_statuses' => $order_statuses,
							'Websms_notification_status' => $Websms_notification_status,
							'getvariables' => self::getvariables(),
							'hasWoocommerce' => $hasWoocommerce,
							'Websms_notification_notes' => $Websms_notification_notes,
							'Websms_notification_reg_msg' => $Websms_notification_reg_msg,
							'sms_body_new_note' => $sms_body_new_note,
							'sms_body_registration_msg' => $sms_body_registration_msg,
							'sms_body_registration_admin_msg' => $sms_body_registration_admin_msg,
							'Websms_notification_checkout_otp' => $Websms_notification_checkout_otp,
							'Websms_notification_signup_otp' => $Websms_notification_signup_otp,
							'Websms_notification_login_otp' => $Websms_notification_login_otp,
							'hasWPmembers' => $hasWPmembers,
							'hasUltimate' => $hasUltimate,
							'hasWPAM' => $hasWPAM,
							'sms_otp_send' => $sms_otp_send,
							'login_with_otp' => $login_with_otp,
							'enable_reset_password' => $enable_reset_password,
							'hasLearnPress' => $hasLearnPress,
						);

						echo get_bellsms_template('views/wc-customer-template.php', $params);
						?>


					</div>
					<!--/-customertemplates tab-->

					<div class="Websms_nav_box Websms_nav_admintemplates_box admintemplates">
						<!--admintemplates tab-->
						<?php
						$params = array(
							'order_statuses' => $order_statuses,
							'hasWoocommerce' => $hasWoocommerce,
							'Websms_notification_reg_admin_msg' => $Websms_notification_reg_admin_msg,
							'sms_body_registration_admin_msg' => $sms_body_registration_admin_msg,
							'getvariables' => self::getvariables(),
						);
						echo get_bellsms_template('views/wc-admin-template.php', $params);
						?>
					</div>
					<!--/-admintemplates tab-->

					<!--Edd download customer templates-->
					<div class="Websms_nav_box Websms_nav_eddcsttemplates_box eddcsttemplates">
						<?php
						$edd_order_statuses = is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') ? edd_get_payment_statuses() : array();
						?>
						<?php
						$params = array('edd_order_statuses' => $edd_order_statuses);
						echo get_bellsms_template('views/edd_customer_template.php', $params);
						?>
					</div>
					<!--/--Edd download customer templates-->

					<!--EDD admintemplates tab-->
					<div class="Websms_nav_box Websms_nav_eddadmintemplates_box eddadmintemplates">
						<!-- Admin-accordion -->
						<?php
						$params = array('edd_order_statuses' => $edd_order_statuses);
						echo get_bellsms_template('views/edd_admin_template.php', $params);
						?>
					</div>
					<!--/-EDD admintemplates tab-->

					<!--Woocommerce Booking tabs-->
					<?php if ($hasWoocommerceBookings) { ?>
						<?php $wcbk_order_statuses = WebsmsWcBooking::get_booking_statuses();
						?>
						<!--Woocommerce Booking Customer templates-->

						<div class="Websms_nav_box Websms_nav_wcbkcsttemplates_box wcbkcsttemplates">

							<?php
							$params = array('wcbk_order_statuses' => $wcbk_order_statuses);
							echo get_bellsms_template('views/booking_customer_template.php', $params);
							?>
						</div>
						<!--/--Woocommerce Booking Customer templates-->
						<!--Woocommerce Booking Admin templates-->

						<div class="Websms_nav_box Websms_nav_wcbkadmintemplates_box wcbkadmintemplates">
							<?php
							$params = array('wcbk_order_statuses' => $wcbk_order_statuses);
							echo get_bellsms_template('views/booking_admin_template.php', $params);
							?>
						</div>
						<!--/--Woocommerce Booking Admin templates-->
					<?php } ?>
					<!--/-Woocommerce Booking tabs-->

					<!--Wp Affiliate Manager tabs-->
					<?php if ($hasWPAM) { ?>
						<?php
						$wpam_statuses = AffiliateManagerForm::get_affiliate_statuses();
						$wpam_transaction = AffiliateManagerForm::get_affiliate_transaction();
						$params = array(
							'wpam_statuses' => $wpam_statuses,
							'wpam_transaction' => $wpam_transaction,
						);
						?>
						<!--Wp Affiliate Manager Customer templates-->

						<div class="Websms_nav_box Websms_nav_wpamcsttemplates_box wpamcsttemplates">

							<?php
							echo get_bellsms_template('views/affiliate_customer_template.php', $params);
							?>
						</div>
						<!--/--Wp Affiliate Manager Customer templates-->
						<!--Wp Affiliate Manager Admin templates-->

						<div class="Websms_nav_box Websms_nav_wpamadmintemplates_box wpamadmintemplates">
							<?php
							echo get_bellsms_template('views/affiliate_admin_template.php', $params);
							?>
						</div>
						<!--/--Wp Affiliate Manager Admin templates-->
					<?php } ?>
					<!--/-Wp Affiliate Manager tabs-->
					<!--Learnpress tabs-->
					<?php if ($hasLearnPress) { ?>
						<?php
						$lpress_statuses = WebsmsLearnPress::get_learnpress_status();
						$params = array(
							'lpress_statuses' => $lpress_statuses,
							'student_notification_course_enroll' => $student_notification_course_enroll,
							'sms_body_course_enroll_msg' => $sms_body_course_enroll_msg,
							'admin_notification_course_enroll' => $admin_notification_course_enroll,
							'sms_body_course_enroll_admin_msg' => $sms_body_course_enroll_admin_msg,
							'student_notification_course_finished' => $student_notification_course_finished,
							'sms_body_course_finished_msg' => $sms_body_course_finished_msg,
							'admin_notification_course_finished' => $admin_notification_course_finished,
							'sms_body_course_finished_admin_msg' => $sms_body_course_finished_admin_msg,
							'become_teacher' => $become_teacher,
							'sms_body_become_teacher_msg' => $sms_body_become_teacher_msg,
							'admin_become_teacher' => $admin_become_teacher,
							'sms_body_admin_become_teacher_msg' => $sms_body_admin_become_teacher_msg,
						);
						?>
						<!--Learnpress Customer templates-->

						<div class="Websms_nav_box Websms_nav_lpresscsttemplates_box lpresscsttemplates">

							<?php
							echo get_bellsms_template('views/lpress_customer_template.php', $params);
							?>
						</div>
						<!--/--Learnpress Customer templates-->
						<!--Learnpress Admin templates-->

						<div class="Websms_nav_box Websms_nav_lpressadmintemplates_box lpressadmintemplates">
							<?php
							echo get_bellsms_template('views/lpress_admin_template.php', $params);
							?>
						</div>
						<!--/--Learnpress Admin templates-->
					<?php } ?>
					<!--/-Learnpress tabs-->
					<div class="Websms_nav_box Websms_nav_callbacks_box otp">
						<!--otp tab-->
						<style>
							.top-border {
								border-top: 1px dashed #b4b9be;
							}
						</style>
						<table class="form-table">
							<?php
							if ($hasWoocommerce) {
							?>
								<?php
								/* 
				<tr valign="top">
						<th scrope="row"><input type="checkbox" name="Websms_general[otp_for_selected_gateways]" id="Websms_general[otp_for_selected_gateways]" class="notify_box" <?php echo (($otp_for_selected_gateways=='on')?"checked='checked'":'')?>  onclick="toggleDisabled(this)"/><?php  _e( 'Enable OTP for Selected Payment Gateways', WebsmsConstants::TEXT_DOMAIN ) ?>
						<?php ?>
						<span class="tooltip" data-title="Please select payment gateway for which you wish to enable OTP Verification"><span class="dashicons dashicons-info"></span></span>
						</th>
						<td>
						<?php
						if($hasWoocommerce){
							self::get_wc_payment_dropdown($checkout_payment_plans); 
						}
						?>
						</td>
				</tr>*/ ?>
							<?php } ?>

							<?php if ($hasWoocommerce || $hasWPAM) { ?>
								<tr valign="top">
									<th scrope="row"><?php _e('Send Admin SMS To', WebsmsConstants::TEXT_DOMAIN) ?>
										<span class="tooltip" data-title="Please make sure that the number must be without country code (e.g.: 8010551055)"><span class="dashicons dashicons-info"></span></span>
									</th>
									<td>
										<select id="send_admin_sms_to" onchange="toggle_send_admin_alert(this);">
											<option value="">Custom</option>
											<option value="post_author" <?php echo (trim($sms_admin_phone) == 'post_author') ? 'selected="selected"' : ''; ?>>Post Author</option>
										</select>
										<script>
											function toggle_send_admin_alert(obj) {
												var value = jQuery(obj).val();
												jQuery('.admin_no').val(value);
												if (value == 'post_author')
													jQuery('.admin_no').attr('readonly', 'readonly');
												else
													jQuery('.admin_no').removeAttr('readonly');
											}
										</script>
										<input type="text" name="Websms_message[sms_admin_phone]" class="admin_no" id="Websms_message[sms_admin_phone]" <?php echo (trim($sms_admin_phone) == 'post_author') ? 'readonly="readonly"' : ''; ?> value="<?php echo $sms_admin_phone; ?>">
										<br /><span><?php _e('Admin order sms notifications will be send in this number.', WebsmsConstants::TEXT_DOMAIN); ?></span>
									</td>
								</tr>
							<?php } ?>
							<?php
							if ($hasWoocommerce) {
								/*
				<tr valign="top" class="top-border">
						<th scrope="row"><?php _e( 'OTP Settings', WebsmsConstants::TEXT_DOMAIN ); ?>
						</th>
						<td>
						<input type="checkbox" name="Websms_general[checkout_otp_popup]" id="Websms_general[checkout_otp_popup]" class="notify_box" <?php echo (($checkout_otp_popup=='on')?"checked='checked'":'')?>/><?php _e( 'Verify OTP in Popup', WebsmsConstants::TEXT_DOMAIN ) ?>
						<span class="tooltip" data-title="Verify OTP in Popup"><span class="dashicons dashicons-info"></span></span>
						</td>
				</tr>
				
				<tr valign="top">
						<th scrope="row">
						</th>
						<td>
						<input type="checkbox" name="Websms_general[register_otp_popup_enabled]" id="Websms_general[register_otp_popup_enabled]" class="notify_box" <?php echo (($register_otp_popup_enabled=='on')?"checked='checked'":'')?>/><?php _e( 'Register OTP in Popup', WebsmsConstants::TEXT_DOMAIN ) ?>
						<span class="tooltip" data-title="Register OTP in Popup"><span class="dashicons dashicons-info"></span></span>
						</td>
				</tr>
				<tr valign="top">
						<th scrope="row">
						
						<?php _e( 'Exclude Role from LOGIN OTP', WebsmsConstants::TEXT_DOMAIN ) ?>
						<span class="tooltip" data-title="Exclude Role from LOGIN OTP"><span class="dashicons dashicons-info"></span></span>
						</th>
						<td>
						<?php echo self::get_wc_roles_dropdown($admin_bypass_otp_login);?>
						
						
						</td>
				</tr>
				
				<tr valign="top">
						<th scrope="row">
						</th>
						<td>
						<input type="checkbox" name="Websms_general[checkout_show_otp_button]" id="Websms_general[checkout_show_otp_button]" class="notify_box" <?php echo (($checkout_show_otp_button=='on')?"checked='checked'":'')?>/><?php _e( 'Show Verify Button at Checkout', WebsmsConstants::TEXT_DOMAIN ) ?>
						<span class="tooltip" data-title="Show verify button in-place of link at checkout"><span class="dashicons dashicons-info"></span></span>
						</td>
				</tr>
				<tr valign="top">
						<th scrope="row">
						</th>
						<td>
						<input type="checkbox" name="Websms_general[checkout_show_otp_guest_only]" id="Websms_general[checkout_show_otp_guest_only]" class="notify_box" <?php echo (($checkout_show_otp_guest_only=='on')?"checked='checked'":'')?>/><?php _e( 'Verify only Guest Checkout', WebsmsConstants::TEXT_DOMAIN ) ?>
						<span class="tooltip" data-title="OTP verification only for guest checkout"><span class="dashicons dashicons-info"></span></span>
						</td>
				</tr>
				*/
							?>
								<tr valign="top">
									<th scrope="row">
									</th>
									<td>
										<?php _e('OTP Re-send Timer', WebsmsConstants::TEXT_DOMAIN) ?>
										<input type="number" name="Websms_general[otp_resend_timer]" id="Websms_general[otp_resend_timer]" class="notify_box" value="<?php echo $otp_resend_timer; ?>" min="15" max="300" /> Sec
										<span class="tooltip" data-title="Set OTP Re-send Timer"><span class="dashicons dashicons-info"></span></span>
									</td>
								</tr>

								<tr valign="top">
									<th scrope="row">
									</th>
									<td>
										<?php _e('Max OTP Re-send Allowed', WebsmsConstants::TEXT_DOMAIN) ?>
										<input type="number" name="Websms_general[max_otp_resend_allowed]" id="Websms_general[max_otp_resend_allowed]" class="notify_box" value="<?php echo $max_otp_resend_allowed; ?>" min="1" max="10" /> Times
										<span class="tooltip" data-title="Set MAX OTP Re-send Allowed"><span class="dashicons dashicons-info"></span></span>
									</td>
								</tr>


								<tr valign="top">
									<th scrope="row">
									</th>
									<td>
										<?php _e('OTP Verify Button Text', WebsmsConstants::TEXT_DOMAIN) ?>
										<input type="text" name="Websms_general[otp_verify_btn_text]" id="Websms_general[otp_verify_btn_text]" class="notify_box" value="<?php echo $otp_verify_btn_text; ?>" required />
										<span class="tooltip" data-title="Set OTP Verify Button Text"><span class="dashicons dashicons-info"></span></span>

									</td>
								</tr>
							<?php
							}
							?>
							<?php
							if ($hasWoocommerce || $hasUltimate || $hasWPAM) {
							?>
								<!--Validate Before Sending OTP-->
								<tr valign="top">
									<th scrope="row">
									</th>
									<td>
										<input type="checkbox" name="Websms_general[validate_before_send_otp]" id="Websms_general[validate_before_send_otp]" class="notify_box" <?php echo (($validate_before_send_otp == 'on') ? "checked='checked'" : '') ?> /><?php _e('Validate Before Sending OTP At Checkout', WebsmsConstants::TEXT_DOMAIN) ?>
										<span class="tooltip" data-title="Validate Before Sending OTP"><span class="dashicons dashicons-info"></span></span>
									</td>
								</tr>
								<!--/-Validate Before Sending OTP-->

							<?php
							}
							?>
							<?php
							if (is_plugin_active('woocommerce/woocommerce.php')) {
							?>
								<tr valign="top" class="top-border">
									<th scrope="row"><?php _e('Miscellaneous', WebsmsConstants::TEXT_DOMAIN) ?>
									</th>
									<td>
										<input type="checkbox" name="Websms_general[allow_multiple_user]" id="Websms_general[allow_multiple_user]" class="notify_box" <?php echo (($Websms_allow_multiple_user == 'on') ? "checked='checked'" : '') ?> /><?php _e('Allow multiple accounts with same mobile number', WebsmsConstants::TEXT_DOMAIN) ?>
										<span class="tooltip" data-title="OTP at registration should be active"><span class="dashicons dashicons-info"></span></span>
									</td>

								</tr>
							<?php
							}
							?>
							<!--integration for contact form 7 -->
							<?php
							if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
							?>
								<tr valign="top">
									<th scrope="row"> <?php _e('Enable ContactForm7', WebsmsConstants::TEXT_DOMAIN) ?>
									</th>
									<td>
										<input type="checkbox" name="Websms_general[allow_query_sms]" id="Websms_general[allow_query_sms]" class="notify_box" <?php
																																								echo (($Websms_allow_query_sms == 'on') ? "checked='checked'" : '');
																																								?> /><?php _e('ContactForm 7 Integration', WebsmsConstants::TEXT_DOMAIN) ?>
										<span class="tooltip" data-title="Enable SMS for Contact Form 7"><span class="dashicons dashicons-info"></span></span>
									</td>
								</tr>
							<?php } ?>
							<!--/-integration for contact form 7 -->

							<tr valign="top">
								<th scrope="row"><?php _e('Default Country', WebsmsConstants::TEXT_DOMAIN) ?>
								</th>
								<td>
									<?php echo self::get_country_code_dropdown(); ?>
									<span class="tooltip" data-title="Default Country for mobile number format validation"><span class="dashicons dashicons-info"></span></span>
									<input type="hidden" name="Websms_general[sa_mobile_pattern]" id="sa_mobile_pattern" value="<?php echo $sa_mobile_pattern; ?>" />

								</td>
							</tr>
							<tr valign="top" class="top-border">
								<th scrope="row"><?php _e('Alerts', WebsmsConstants::TEXT_DOMAIN) ?>
								</th>
								<td>
									<input type="text" name="Websms_general[alert_email]" class="admin_email" id="Websms_general[alert_email]" value="<?php echo $alert_email; ?>"> <?php _e('send alerts to this email id', WebsmsConstants::TEXT_DOMAIN) ?>

									<span class="tooltip" data-title="Send Alerts for low balance & daily balance etc."><span class="dashicons dashicons-info"></span></span>
								</td>

							</tr>
							<tr valign="top">
								<th scrope="row">
								</th>
								<td>
									<input type="checkbox" name="Websms_general[low_bal_alert]" id="Websms_general[low_bal_alert]" class="Websms_box notify_box" <?php echo (($low_bal_alert == 'on') ? "checked='checked'" : ''); ?> onchange="toggleReadonly(this, 'input[type=number]')" /> <?php _e('Low Balance Alert', WebsmsConstants::TEXT_DOMAIN) ?> <input type="number" min="500" name="Websms_general[low_bal_val]" id="Websms_general[low_bal_val]" value="<?php echo $low_bal_val; ?>">
									<span class="tooltip" data-title="Set Low Balance Alert"><span class="dashicons dashicons-info"></span></span>
								</td>
							</tr>


							<tr valign="top">
								<th scrope="row">
								</th>
								<td>
									<input type="checkbox" name="Websms_general[daily_bal_alert]" id="Websms_general[daily_bal_alert]" class="notify_box" <?php echo (($daily_bal_alert == 'on') ? "checked='checked'" : ''); ?> /><?php _e('Daily Balance Alert', WebsmsConstants::TEXT_DOMAIN) ?>
									<span class="tooltip" data-title="Set Daily Balance Alert"><span class="dashicons dashicons-info"></span></span>
								</td>
							</tr>
							<!--enable shorturl-->
							<tr valign="top">
								<th scrope="row">
								</th>
								<td>
									<input type="checkbox" name="Websms_general[enable_short_url]" id="Websms_general[enable_short_url]" class="notify_box" <?php echo (($enable_short_url == 'on') ? "checked='checked'" : ''); ?> /><?php _e('Enable Short Url', WebsmsConstants::TEXT_DOMAIN) ?>
									<span class="tooltip" data-title="Enable Short Url"><span class="dashicons dashicons-info"></span></span>
								</td>
							</tr>
							<!--/-enable shorturl-->
							<?php
							if (is_plugin_active('woocommerce/woocommerce.php')) {
							?>
								<tr valign="top">
									<th scrope="row">
									</th>
									<td>

										<input type="checkbox" name="Websms_general[auto_sync]" id="Websms_general[auto_sync]" class="Websms_box sync_group" <?php echo (($auto_sync == 'on') ? "checked='checked'" : ''); ?> onchange="toggleDisabled(this)" /> <?php _e('Sync To Group', WebsmsConstants::TEXT_DOMAIN) ?>
										<?php
										$groups = json_decode(WebsmscURLOTP::group_list(), true);
										?>



										<select name="Websms_general[group_auto_sync]" id="group_auto_sync">

											<?php
											if (!is_array($groups['description']) || array_key_exists('desc', $groups['description'])) {
											?>
												<option value="0">SELECT</option>
												<?php
											} else {
												foreach ($groups['description'] as $group) {
												?>
													<option value="<?php echo $group['Group']['name']; ?>" <?php echo (trim($group_auto_sync) == $group['Group']['name']) ? 'selected="selected"' : ''; ?>><?php echo $group['Group']['name']; ?></option>
											<?php
												}
											}
											?>
										</select>
										<?php
										if ((!is_array($groups['description']) || array_key_exists('desc', $groups['description'])) && $islogged == true) {
										?>
											<a href="javascript:void(0)" onclick="create_group(this);" id="create_group" style="text-decoration: none;">Create Group </a>
										<?php
										}
										?>
										<span class="tooltip" data-title="Sync users to a Group in Websms.co.in"><span class="dashicons dashicons-info"></span></span>
									</td>
								</tr>
							<?php
							}
							?>

						</table>
					</div>
					<!--/-otp tab-->
					<div class="Websms_nav_box Websms_nav_credits_box credits <?php echo $credit_show ?>">
						<!--credit tab-->
						<table class="form-table">
							<tr valign="top">
								<td>
									<?php
									echo '<h2><strong>SMS Credits</strong></h2>';
									foreach ($credits['description']['routes'] as $credit) { ?>
										<div class="col-lg-12 creditlist">
											<div class="col-lg-8 route">
												<h3><?php echo ucwords($credit['route']); ?></h3>
											</div>
											<div class="col-lg-4 credit">
												<h3><?php echo $credit['credits']; ?> Credits</h3>
											</div>
										</div>
									<?php

									}
									?>
								</td>
							</tr>
							<tr valign="top">
								<td>
									<p><b>Need More credits?</b> <a href="http://www.Websms.co.in/#pricebox" target="_blank">Click Here</a> to purchase.</p>
								</td>
							</tr>
						</table>

					</div>
					<!--/-credit tab-->

					<div class="Websms_nav_box Websms_nav_support_box support">
						<!--support tab-->
						<table class="form-table">
							<tr valign="top">
								<td>
									<h2><?php _e('We would be glad to help you. You can reach us through any of the three ways.', WebsmsConstants::TEXT_DOMAIN) ?></h2>
								</td>
							</tr>
							<tr valign="top">
								<td>
									<div class="col-lg-12 creditlist">
										<div class="col-lg-8 route">
											<h3><a href="https://wsw.lk/contact.html" target="_blank">Our Support Center</a>
											</h3>
										</div>
									</div>
								</td>
							</tr>
							<tr valign="top">
								<td>
									<div class="col-lg-12 creditlist">
										<div class="col-lg-8 route">
											<h3><?php _e('Email Support', WebsmsConstants::TEXT_DOMAIN) ?>: <a href="mailto:support@wsw.lk" target="_blank">support@wsw.lk</a></h3>
										</div>
									</div>
								</td>
							</tr>


						</table>
					</div>
					<!--/-support tab-->

					<script>
						function insertAtCaret(textFeildValue, txtbox_id) {
							var textObj = document.getElementById(txtbox_id);
							if (document.all) {
								if (textObj.createTextRange && textObj.caretPos) {
									var caretPos = textObj.caretPos;
									caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? textFeildValue + ' ' : textFeildValue;
								} else {
									textObj.value = textObj.value + textFeildValue;
								}
							} else {
								if (textObj.setSelectionRange) {
									var rangeStart = textObj.selectionStart;
									var rangeEnd = textObj.selectionEnd;
									var tempStr1 = textObj.value.substring(0, rangeStart);
									var tempStr2 = textObj.value.substring(rangeEnd);

									textObj.value = tempStr1 + textFeildValue + tempStr2;
								} else {
									alert("This version of Mozilla based browser does not support setSelectionRange");
								}
							}
						}
						jQuery(document).ready(function() {
							function close_accordion_section() {
								jQuery('.cvt-accordion .expand_btn').removeClass('active');
								jQuery('.cvt-accordion .cvt-accordion-body-content').slideUp(300).removeClass('open');
							}

							jQuery('.expand_btn').click(function(e) {
								var currentAttrValue = jQuery(this).parent().attr('data-href');
								if (jQuery(e.target).is('.active')) {
									close_accordion_section();
								} else {
									close_accordion_section();
									jQuery(this).addClass('active');
									jQuery('.cvt-accordion ' + currentAttrValue).slideDown(300).addClass('open');
								}

								e.preventDefault();
							});

							jQuery('.Websms_tokens a').click(function() {
								insertAtCaret(jQuery(this).attr('val'), jQuery(this).parents('td').find('textarea').attr('id'));
								return false;
							});
						});

						//checkbox click function
						jQuery('.cvt-accordion-body-title input[type="checkbox"]').click(function(e) {

							var childdiv = jQuery(this).parent().attr('data-href'); //if child div have multiple checkbox

							if (!jQuery(this).is(':checked')) {
								//select all child div checkbox
								jQuery(childdiv).find('.notify_box').each(function() {
									this.checked = false;
								});

								jQuery(this).parent().find('.expand_btn.active').trigger('click'); //expand accordion

							} else {
								//uncheck all child  div checkbox
								jQuery(childdiv).find('.notify_box').each(function() {
									this.checked = true;
								});

								jQuery(this).parent().find('.expand_btn').not('.active').trigger('click'); //expand accordion

							}
						});

						// on checkbox toggle readonly input
						function toggleReadonly(obj, type) {

							for (var e = jQuery('.Websms_box input[type="checkbox"]').length, t = 0; e > t; t++)
								jQuery('.Websms_box input[type="checkbox"]').eq(t).is(":checked") === !1 ? jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find(type).attr("readonly", !0) : jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find(type).removeAttr("readonly");
						}

						// on checkbox enable-disable select 	
						function toggleDisabled(obj) {

							for (var e = jQuery('.Websms_box input[type="checkbox"]').length, t = 0; e > t; t++)
								if (jQuery('.Websms_box input[type="checkbox"]').eq(t).is(":checked") === !1) {

									//make disabled
									jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find("select").attr("disabled", !0); //for select
									jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find("#create_group").addClass("anchordisabled"); //for anchor
								}
							else {
								//remove disabled
								jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find("select").removeAttr("disabled"); //for select
								jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find("#create_group").removeClass("anchordisabled"); //for anchor
								jQuery(".chosen-select").trigger("chosen:updated");
							}

							/*jQuery('.Websms_box input[type="checkbox"]').eq(t).is(":checked") === !1 ? jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find("select").attr("disabled", !0) : jQuery('.Websms_box input[type="checkbox"]').eq(t).parent().parent().find("select").removeAttr("disabled"); */
						}

						toggleReadonly(jQuery('.Websms_box input[type="checkbox"]'), 'input[type="number"]'); //init on input type number
						toggleDisabled(jQuery('.Websms_box select')); //init on select


						function choseMobPattern(obj) {
							var pattern = jQuery('option:selected', obj).attr('data-pattern');
							jQuery('#sa_mobile_pattern').val(pattern);
						}
						//geo ip to country code
						<?php
						if (!$islogged) { ?>
							try {
								jQuery.get("https://ipapi.co/json/", function(data, status) {
									if (status == 'success')
										calling_code = data.country_calling_code.replace(/\+/g, '');
									else {
										calling_code = 91;
									}
									jQuery('#default_country_code').val(calling_code);
								}).fail(function() {
									console.log("ip check url is not working");
									jQuery('#default_country_code').val(91);
								});
							} catch (e) {
								jQuery('#default_country_code').val(91);
							}
						<?php } ?>
						//geo ip to country code ends
						jQuery('#default_country_code').trigger('change');
					</script>

				</div>
			</div>
			<?php //submit_button(); 
			?>
			<p class="submit"><input type="submit" id="Websms_bckendform_btn" class="button button-primary" value="Save Changes" /></p>
		</form>
		<script>
			jQuery('#Websms_bckendform_btn').click(function() {
				if (jQuery('[name="Websms_gateway[Websms_api]"]').val() == 'SELECT' || jQuery('[name="Websms_gateway[Websms_api]"]').val() == '') {
					alert('Please choose your senderid.');
					return false;
				}
				jQuery('form').submit();
			});
		</script>
	<?php
		return apply_filters('wc_websms_lk_setting', array());
	}

	public static function action_woocommerce_admin_field_verify_websms_lk_user($value)
	{
		global $current_user;
		wp_get_current_user();
		$bellsms_API_key         = bellsms_get_option('bellsms_API_key', 'Websms_gateway', '');
		$Websms_API_Token     = bellsms_get_option('Websms_API_Token', 'Websms_gateway', '');
		$Websms_Sender_ID     = bellsms_get_option('Websms_Sender_ID', 'Websms_gateway', '');
		$hidden = '';
		if ($bellsms_API_key != '' && $Websms_API_Token != '') {
			$credits = json_decode(WebsmscURLOTP::get_credits(), true);
			if ($credits['status'] == 'success' || (is_array($credits['description']) && $credits['description']['desc'] == 'no senderid available for your account')) {
				$hidden = 'hidden';
			}
		}
	?>
		<tr valign="top" class="<?php echo $hidden ?>">
			<th>&nbsp;</th>
			<td>
				<a href="#" class="button-primary woocommerce-save-button" onclick="verifyUser(this); return false;">verify and continue</a>
				Don't have an account on smsBell? <a href="http://www.Websms.co.in/?name=<?php echo urlencode($current_user->user_firstname . ' ' . $current_user->user_lastname); ?>&email=<?php echo urlencode($current_user->user_email); ?>&phone=&username=<?php echo str_replace(' ', '_', strtolower(get_bloginfo())); ?>#register" target="_blank">Signup Here for FREE</a>
				<div id="verify_status"></div>
			</td>
		</tr>
<?php
	}
}
Websms_Setting_Options::init();
