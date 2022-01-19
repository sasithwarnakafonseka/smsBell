<?php
	class WPLoginForm extends FormInterface
	{
		private $formSessionVar  	= FormSessionVars::WP_LOGIN_REG_PHONE;
		private $formSessionVar2 	= FormSessionVars::WP_DEFAULT_LOGIN;
		private $formSessionVar3 	= FormSessionVars::WP_LOGIN_WITH_OTP;
		private $phoneNumberKey;
		function handleForm()
		{	
			$this->phoneNumberKey = 'billing_phone';
			if(!empty($_REQUEST['learn-press-register-nonce'])){return;}
			
			add_filter( 'authenticate', 				array($this,'_handle_Websms_wp_login'), 99, 4 );
			$this->routeData();
			$enabled_login_with_otp = bellsms_get_option( 'login_with_otp', 'Websms_general', 'on');
			if($enabled_login_with_otp=='on')
			{
				add_action( 'woocommerce_login_form_end',	array($this,'add_login_with_otp_popup') );
				add_action( 'woocommerce_login_form_end',	array($this,'Websms_display_login_with_otp') );
				add_action( 'um_after_login_fields',  		array($this,'add_login_with_otp_popup'), 1002 );
				add_action( 'um_after_login_fields',  		array($this,'Websms_display_login_with_otp'), 1002 );
			}
		}
		
		function routeData()
		{
			if(!array_key_exists('option', $_REQUEST)) return;
			switch (trim($_REQUEST['option'])) 
			{
				case "Websms-ajax-otp-generate":
					$this->_handle_wp_login_ajax_send_otp($_POST);				break;
				case "Websms-ajax-otp-validate":
					$this->_handle_wp_login_ajax_form_validate_action($_POST);	break;
				case "Websms_ajax_form_validate":
					$this->_handle_wp_login_create_user_action($_POST);			break;
				case "Websms_ajax_login_with_otp":
					$this->handle_login_with_otp();			break;
			}
		}
		
		function handle_login_with_otp()
		{
			if(empty($_REQUEST['username']))
			{
				wp_send_json( WebsmsUtility::_create_json_response(WebsmsMessages::PHONE_NOT_FOUND,'error'));
			}
			else
			{
				$phone_number = !empty($_REQUEST['username']) ? $_REQUEST['username'] : '';
				if($phone_number!='')
				{
					$user_info = $this->getUserFromPhoneNumber($phone_number,$this->phoneNumberKey);
					$user_login = ($user_info) ? $user_info->data->user_login : '';
				}
				if(!empty($user_login))
				{
					//WebsmsUtility::checkSession();
					//$this->unsetOTPSessionVariables();
					//$_SESSION[$this->formSessionVar3]=true;
					WebsmsUtility::initialize_transaction($this->formSessionVar3);
					Websms_site_challenge_otp(null,null,null,$phone_number,"phone",null,WebsmsUtility::currentPageUrl(),true);
				}
				else
				{
					wp_send_json( WebsmsUtility::_create_json_response( WebsmsMessages::PHONE_NOT_FOUND,'error'));
				}				
			}
		}
		
		public static function Websms_display_login_with_otp() 
		{
			echo '<input type="button" class="button" name="Websms_login_with_otp" value="Login with OTP" id="Websms_login_with_otp">';
			 echo '<script>
							   var counterRunning=false;
							   jQuery("#Websms_login_with_otp").click(function(o) {
								   if(counterRunning){$mo("#myLoginModal").show();return false;}
								   //var e = jQuery("input[name=username]").val();
								   var e = jQuery(this).parents("form").find("input[type=\"text\"]:first").val();
								   jQuery(this).parents("form").find("input[type=\"password\"]").val("");
								   if(e=="" || isNaN(e))
								   {
									   alert("'.WebsmsMessages::showMessage('ENTER_MOB_NO').'");
									   return false;
								   }
								   
								   $mo = jQuery;
								   $mo.ajax({
										url:"'.site_url().'/?option=Websms_ajax_login_with_otp",type:"POST",
										//data:{login_otp_nos:e,Websms_action:"Websms_action_login_with_otp"},
										data:$mo(this).parents("form").serialize()+"&login=Login",
										crossDomain:!0,
										dataType:"json",
										success:function(o){("success"==o.result)?($mo(".blockUI").hide(),
										$mo("#Websms_login_message").empty().removeClass("woocommerce-error"),
										$mo("#Websms_login_message").append(o.message),
										$mo("#Websms_login_message").addClass("woocommerce-message"),
										$mo("#myLoginModal").show(), 
										$mo("#Websms_validate_field").show(),
										$mo("#mo_customer_validation_otp_token").focus()):
										($mo(".blockUI").hide(),
										$mo("#Websms_login_message").empty(),
										$mo("#Websms_login_message").append(o.message),
										$mo("#Websms_login_message").addClass("woocommerce-error"),
										$mo("#myLoginModal").show())
										timerLoginCount();
										counterRunning=true;
										},
										error:function(o,e,m)
										{

										}
									   });
								   
								   return false;		
								});	 
			 </script>';			
		}
		
		function add_login_with_otp_popup()
		{
			//if($this->guestCheckOutOnly && is_user_logged_in())  return;
			$otp_resend_timer = bellsms_get_option( 'otp_resend_timer', 'Websms_general', '15');
			$params=array(
				'otp_range'=>WebsmsMessages::showMessage('OTP_RANGE'), 
				'VALIDATE_OTP'=>WebsmsMessages::showMessage('VALIDATE_OTP'), 
				'RESEND'=>WebsmsMessages::showMessage('RESEND'),
				'otp_resend_timer'=>$otp_resend_timer,
				'modalName'=>'myLoginModal',
				'alert_msg_div'=>'Websms_login_message',
				'timer_div'=>'login_timer',
				'resendFunc'=>'resendLoginOtp',
				'validate_otp_btn'=>'Websms_login_otp_validate_submit',
				'resend_btn_id'=>'login_verify_otp',
				'otp_input_field_nm'=>'Websms_customer_validation_otp_token',
			);
			echo get_bellsms_template('template/otp-popup-1.php',$params);
			echo '<div id="login_with_otp_extra_fields"></div>';
			$otp_resend_timer = bellsms_get_option( 'otp_resend_timer', 'Websms_general', '15');
			echo '<script>function resendLoginOtp()
			{
				jQuery("#Websms_login_with_otp").trigger("click");
			}
			</script>';
			
			echo '<script>			
		   	function timerLoginCount()
			{
				var timer = function(secs){
					var sec_num = parseInt(secs, 10)    
					var hours   = Math.floor(sec_num / 3600) % 24
					var minutes = Math.floor(sec_num / 60) % 60
					var seconds = sec_num % 60    
					hours = hours < 10 ? "0" + hours : hours;
					minutes = minutes < 10 ? "0" + minutes : minutes;
					seconds = seconds < 10 ? "0" + seconds : seconds;
					return [hours,minutes,seconds].join(":")
				};
				document.getElementById("login_timer").style.display = "block";
				document.getElementById("login_timer").innerHTML = timer('.$otp_resend_timer.')+" sec";
				var counter = '.$otp_resend_timer.';
				 interval = setInterval(function() {
					counter--;
					 var places = (counter < 10 ? "0" : "");
					document.getElementById("login_timer").innerHTML = timer(counter)+ " sec";
					if (counter == 0) {
						counterRunning=false;
						document.getElementById("login_timer").style.display = "none";
						var cssString = "pointer-events: auto; cursor: pointer; opacity: 1; float:right"; 
						document.getElementById("login_verify_otp").style.cssText = cssString;
						clearInterval(interval);
					}
					else
					{
						document.getElementById("login_verify_otp").style.cssText = "pointer-events: none; cursor: default; opacity: 1; float:right";
					}
				}, 1000);
			}
			
			function stoptimer(obj)
			{
				clearInterval(obj);
			}
			
			
			$mo = jQuery;
			$mo("#Websms_login_otp_validate_submit").click(function(){
					if($mo("#Websms_login_customer_validation_otp_token").val()=="")
					{
						alert("Please enter OTP");
						return false;
					}
					
					var extra_fields=\'<input type="hidden" name="otp_type" value="phone"><input type="hidden" name="from_both">\';
					$mo("#login_with_otp_extra_fields").html(extra_fields);

					$mo = jQuery;
								   $mo.ajax({
										url:"'.site_url().'/?option=Websms-validate-otp-form",type:"POST",
										data:$mo("form.login").serialize(),
										crossDomain:!0,
										dataType:"json",
										success:function(o){("success"!=o.result)?($mo(".blockUI").hide(),
										$mo("#Websms_login_message").empty().addClass("woocommerce-error"),
										$mo("#Websms_login_message").append(o.message),
										$mo("#Websms_login_message").removeClass("woocommerce-message"),
										$mo("#Websms_login_customer_validation_otp_token").focus()):
										($mo("#login_with_otp_extra_fields").html("<input type=\"hidden\" name=\"login\" value=\"Login\">"), $mo("form.login").submit())	
										},
										error:function(o,e,m)
										{
											alert("error found here");
										}
					});

					//$mo("form.login").submit();
					
					return false;		   
			});	
			</script>
			';
		}
		
		public static function isFormEnabled() 
		{
			//return (bellsms_get_option('buyer_login_otp', 'Websms_general')=="on") ? true : false; //commented on 01-07-2019
			
			return (bellsms_get_option('buyer_login_otp', 'Websms_general')=="on" || bellsms_get_option('login_with_otp', 'Websms_general')=="on") ? true : false;
		}

		function check_wp_login_register_phone() 
		{
			return true; //get_option('mo_customer_validation_wp_login_register_phone') ? true : false;
		}

		function check_wp_login_by_phone_number()                                 
		{
			return true;//get_option('mo_customer_validation_wp_login_allow_phone_login') ? true : false;
		}
		
		function byPassLogin($user_role)
		{
			$current_role 		= array_shift($user_role);
			$excluded_roles 	= bellsms_get_option('admin_bypass_otp_login', 'Websms_general',array());
			if(!is_array($excluded_roles) && $excluded_roles=='on')
			{
				$excluded_roles = ($current_role=='administrator') ? array('administrator') : array();
			}
			return in_array($current_role,$excluded_roles) ? true : false;			
		}

		function check_wp_login_restrict_duplicates()
		{
			return (bellsms_get_option('allow_multiple_user', 'Websms_general')=="on") ? true : false;
		}

		function _handle_wp_login_create_user_action($postdata)
		{
			$redirect_to = isset($postdata['redirect_to'])?$postdata['redirect_to']:null;//added this line on 28-11-2018 due to affiliate login redirect issue
			
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar]) 
				|| $_SESSION[$this->formSessionVar]!='validated') 	return;

			$user = is_email( $postdata['log'] ) ? get_user_by("email",$postdata['log']) : get_user_by("login",$postdata['log']);
			if(!$user)
				$user = is_email( $postdata['username'] ) ? get_user_by("email",$postdata['username']) : get_user_by("login",$postdata['username']);
			
			update_user_meta($user->data->ID, $this->phoneNumberKey ,sanitize_text_field($postdata['mo_phone_number']));
			$this->login_wp_user($user->data->user_login,$redirect_to);
		}

		function login_wp_user($user_log, $extra_data=null)
		{ 
			$user = get_user_by("login",$user_log);
			wp_set_auth_cookie($user->data->ID);
			$this->unsetOTPSessionVariables();
			do_action( 'wp_login', $user->user_login, $user );	
			$redirect = WebsmsUtility::isBlank($extra_data) ? site_url() : $extra_data;
			wp_redirect($redirect);
			exit;
		}

		function _handle_Websms_wp_login($user, $username, $password)
		{
			WebsmsUtility::checkSession();
			/*login with otp*/
			$login_with_otp_enabled = (bellsms_get_option('login_with_otp', 'Websms_general')=="on") ? true : false;
			
			if(empty($password))
			{
				if(!empty($_REQUEST['username']))
				{
					$phone_number 	= !empty($_REQUEST['username'])?$_REQUEST['username']:'';
					$user_info 		= $this->getUserFromPhoneNumber($phone_number,$this->phoneNumberKey);
					$user_login 	= ($user_info) ? $user_info->data->user_login : '';
				}
			}
			
			if($login_with_otp_enabled && empty($password) && !empty($user_login) && !empty($_SESSION['login_otp_success']))
			{
				if ( ! empty( $_POST['redirect'] ) ) {
					$redirect 		= wp_sanitize_redirect( $_POST['redirect'] );
				} elseif ( wc_get_raw_referer() ) {
					$redirect 		= wc_get_raw_referer();
				} else {
					$redirect 		= wc_get_page_permalink( 'myaccount' );
				}
				unset($_SESSION['login_otp_success']);
				$this->login_wp_user($user_login,$redirect);
			}
			/*login with otp ends here*/
			
			
			if((array_key_exists($this->formSessionVar,$_SESSION) && strcasecmp($_SESSION[$this->formSessionVar],'validated')==0) && !empty($_POST['mo_phone_number']))
			{
				update_user_meta($user->data->ID, $this->phoneNumberKey ,sanitize_text_field($_POST['mo_phone_number']));
				$this->unsetOTPSessionVariables();
			}
			
			if(isset($_SESSION['sa_login_mobile_verified']))
			{
				unset($_SESSION['sa_login_mobile_verified']);
				return $user;
			}
			
			$user 					= $this->getUserIfUsernameIsPhoneNumber($user, $username, $password, $this->phoneNumberKey);
			
			if(is_wp_error($user)) 
				return $user;
			
			$user_meta 				= get_userdata($user->data->ID);
			$user_role 				= $user_meta->roles;
			$phone_number 			= get_user_meta($user->data->ID, $this->phoneNumberKey,true);
			if($this->byPassLogin($user_role)) return $user;
			$this->askPhoneAndStartVerification($user,$this->phoneNumberKey,$username,$phone_number);
			$this->fetchPhoneAndStartVerification($user,$this->phoneNumberKey,$username,$password,$phone_number);
			return $user;
		} 

		function getUserIfUsernameIsPhoneNumber($user, $username, $password, $key)
		{
			if(!$this->check_wp_login_by_phone_number() || !WebsmsUtility::validatePhoneNumber($username)) return $user;
			$user_info 				= $this->getUserFromPhoneNumber($username,$key);
			$username 				= is_object($user_info) ? $user_info->data->user_login : $username; //added on 20-05-2019			
			return wp_authenticate_username_password(NULL, $username, $password);
		}

		function getUserFromPhoneNumber($username,$key)
		{
			global $wpdb;
			$results 				= $wpdb->get_row("SELECT `user_id` FROM {$wpdb->base_prefix}usermeta inner join {$wpdb->base_prefix}users on ({$wpdb->base_prefix}users.ID = {$wpdb->base_prefix}usermeta.user_id) WHERE `meta_key` = '$key' AND `meta_value` =  '$username' order by user_id desc");			
			$user_id 				= (!empty($results)) ? $results->user_id : 0;
			return get_userdata($user_id);
		}

		function askPhoneAndStartVerification($user,$key,$username,$phone_number)
		{
			if(!WebsmsUtility::isBlank($phone_number)) return;
			if(!$this->check_wp_login_register_phone() )
				Websms_site_otp_validation_form(null,null,null, WebsmsMessages::PHONE_NOT_FOUND,null,null);
			else
			{
				WebsmsUtility::initialize_transaction($this->formSessionVar);
				Websms_external_phone_validation_form(WebsmsUtility::currentPageUrl(), $user->data->user_login, WebsmsMessages::REGISTER_PHONE_LOGIN, $key, array('user_login'=>$username));
			}					
		}

		function fetchPhoneAndStartVerification($user,$key,$username,$password,$phone_number)
		{
			if((array_key_exists($this->formSessionVar,$_SESSION) && strcasecmp($_SESSION[$this->formSessionVar],'validated')==0)
				|| (array_key_exists($this->formSessionVar2,$_SESSION) && strcasecmp($_SESSION[$this->formSessionVar2],'validated')==0)) return;
			WebsmsUtility::initialize_transaction($this->formSessionVar2);
			
			//Websms_site_challenge_otp($username,null,null,$phone_number[0],"phone",$password,$_REQUEST['redirect_to'],false);
			//Websms_site_challenge_otp($username,null,null,$phone_number[0],"phone",$password,WebsmsUtility::currentPageUrl(),false); //commented on 03-12-2018 get_user_meta set true
			Websms_site_challenge_otp($username,null,null,$phone_number,"phone",$password,WebsmsUtility::currentPageUrl(),false);
		}

		function _handle_wp_login_ajax_send_otp($data)
		{
			WebsmsUtility::checkSession();
			if($this->check_wp_login_restrict_duplicates() 
				&& !WebsmsUtility::isBlank($this->getUserFromPhoneNumber($data['billing_phone'],$this->phoneNumberKey)))
				wp_send_json(WebsmsUtility::_create_json_response(WebsmsMessages::PHONE_EXISTS,WebsmsConstants::ERROR_JSON_TYPE));
			elseif(isset($_SESSION[$this->formSessionVar]))
			{
				Websms_site_challenge_otp('ajax_phone','',null, trim($data['billing_phone']),"phone",null,$data, null);
			}
		}

		function _handle_wp_login_ajax_form_validate_action($data)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])&&!isset($_SESSION[$this->formSessionVar2])&&!isset($_SESSION[$this->formSessionVar3])) return;
			
			if(strcmp($_SESSION['phone_number_mo'], $data['billing_phone']) && isset($data['billing_phone']))
				wp_send_json( WebsmsUtility::_create_json_response( WebsmsMessages::PHONE_MISMATCH,'error'));
			else
				do_action('Websms_validate_otp','phone');
		}

		function handle_failed_verification($user_login, $user_email, $phone_number)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2]) && !isset($_SESSION[$this->formSessionVar3])) return;

			if(isset($_SESSION[$this->formSessionVar])){	
				$_SESSION[$this->formSessionVar] = 'verification_failed';
				wp_send_json( WebsmsUtility::_create_json_response(WebsmsMessages::INVALID_OTP,'error'));
			}
			if(isset($_SESSION[$this->formSessionVar2]))
				Websms_site_otp_validation_form($user_login,$user_email,$phone_number,WebsmsMessages::INVALID_OTP,"phone",FALSE);
			if(isset($_SESSION[$this->formSessionVar3])){
				wp_send_json( WebsmsUtility::_create_json_response(WebsmsMessages::INVALID_OTP,'error'));
			}			
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
				WebsmsUtility::checkSession();
				if(!isset($_SESSION[$this->formSessionVar]) && !isset($_SESSION[$this->formSessionVar2]) && !isset($_SESSION[$this->formSessionVar3])) return;
				
				if(isset($_SESSION[$this->formSessionVar]))
				{
					$_SESSION['sa_login_mobile_verified']=true;
					$_SESSION[$this->formSessionVar] = 'validated';
					wp_send_json( WebsmsUtility::_create_json_response('successfully validated','success') );
				}
				elseif(isset($_SESSION[$this->formSessionVar3]))
				{
					$_SESSION['login_otp_success']=true;
					wp_send_json( WebsmsUtility::_create_json_response("OTP Validated Successfully.",'success'));
					/* $user_info = $this->getUserFromPhoneNumber($phone_number,$this->phoneNumberKey);
					unset($_SESSION[$this->formSessionVar3]);
					
					if($user_info->data->user_login!='')
					{
						//$this->login_wp_user($user_info->data->user_login);
						$this->login_wp_user($user_info->data->user_login,$redirect_to); //for ultimate member
					} */
					
				}
				else
				{	
					$_SESSION['sa_login_mobile_verified']=true;
				}
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
			unset($_SESSION[$this->formSessionVar2]);
			unset($_SESSION[$this->formSessionVar3]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			WebsmsUtility::checkSession();
			//return isset($_SESSION[$this->formSessionVar]) ? TRUE : $isAjax;
			return (isset($_SESSION[$this->formSessionVar]) || isset($_SESSION[$this->formSessionVar3])) ? TRUE : $isAjax;
		}

		function handleFormOptions()
	    {
			update_option('mo_customer_validation_wp_login_enable',
				isset( $_POST['mo_customer_validation_wp_login_enable']) ? $_POST['mo_customer_validation_wp_login_enable'] : 0);
			update_option('mo_customer_validation_wp_login_register_phone',
				isset( $_POST['mo_customer_validation_wp_login_register_phone']) ? $_POST['mo_customer_validation_wp_login_register_phone'] : '');
			update_option('mo_customer_validation_wp_login_bypass_admin',
				isset( $_POST['mo_customer_validation_wp_login_bypass_admin']) ? $_POST['mo_customer_validation_wp_login_bypass_admin'] : '');
			update_option('mo_customer_validation_wp_login_key',
				isset( $_POST['wp_login_phone_field_key']) ? $_POST['wp_login_phone_field_key'] : '');
			update_option('mo_customer_validation_wp_login_allow_phone_login',
				isset( $_POST['mo_customer_validation_wp_login_allow_phone_login']) ? $_POST['mo_customer_validation_wp_login_allow_phone_login'] : '');
			update_option('mo_customer_validation_wp_login_restrict_duplicates',
				isset( $_POST['mo_customer_validation_wp_login_restrict_duplicates']) ? $_POST['mo_customer_validation_wp_login_restrict_duplicates'] : '');
	    }
	}
	new WPLoginForm;