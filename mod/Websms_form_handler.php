<?php
	require_once 'forms/woocommerce/wc-checkout.php';
	require_once 'forms/woocommerce/wc-registration.php';
	require_once 'forms/wp-login.php';
	require_once 'forms/ultimate-member.php';
	require_once 'forms/cf7.php';
	require_once 'forms/wp-member.php';
	require_once 'forms/pie-registration.php';
	require_once 'forms/affilate-manager.php';
	require_once 'forms/wp-reset-password.php';
	require_once 'forms/learnpress-registration.php';
	
	add_action(	'init', 'Websms_customer_validation_handle_form' , 1 );
	add_action( 'Websms_validate_otp', '_handle_validation_form_action' , 1, 2);

	function Websms_site_challenge_otp($user_login, $user_email, $errors, $phone_number=null,$otp_type,$password="",$extra_data=null,$from_both=false)
	{
		WebsmsUtility::checkSession();
		$_SESSION['current_url'] 	= WebsmsUtility::currentPageUrl();
		$_SESSION['user_email'] 	= $user_email;
		$_SESSION['user_login'] 	= $user_login;
		$_SESSION['user_password'] 	= $password;
		$_SESSION['phone_number_mo']= $phone_number;
		$_SESSION['extra_data'] 	= $extra_data;
		_handle_otp_action($user_login,$user_email,$phone_number,$otp_type,$from_both);
	}

	function _handle_verification_resend_otp_action($otp_type,$from_both)
	{
		WebsmsUtility::checkSession();
		$user_email 	= $_SESSION['user_email'];
		$user_login 	= $_SESSION['user_login'];
		$password 		= $_SESSION['user_password'];
		$phone_number 	= $_SESSION['phone_number_mo'];
		$extra_data 	= $_SESSION['extra_data'];
		_handle_otp_action($user_login,$user_email,$phone_number,$otp_type,$from_both);
	}

	function _handle_otp_action($user_login,$user_email,$phone_number,$otp_type,$form)
	{
		global $phoneLogic;
		$phoneLogic->_handle_logic($user_login,$user_email,$phone_number,$otp_type,$form);
	}

	function _handle_validation_goBack_action()
	{
		WebsmsUtility::checkSession();
		$url = isset($_SESSION['current_url'])? $_SESSION['current_url'] : '';
		session_unset();
		wp_redirect($url);
		exit();
	}
	
	function _handle_validation_form_action($requestVariable='Websms_customer_validation_otp_token',$from_both=false)
	{
		WebsmsUtility::checkSession();
		$_REQUEST		= bellsms_sanitize_array($_REQUEST);
		$user_login 	= !WebsmsUtility::isBlank($_SESSION['user_login']) ? $_SESSION['user_login'] 						 	: null;
		$user_email 	= !WebsmsUtility::isBlank($_SESSION['user_email']) ? $_SESSION['user_email'] 							: null;
		$phone_number 	= (array_key_exists('billing_phone',$_REQUEST) && !WebsmsUtility::isBlank($_REQUEST['billing_phone']))? $_REQUEST['billing_phone'] 											: null;
		$phone_number 	= array_key_exists('phone_number_mo', $_SESSION) && !WebsmsUtility::isBlank($_SESSION['phone_number_mo']) ? $_SESSION['phone_number_mo'] : $phone_number;
		$password 		= !WebsmsUtility::isBlank($_SESSION['user_password']) 					? $_SESSION['user_password'] 						: null;
		$extra_data 	= !WebsmsUtility::isBlank($_SESSION['extra_data']) 						? $_SESSION['extra_data'] 							: null;
		//$txID 			= !WebsmsUtility::isBlank($_SESSION['mo_customer_validation_site_txID'])? $_SESSION['mo_customer_validation_site_txID' ] 	: null;
		$requestVariable = (array_key_exists('phone',$_REQUEST) && !array_key_exists('Websms_customer_validation_otp_token',$_REQUEST))?$_REQUEST['phone']:'Websms_customer_validation_otp_token';
		
		$requestVariable = array_key_exists('order_verify',$_REQUEST)?'order_verify':$requestVariable;
		
		$otp_token 		= !WebsmsUtility::isBlank($_REQUEST[$requestVariable])? $_REQUEST[$requestVariable] : null;
	
		$content = json_decode(WebsmscURLOTP::validate_otp_token($phone_number, $otp_token),true);
		if($content['status']=='success' && isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') == 0) {
			_handle_success_validated($user_login,$user_email,$password,$phone_number,$extra_data);
		}else{
			_handle_error_validated($user_login,$user_email,$phone_number);
		}
	}

	function _handle_success_validated($user_login,$user_email,$password,$phone_number,$extra_data)
	{		
		$redirect_to = array_key_exists('redirect_to', $_POST) ? $_POST['redirect_to'] : '';
		do_action('otp_verification_successful',$redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data);
	}

	function _handle_error_validated($user_login,$user_email,$phone_number)
	{	
		do_action('otp_verification_failed',$user_login,$user_email,$phone_number);
	}
	
	function _handle_validate_otp_choice_form($postdata)
	{
		WebsmsUtility::checkSession();
		if($postdata['mo_customer_validation_otp_choice'] == 'user_email_verification')
			Websms_site_challenge_otp($_SESSION['user_login'],$_SESSION['user_email'],null,$_SESSION['phone_number_mo'],"email",$_SESSION['user_password'],$_SESSION['extra_data'],true);
		else 
			Websms_site_challenge_otp($_SESSION['user_login'],$_SESSION['user_email'],null,$_SESSION['phone_number_mo'],"phone",$_SESSION['user_password'],$_SESSION['extra_data'],true);
	}

	function _handle_mo_ajax_phone_validate($getdata)
	{
		WebsmsUtility::checkSession();
		$_SESSION[FormSessionVars::AJAX_FORM] = trim($getdata['billing_phone']);
		Websms_site_challenge_otp($_SESSION['user_login'],null,null, trim($data['billing_phone']),"phone",$_SESSION['user_password'],null, null);
	}
	
	function _handle_mo_ajax_form_validate_action()
	{
		WebsmsUtility::checkSession();
		if(isset($_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]))
		{
			_handle_validation_form_action();
			if($_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]=='validated')
				wp_send_json( WebsmsUtility::_create_json_response('successfully validated','success') );
			else
				wp_send_json( WebsmsUtility::_create_json_response(WebsmsMessages::INVALID_OTP,'error'));
		}
	}

	function _handle_mo_create_user_wc_action($postdata)
	{
		WebsmsUtility::checkSession();
		if(isset($_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]) && $_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]=='validated')
			create_new_wc_social_customer($postdata);
	}

	function Websms_customer_validation_handle_form()
	{
		if(array_key_exists('option', $_REQUEST) && $_REQUEST['option'])
		{
			switch (trim($_REQUEST['option'])) 
			{
				case "validation_goBack":
					_handle_validation_goBack_action();								break;
				case "Websms-ajax-otp-generate":
					_handle_mo_ajax_phone_validate($_GET);							break;
				case "Websms-ajax-otp-validate":
					_handle_mo_ajax_form_validate_action($_GET);					break;
				case "Websms_ajax_form_validate":
					_handle_mo_create_user_wc_action($_POST);						break;
				case "Websms-validate-otp-form":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_validation_form_action();	break;
				case "verification_resend_otp_phone":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_verification_resend_otp_action("phone",trim($_REQUEST['option'])); 	break;
				case "verification_resend_otp_email":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_verification_resend_otp_action("email",trim($_REQUEST['option']));		break;
				case "verification_resend_otp_both":
					$from_both = $_POST['from_both']=='true' ? true : false;
					_handle_verification_resend_otp_action("both",trim($_REQUEST['option']));		break;
				case "Websms-validate-otp-choice-form":
					_handle_validate_otp_choice_form($_POST);						break;
																break;
			}
		}
	}
?>