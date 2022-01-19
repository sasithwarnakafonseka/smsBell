<?php

class WebsmsConstants
{
	const SUCCESS				= "SUCCESS";
	const FAILURE				= "FAILURE";
	const TEXT_DOMAIN 			= "wsw.lk";
	const PATTERN_PHONE			= '/^(\+)?(country_code)?0?\d+$/'; //'/^\d{10}$/';//'/\d{10}$/';
	const ERROR_JSON_TYPE 		= 'error';
	const SUCCESS_JSON_TYPE 	= 'success';
	const DEFAULT_SMS_TEMPLATE  = "Dear Customer, Your OTP is ##otp##. Use this Passcode to complete your transaction. Thank you.";
	const RCON_TEMPLATE			= "YOUR CODE IS : ##otp##";
	const USERPRO_AJAX_CHECK	= "mo_phone_validation";
	const USERPRO_VER_FIELD_META= "verification_form";	
	
	function __construct()
	{
		$this->define_global();
	}
	
	public static function getPhonePattern()
	{
		$country_code = bellsms_get_option( 'default_country_code', 'Websms_general' );
		$sa_mobile_pattern = bellsms_get_option( 'sa_mobile_pattern', 'Websms_general','/^(\+)?(country_code)?0?\d{10}$/' );
		$pattern = ($sa_mobile_pattern!='') ? $sa_mobile_pattern:self::PATTERN_PHONE;
		$country_code = str_replace('+', '', $country_code);
		$pattern_phone = str_replace("country_code",$country_code,$pattern);
		return $pattern_phone;
	}	
	
	function define_global()
	{
		global $phoneLogic;
		$phoneLogic = new PhoneLogic();
		define('MOV_DIR', plugin_dir_path(dirname(__FILE__)));
		define('MOV_URL', plugin_dir_url(dirname(__FILE__)));
		define('MOV_CSS_URL', MOV_URL . 'css/websms_lk_customer_validation_style.css');
		define('MOV_LOADER_URL', MOV_URL . 'images/ajax-loader.gif');
	}
}
new WebsmsConstants;