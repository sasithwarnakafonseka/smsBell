<?php

	class PieRegistrationForm extends FormInterface
	{

		private $formSessionVar = FormSessionVars::PIE_REG;
		private $phoneFormID;
		private $phoneFieldKey;

		function handleForm()
		{
			$this->phoneFieldKey = 'billing_phone';
			$this->phoneFormID = $this->getPhoneFieldKey();
			add_action( 'pie_register_after_register_validate', array($this,'Websms_pie_user_registration'),99,0);
		}

		public static function isFormEnabled()
		{
			return (bellsms_get_option('buyer_signup_otp', 'Websms_general')=="on") ? true : false;
		}

		

		function Websms_pie_user_registration()
		{
			WebsmsUtility::checkSession();
			if(!array_key_exists($this->formSessionVar,$_SESSION))
			{
				$phone_field = $this->getPhoneFieldKey();
				$phone = !WebsmsUtility::isBlank($phone_field) ? $_POST[$phone_field] : NULL;
				$this->startTheOTPVerificationProcess($_POST['username'],$_POST['e_mail'],$phone);
			}
			elseif(strcasecmp($_SESSION[$this->formSessionVar],'validated')==0)
				$_SESSION[$this->formSessionVar] = 'validationChecked';
			elseif(strcasecmp($_SESSION[$this->formSessionVar],'validationChecked')==0)
				$this->unsetOTPSessionVariables();
		}

		function startTheOTPVerificationProcess($username,$useremail,$phone)
		{
			WebsmsUtility::initialize_transaction($this->formSessionVar);
			$errors = new WP_Error();
			Websms_site_challenge_otp( $username,$useremail,$errors,$phone,"phone");
		}

		function getPhoneFieldKey()
		{
			$fields = unserialize(get_option('pie_fields'));
			$keys = (is_array($fields)) ? array_keys($fields) : array();
			foreach($keys as $key)
			{
				if(strcasecmp(trim($fields[$key]['label']),$this->phoneFieldKey)==0)
					return str_replace("-","_",sanitize_title($fields[$key]['type']."_"
						.(isset($fields[$key]['id']) ? $fields[$key]['id'] : "")));
			}
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			Websms_site_otp_validation_form($user_login,$user_email,$phone_number,WebsmsUtility::_get_invalid_otp_method(),"phone",FALSE);
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$_SESSION[$this->formSessionVar]="validated";
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			WebsmsUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? FALSE : $isAjax;
		}

		public function getPhoneNumberSelector($selector)	
		{
			WebsmsUtility::checkSession();
			if(self::isFormEnabled()) array_push($selector, $this->phoneFormID); 
			return $selector;
		}

		function handleFormOptions()
		{
		
		}	
	}
	new PieRegistrationForm;