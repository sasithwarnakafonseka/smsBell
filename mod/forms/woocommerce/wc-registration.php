<?php
	class WooCommerceRegistrationForm extends FormInterface
	{
		private $formSessionVar = FormSessionVars::WC_DEFAULT_REG;
		private $formSessionVar2 = FormSessionVars::WC_REG_POPUP;
		private $otpType;
		private $generateUserName;
		private $generatePassword;
		private $redirectToPage;
		private $popupEnabled;
		
		function handleForm()
		{
			$this->popupEnabled 	= (bellsms_get_option('register_otp_popup_enabled', 'Websms_general')=="on") ? TRUE : FALSE;
			$this->otpType = get_option('mo_customer_validation_wc_enable_type');
			$this->generateUserName = get_option( 'woocommerce_registration_generate_username' );
			$this->generatePassword = get_option( 'woocommerce_registration_generate_password' );  
			$this->redirectToPage = get_option('mo_customer_validation_wc_redirect');
			if(isset($_REQUEST['register'])){
				add_filter('woocommerce_registration_errors', array($this,'woocommerce_site_registration_errors'),10,3);
			}
			
			add_action( 'woocommerce_register_form', array($this,'Websms_add_phone_field') );
			add_action( 'woocommerce_created_customer', array( $this, 'wc_user_created' ), 10, 2 );
			
			if($this->popupEnabled==TRUE){
			 add_action( 'woocommerce_register_form_end', array($this,'add_modal_html_register_otp') );
			 add_action( 'woocommerce_register_form_end', array($this,'Websms_display_registerOTP_btn') );
			}
			
			$this->routeData();
		}
		
		public static function isFormEnabled()
		{
			return (bellsms_get_option('buyer_signup_otp', 'Websms_general')=="on") ? true : false;
		}
		
		/*popup in modal*/
		function routeData()
		{
			if(!array_key_exists('option', $_REQUEST)) return;
			switch (trim($_REQUEST['option'])) 
			{
				case "Websms_register_otp_validate_submit":
					$this->handle_ajax_register_validate_otp($_REQUEST);			break;
			}
		}
		
		
				
		function handle_ajax_register_validate_otp($data)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar2])) return;
			
			if(strcmp($_SESSION['phone_number_mo'], $data['billing_phone']))
				wp_send_json( WebsmsUtility::_create_json_response( WebsmsMessages::PHONE_MISMATCH,'error'));
			else
				do_action('Websms_validate_otp','phone');
		}
		
		
		
		public static function Websms_display_registerOTP_btn() 
		{
			$otp_resend_timer = bellsms_get_option( 'otp_resend_timer', 'Websms_general', '15');
			echo '<button type="submit" class="woocommerce-Button button" name="register" value="Register" id="Websms_register_with_otp">Register</button>';
			 
			 echo '<script>
							
							  var counterRunning=false;
							   jQuery("[name=register]").not("#Websms_register_with_otp").hide();
							   jQuery("#Websms_register_with_otp").click(function(o) {
								   if(counterRunning){$mo("#myRegisterModal").show();return false;}
								   var e = jQuery("[name=\"billing_phone\"]").val();
								   $mo = jQuery;
								   $mo.ajax({
										url:"'.site_url().'/?option=Websms_register_with_otp",type:"POST",
										data:$mo(this).parents("form").serialize()+"&register=Register",
										crossDomain:!0,
										dataType:"json",
										success:function(o){("success"==o.result)?($mo(".blockUI").hide(),
										$mo("#Websms_register_message").empty().removeClass("woocommerce-error"),
										$mo("#Websms_register_message").append(o.message),
										$mo("#Websms_register_message").addClass("woocommerce-message"),
										$mo("#myRegisterModal").show(), 
										$mo("#Websms_validate_field").show(),
										$mo("#mo_customer_validation_otp_token").focus()):
										($mo("#Websms_register_with_otp").parents("form").submit())
										timerRegCount();
										counterRunning=true;
										},
										error:function(o,e,m)
										{
											$mo("#register_with_otp_extra_fields").html("<input type=\"hidden\" name=\"register\" value=\"Register\">"),
											$mo("#Websms_register_with_otp").parents("form").submit();
										}
									   });
								   
								   return false;		
								});
								
								
			 
			 </script>';
			
		}
		
		function add_modal_html_register_otp()
		{
			//if($this->guestCheckOutOnly && is_user_logged_in())  return;
			$otp_resend_timer = bellsms_get_option( 'otp_resend_timer', 'Websms_general', '15');
			$params=array(
				'otp_range'=>WebsmsMessages::showMessage('OTP_RANGE'), 
				'VALIDATE_OTP'=>WebsmsMessages::showMessage('VALIDATE_OTP'), 
				'RESEND'=>WebsmsMessages::showMessage('RESEND'),
				'otp_resend_timer'=>$otp_resend_timer,
				'modalName'=>'myRegisterModal',
				'alert_msg_div'=>'Websms_register_message',
				'timer_div'=>'reg_timer',
				'resendFunc'=>'resendRegOtp',
				'validate_otp_btn'=>'Websms_register_otp_validate_submit',
				'resend_btn_id'=>'register_verify_otp',
				'otp_input_field_nm'=>'Websms_customer_validation_otp_token',
			);
			echo get_bellsms_template('template/otp-popup-1.php',$params);
			
			echo '<script>
			function resendRegOtp()
			{
				jQuery("#Websms_register_with_otp").trigger("click");
			}
			</script>';
			echo '<div id="register_with_otp_extra_fields"></div>';
			echo '<script>
			
		   	function timerRegCount()
			{
				var timer = function(secs){
					var sec_num = parseInt(secs, 10)    
					var hours   = Math.floor(sec_num / 3600) % 24
					var minutes = Math.floor(sec_num / 60) % 60
					var seconds = sec_num % 60    
					hours = hours < 10 ? "0" + hours : hours;
					minutes = minutes < 10 ? "0" + minutes : minutes;
					seconds = seconds < 10 ? "0" + seconds : seconds;
					return [hours,minutes,seconds].join(":");
				};
				document.getElementById("reg_timer").style.display = "block";
				document.getElementById("reg_timer").innerHTML = timer('.$otp_resend_timer.')+" sec";
				var counter = '.$otp_resend_timer.';
				 interval = setInterval(function() {
					counter--;
					 var places = (counter < 10 ? "0" : "");
					document.getElementById("reg_timer").innerHTML = timer(counter)+ " sec";
					if (counter == 0) {
						counterRunning=false;
						document.getElementById("reg_timer").style.display = "none";
						var cssString = "pointer-events: auto; cursor: pointer; opacity: 1; float:right"; 
						document.getElementById("register_verify_otp").style.cssText = cssString;
						clearInterval(interval);
					}
					else
					{
						document.getElementById("register_verify_otp").style.cssText = "pointer-events: none; cursor: default; opacity: 1; float:right";
					}
				}, 1000);
			}
			
			function stoptimer(obj)
			{
				clearInterval(obj);
			}
			
			
			$mo = jQuery;
			$mo("#Websms_register_otp_validate_submit").click(function(){
					var e = $mo("#myRegisterModal #Websms_customer_validation_otp_token").val();
					if(e=="")
					{
						alert("Please enter OTP");
						return false;
					}
					var extra_fields=\'<input type="hidden" name="Websms_action" value="1" /><input type="hidden" name="otp_type" value="phone"><input type="hidden" name="from_both" value="false">\';
					$mo("#register_with_otp_extra_fields").html(extra_fields);
					
					
					var p = $mo("[name=\"billing_phone\"]").val();
					
					$mo = jQuery;
								   $mo.ajax({
										url:"'.site_url().'/?option=Websms_register_otp_validate_submit",type:"POST",
										data:{Websms_customer_validation_otp_token:e,Websms_action:"Websms_action_register_ajax_validate_otp",billing_phone:p},
										crossDomain:!0,
										dataType:"json",
										success:function(o){("success"==o.result)?($mo(".blockUI").hide(),
										$mo("#Websms_register_message").empty().removeClass("woocommerce-error"),$mo("#myRegisterModal").hide(),
										$mo("#register_with_otp_extra_fields").html("<input type=\"hidden\" name=\"register\" value=\"Register\">"),
										$mo("#Websms_register_otp_validate_submit").parents("form").submit()):
										($mo(".blockUI").hide(),
										$mo("#Websms_register_message").empty(),
										$mo("#Websms_register_message").append(o.message),
										$mo("#Websms_register_message").addClass("woocommerce-error"),
										$mo("#myRegisterModal").show())
										},
										error:function(o,e,m)
										{

										}
									   });
										
					return false;		   
			});	
			</script>
			';
		}
		/*popup in modal*/
		
		
		
		//this function created for updating and create a hook created on 29-01-2019
		public function wc_user_created($user_id, $data)
		{
			$post_data = wp_unslash( $_POST );
			
			if(array_key_exists('billing_phone', $post_data))
			{
				$billing_phone = $post_data['billing_phone'];
				update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $billing_phone ) );
				do_action('bellsms_after_update_new_user_phone',$user_id,$billing_phone);
			}
		}
		
		function show_error_msg($error_hook = NULL, $err_msg = NULL, $type = NULL)
		{
			if(isset($_SESSION[$this->formSessionVar2]))
			{
				wp_send_json( WebsmsUtility::_create_json_response($err_msg,$type));
			}
			else
			{
				return new WP_Error( $error_hook,$err_msg);
			}
		}
		
		function woocommerce_site_registration_errors($errors,$username,$email)
		{
			WebsmsUtility::checkSession();
			if(isset($_SESSION['sa_mobile_verified']))
			{
				unset($_SESSION['sa_mobile_verified']);
				return $errors;
			}
			$password = !empty($_REQUEST['password']) ? $_REQUEST['password'] :'';
			if(!WebsmsUtility::isBlank(array_filter($errors->errors))) return $errors; 
			if(isset($_REQUEST['option']) && $_REQUEST['option']=='Websms_register_with_otp')
			{
				WebsmsUtility::initialize_transaction($this->formSessionVar2);
			}
			else
			{
				WebsmsUtility::initialize_transaction($this->formSessionVar);
			}
			
			if(bellsms_get_option('allow_multiple_user', 'Websms_general')!="on") {
				if( sizeof(get_users(array('meta_key' => 'billing_phone', 'meta_value' => $_POST['billing_phone']))) > 0 ) {
					if(isset($_SESSION[$this->formSessionVar2]))
					{
						$this->show_error_msg(NULL,__('An account is already registered with this mobile number. Please login.', 'error' ));
					}
					else
					{
						return $this->show_error_msg('registration-error-number-exists',__( 'An account is already registered with this mobile number. Please login.', 'woocommerce' ));
					}
				}
			}
			
			
				if ( isset($_POST['billing_phone']) && WebsmsUtility::isBlank( $_POST['billing_phone']) ){
					if(isset($_SESSION[$this->formSessionVar2]))
					{
						$this->show_error_msg(NULL,__('Please enter phone number.', 'error' ));
					}
					else
					{
						return $this->show_error_msg('registration-error-invalid-phone',__( 'Please enter phone number.', 'woocommerce' ));
					}
				}
			
			do_action( 'woocommerce_register_post', $username, $email, $errors );
			if($errors->get_error_code())
				throw new Exception( $errors->get_error_message() );
			
			
			//process and start the OTP verification process
			return $this->processFormFields($username,$email,$errors,$password); 	
		}

		function processFormFields($username,$email,$errors,$password)
		{
			global $phoneLogic;
						
			if ( !isset( $_POST['billing_phone'] ) || !WebsmsUtility::validatePhoneNumber($_POST['billing_phone']))
				return new WP_Error( 'billing_phone_error', str_replace("##phone##",WebsmscURLOTP::checkPhoneNos($_POST['billing_phone']),$phoneLogic->_get_otp_invalid_format_message()) );
			Websms_site_challenge_otp($username,$email,$errors,$_POST['billing_phone'],"phone",$password);
		}
		
		function Websms_add_phone_field()
		{
			
			echo '<p class="form-row form-row-wide">
					<label for="reg_billing_phone">'.WebsmsMessages::showMessage('Phone').'<span class="required">*</span></label>
					<input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="'.(!empty( $_POST['billing_phone'] ) ? $_POST['billing_phone'] : "").'" />
			  	  </p>';
			
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2])) return;
			if(isset($_SESSION[$this->formSessionVar]))
				Websms_site_otp_validation_form($user_login,$user_email,$phone_number,WebsmsUtility::_get_invalid_otp_method(),"phone",FALSE);
			if(isset($_SESSION[$this->formSessionVar2]))
				wp_send_json( WebsmsUtility::_create_json_response(WebsmsMessages::INVALID_OTP,'error'));
			
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2])) return;
			$_SESSION['sa_mobile_verified'] = true;
			if(isset($_SESSION[$this->formSessionVar2]))
				wp_send_json( WebsmsUtility::_create_json_response("OTP Validated Successfully",'success'));
		}
		
		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
			unset($_SESSION[$this->formSessionVar2]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			WebsmsUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar2]) ? TRUE : $isAjax;
		}

		function handleFormOptions()
		{
			update_option('mo_customer_validation_wc_default_enable',
				isset( $_POST['mo_customer_validation_wc_default_enable']) ? $_POST['mo_customer_validation_wc_default_enable'] : 0);
			update_option('mo_customer_validation_wc_enable_type',
				isset( $_POST['mo_customer_validation_wc_enable_type']) ? $_POST['mo_customer_validation_wc_enable_type'] : '');
			update_option('mo_customer_validation_wc_redirect',
				isset( $_POST['page_id']) ? get_the_title($_POST['page_id']) : 'My Account');
		}
	}
	new WooCommerceRegistrationForm;