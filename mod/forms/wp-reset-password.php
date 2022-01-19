<?php
	class WPResetPasswordForm extends FormInterface
	{
		private $formSessionVar = FormSessionVars::WP_DEFAULT_LOST_PWD;
		private $phoneNumberKey;

		function handleForm()
		{	
			$this->phoneNumberKey = 'billing_phone';
			add_action( 'retrieve_password', array($this,'startWebsmsResetPasswordProcess'), 10, 1 );
			$this->routeData();
		}
		
		function routeData()
		{
			if (!empty($_REQUEST['option']) && $_REQUEST['option']=="Websms-change-password-form") 
			{
				$this->_handle_Websms_changed_pwd($_POST);
			} 
		}
				
		public static function isFormEnabled() 
		{
			return (bellsms_get_option('reset_password', 'Websms_general')=="on") ? true : false;
		}
		
		function _handle_Websms_changed_pwd($post_data)
		{
			WebsmsUtility::checkSession();
			$error='';
			$new_password = !empty($post_data['Websms_user_newpwd']) ? $post_data['Websms_user_newpwd'] : '' ;
			$confirm_password = !empty($post_data['Websms_user_cnfpwd']) ? $post_data['Websms_user_cnfpwd'] : '';
			
			if ($new_password=='') {
				$error = 'Please enter your password.';
			}
			if ($new_password !== $confirm_password ){
				$error ='Passwords do not match.';
			}
			if(!empty($error))
			{
				WebsmsAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], $error, 'phone',false);
				
			}
			$user = get_user_by( 'login', $_SESSION['user_login'] );
			reset_password( $user, $new_password );
			$this->unsetOTPSessionVariables();
			wp_redirect( add_query_arg( 'password-reset', 'true', wc_get_page_permalink( 'myaccount' ) ) );
			exit;
		}
		
		function startWebsmsResetPasswordProcess($user_login)
		{
			WebsmsUtility::checkSession();	
			$user = get_user_by( 'login', $user_login );
			$phone_number = get_user_meta($user->data->ID, $this->phoneNumberKey,true);
			if(isset($_REQUEST['wc_reset_password']))
			{
				WebsmsUtility::initialize_transaction($this->formSessionVar);
				if($phone_number!='')
				{
					$this->fetchPhoneAndStartVerification($user->data->user_login,$this->phoneNumberKey,NULL,NULL,$phone_number);
				}
			}
			return $user;
		} 

		function fetchPhoneAndStartVerification($user,$key,$username,$password,$phone_number)
		{
			if((array_key_exists($this->formSessionVar,$_SESSION) && strcasecmp($_SESSION[$this->formSessionVar],'validated')==0)) return;
			Websms_site_challenge_otp($user,$username,null,$phone_number,"phone",$password,WebsmsUtility::currentPageUrl(),false);
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;

			if(isset($_SESSION[$this->formSessionVar])){	
				$_SESSION[$this->formSessionVar] = 'verification_failed';
				//wp_send_json( WebsmsUtility::_create_json_response(WebsmsMessages::INVALID_OTP,'error'));
				Websms_site_otp_validation_form($user_login,$user_email,$phone_number,WebsmsMessages::INVALID_OTP,"phone",FALSE);
			}
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			WebsmsAskForResetPassword($_SESSION['user_login'],$_SESSION['phone_number_mo'], "Please change Your password", 'phone',false);
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			WebsmsUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
		}

		function handleFormOptions()
	    {
			
	    }
	}
	new WPResetPasswordForm;