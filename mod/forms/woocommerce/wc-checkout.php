<?php
	class WooCommerceCheckOutForm extends FormInterface
	{
		
		private $guestCheckOutOnly;
		private $showButton;
		private $formSessionVar = FormSessionVars::WC_CHECKOUT;
		private $popupEnabled;
		private $paymentMethods;
		private $otp_for_selected_gateways;

		function handleForm()
		{	 
			add_action( 'woocommerce_checkout_process', array($this,'my_custom_checkout_field_process'));

			$this->paymentMethods 				= maybe_unserialize(bellsms_get_option( 'checkout_payment_plans', 'Websms_general' ));
			$this->otp_for_selected_gateways 	= (bellsms_get_option('otp_for_selected_gateways', 'Websms_general')=="on") ? TRUE : FALSE;
			
			$this->popupEnabled					= (bellsms_get_option('checkout_otp_popup', 'Websms_general')=="on") ? TRUE : FALSE;
			$this->guestCheckOutOnly			= (bellsms_get_option('checkout_show_otp_guest_only', 'Websms_general')=="on") ? TRUE : FALSE;
			$this->showButton 					= (bellsms_get_option('checkout_show_otp_button', 'Websms_general')=="on") ? TRUE : FALSE;

			if($this->popupEnabled)  add_action( 'woocommerce_after_checkout_billing_form' , array($this,'add_custom_popup') 		, 99		);
			if($this->popupEnabled)  add_action( 'woocommerce_review_order_after_submit'   , array($this,'add_custom_button')		, 1, 1	);
			if(!$this->popupEnabled) add_action( 'woocommerce_after_checkout_billing_form' , array($this,'my_custom_checkout_field'), 99		);
			
			if($this->otp_for_selected_gateways==TRUE)
				add_action( 'wp_enqueue_scripts', array($this,'enqueue_script_on_page'));
			$this->routeData();
		}

		public static function isFormEnabled()
		{
			return (bellsms_get_option('buyer_checkout_otp', 'Websms_general')=="on") ? true : false;
		}

		function routeData()
		{
			if(!array_key_exists('option', $_GET)) return;
			if(strcasecmp(trim($_GET['option']),'Websms-woocommerce-checkout') == 0) $this->handle_woocommere_checkout_form($_POST);
		}
		
		function handle_woocommere_checkout_form($getdata)
		{
			WebsmsUtility::checkSession();
			WebsmsUtility::initialize_transaction($this->formSessionVar);
			Websms_site_challenge_otp('test',$getdata['user_email'],null, trim($getdata['user_phone']),"phone");
		}

		function checkIfVerificationNotStarted()
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])){
				wc_add_notice(  WebsmsMessages::showMessage('ENTER_VERIFY_CODE'), 'error' );
				return TRUE;
			}
			return FALSE;
		}

		function checkIfVerificationCodeNotEntered()
		{
			if(array_key_exists('order_verify', $_POST) && isset($_POST['order_verify'])) return FALSE;

			wc_add_notice( WebsmsMessages::showMessage('ENTER_PHONE_CODE'), 'error' );
			return TRUE;
		}

		function add_custom_button($order_id)
		{
			if($this->guestCheckOutOnly && is_user_logged_in())  return;
			$this->show_validation_button_or_text(TRUE);
			$this->common_button_or_link_enable_disable_script();
			$otp_resend_timer = bellsms_get_option( 'otp_resend_timer', 'Websms_general', '15');
			$validate_before_send_otp = bellsms_get_option( 'validate_before_send_otp', 'Websms_general', 'off');
			
			echo ',counterRunning=false, $mo(".woocommerce-error").length>0&&$mo("html, body").animate({scrollTop:$mo("div.woocommerce").offset().top-50},1e3),';
			
			echo '$mo("#Websms_otp_token_submit").click(function(o){ if(counterRunning){$mo("#myModal").show();return false;}';
				if($validate_before_send_otp=='on')
				{
					echo '$mo(".validate-required").find(":input,select").trigger("change");
					var error = $mo(".woocommerce-billing-fields .validate-required").not(".woocommerce-validated").find("input:not(:hidden)").length;
					
					error=error + parseInt($mo(".woocommerce-account-fields .validate-required").not(".woocommerce-validated").find("input:not(:hidden)").length);
					
					if($mo(".validate-required #terms").length> 0 && $mo(".validate-required #terms").prop("checked")==false)
					{
						error=error + 1;
					}
					if($mo("#ship-to-different-address-checkbox").prop("checked")==true)
					{
						error=error + parseInt($mo(".woocommerce-shipping-fields .validate-required").not(".woocommerce-validated").find("input:not(:hidden)").length);
					}
					if(error>0){
						$mo(".validate-required").not(".woocommerce-validated").find(":input,select").eq(0).focus();return false;
					}';
				}
				
				echo 'var e=$mo("input[name=billing_email]").val(),m=$mo(this).parents("form").find("input[name=billing_phone]").val(),a=$mo("div.woocommerce");a.addClass("processing").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),$mo.ajax({url:"'.site_url().'/?option=Websms-woocommerce-checkout",type:"POST",data:{user_email:e,user_phone:m},crossDomain:!0,dataType:"json",success:function(o){
				"success"==o.result?($mo(".blockUI").hide(),$mo("#Websms_message").empty().removeClass("woocommerce-error"),$mo("#Websms_message").append(o.message),$mo("#Websms_message").addClass("woocommerce-message"),$mo("#myModal").show(), $mo("#myModal #Websms_validate_field").show(),$mo("#mo_customer_validation_otp_token").focus(),timerCount(),counterRunning=true):($mo(".blockUI").hide(),$mo("#Websms_message").empty(),$mo("#Websms_message").append(o.message),$mo("#Websms_message").addClass("woocommerce-error"),$mo("#myModal #Websms_validate_field").hide(),$mo("#myModal").show());
			
			
		   	function timerCount()
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
				document.getElementById("timer").style.display = "block";
				document.getElementById("timer").innerHTML = timer('.$otp_resend_timer.')+" sec";
				var counter = '.$otp_resend_timer.';
				 interval = setInterval(function() {
					counter--;
					document.getElementById("timer").innerHTML = timer(counter)+ " sec";
					if (counter == 0) {
						counterRunning=false;
						document.getElementById("timer").style.display = "none";
						var cssString = "pointer-events: auto; cursor: pointer; opacity: 1; float:right"; 
						document.getElementById("verify_otp").style.cssText = cssString;
						clearInterval(interval);
					}
					else
					{
						document.getElementById("verify_otp").style.cssText = "pointer-events: none; cursor: default; opacity: 1; float:right";
					}
				}, 1000);
			}
			function stoptimer(obj)
			{
				clearInterval(obj);
			}
			},error:function(o,e,m){}}),o.preventDefault()}),';
			
			echo '$mo("#Websms_otp_validate_submit").click(function(o){counterRunning=false,clearInterval(interval),$mo("#myModal").hide(),$mo("#Websms_message").removeClass("woocommerce-message"),$mo("#myModal #Websms_validate_field").hide(),$mo(".woocommerce-checkout").submit()});});';
			echo ($this->otp_for_selected_gateways && $this->popupEnabled) ? '' : 'jQuery("input[name=woocommerce_checkout_place_order], button[name=woocommerce_checkout_place_order]").hide();';
			echo '</script>';
		}

		function add_custom_popup()
		{
			if($this->guestCheckOutOnly && is_user_logged_in())  return;
			$params=array(
				'otp_range'=>WebsmsMessages::showMessage('OTP_RANGE'), 
				'VALIDATE_OTP'=>WebsmsMessages::showMessage('VALIDATE_OTP'), 
				'RESEND'=>WebsmsMessages::showMessage('RESEND'),
				'modalName'=>'myModal',
				'alert_msg_div'=>'Websms_message',
				'timer_div'=>'timer',
				'resendFunc'=>'resendotp',
				'validate_otp_btn'=>'Websms_otp_validate_submit',
				'resend_btn_id'=>'verify_otp',
				'otp_input_field_nm'=>'order_verify',
				
			);
			echo get_bellsms_template('template/otp-popup-1.php',$params);
			echo '<script>function resendotp()
			{
				jQuery("#Websms_otp_token_submit").trigger("click");
			}
			</script>'; 
		}

		function my_custom_checkout_field( $checkout )
		{
			if($this->guestCheckOutOnly && is_user_logged_in())  return;


			echo '<div id="mo_message" hidden></div>';
			$this->show_validation_button_or_text();	
			woocommerce_form_field( 'order_verify', array(
	        'type'          => 'text',
	        'class'         => array('form-row-wide'),
	        'label'         => WebsmsMessages::showMessage('VERIFY_CODE_TXT'),
	        'required'  	=> true,
	        'placeholder'   => WebsmsMessages::showMessage('Enter_Verify_Code'),
	        ), $checkout->get_value( 'order_verify' ));
			
	        $this->common_button_or_link_enable_disable_script();

			echo ',$mo(".woocommerce-error").length>0&&$mo("html, body").animate({scrollTop:$mo("div.woocommerce").offset().top-50},1e3),$mo("#Websms_otp_token_submit").click(function(o){var e=$mo("input[name=billing_email]").val(),n=$mo(this).parents("form").find("input[name=billing_phone]").val(),a=$mo("div.woocommerce");a.addClass("processing").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),$mo.ajax({url:"'.site_url().'/?option=Websms-woocommerce-checkout",type:"POST",data:{user_email:e, user_phone:n},crossDomain:!0,dataType:"json",success:function(o){ if(o.result=="success"){$mo(".blockUI").hide(),$mo("#mo_message").empty(),$mo("#mo_message").append(o.message),$mo("#mo_message").addClass("woocommerce-message"),$mo("#mo_message").show(),$mo("#order_verify").focus()}else{$mo(".blockUI").hide(),$mo("#mo_message").empty(),$mo("#mo_message").append(o.message),$mo("#mo_message").addClass("woocommerce-error"),$mo("#mo_message").show();} ;},error:function(o,e,n){}}),o.preventDefault()});});</script>';
		}

		function show_validation_button_or_text($popup=FALSE)
		{
			if(!$this->showButton) $this->showTextLinkOnPage();
			if($this->showButton) $this->showButtonOnPage();
		}

		function showTextLinkOnPage()
		{
			$otp_verify_btn_text = bellsms_get_option( 'otp_verify_btn_text', 'Websms_general', '');
			echo '<div title="'.WebsmsMessages::showMessage('ENABLE_LINK').'"><a href="#" style="text-align:center;color:grey;pointer-events:none;" id="Websms_otp_token_submit" class="" >'.$otp_verify_btn_text.'</a></div>';
		}

		function showButtonOnPage()
		{
			$otp_verify_btn_text = bellsms_get_option( 'otp_verify_btn_text', 'Websms_general', '');
			echo '<button type="button" class="button alt" id="Websms_otp_token_submit" disabled title="'
				.WebsmsMessages::showMessage('ENABLE_LINK').'" value="'
				.$otp_verify_btn_text.'" >'.$otp_verify_btn_text.'</button>';
		}

		function common_button_or_link_enable_disable_script()
		{
			echo '<script> jQuery(document).ready(function() {$mo = jQuery,';
	        echo '$mo(".woocommerce-message").length>0&&($mo("#order_verify").focus(),$mo("#mo_message").addClass("woocommerce-message"),$mo("#mo_message").show());';
			if(!$this->showButton) $this->enabledDisableScriptForTextOnPage();
			if($this->showButton) $this->enableDisableScriptForButtonOnPage();
		}

		function enabledDisableScriptForTextOnPage()
		{
			echo '""!=$mo("input[name=billing_phone]").val()&&$mo("#Websms_otp_token_submit").removeAttr("style"); $mo("input[name=billing_phone]").keyup(function(){
			if($mo(this).val().match('.WebsmsConstants::getPhonePattern().')) { $mo("#Websms_otp_token_submit").removeAttr("style");} else{$mo("#Websms_otp_token_submit").css({"color":"grey","pointer-events":"none"}); }
			})';
		}

		function enableDisableScriptForButtonOnPage()
		{
			echo '""!=$mo("input[name=billing_phone]").val()&&$mo("#Websms_otp_token_submit").prop( "disabled", false ); $mo("input[name=billing_phone]").keyup(function() {if($mo(this).val().match('.WebsmsConstants::getPhonePattern().')) {$mo("#Websms_otp_token_submit").prop( "disabled", false );} else { $mo("#Websms_otp_token_submit").prop( "disabled", true ); }})';
		}

		function my_custom_checkout_field_process()
		{
			if($this->guestCheckOutOnly && is_user_logged_in()) return; 
			if(!$this->isPaymentVerificationNeeded()) return;
			if($this->checkIfVerificationNotStarted()) return;
			if($this->checkIfVerificationCodeNotEntered()) return;
			$this->handle_otp_token_submitted(FALSE);		
		}

		function handle_otp_token_submitted($error)
		{
			$error = $this->processPhoneNumber();
			if(!$error) $this->processOTPEntered();
		}

		function isPaymentVerificationNeeded()
		{
			if(!$this->otp_for_selected_gateways)
				return true;
			
			$payment_method = $_POST['payment_method'];
			return in_array($payment_method,$this->paymentMethods);
		}

		function processPhoneNumber()
		{
			WebsmsUtility::checkSession();
			if(array_key_exists('phone_number_mo', $_SESSION) 
					&& strcasecmp($_SESSION['phone_number_mo'], $_POST['billing_phone'])!=0)
			{
				wc_add_notice(  WebsmsMessages::showMessage('PHONE_MISMATCH'), 'error' );
				return TRUE;
			}
		}

		function handle_failed_verification($user_login,$user_email,$phone_number)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			wc_add_notice( WebsmsUtility::_get_invalid_otp_method(), 'error' );
		}

		function handle_post_verification($redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data)
		{
			WebsmsUtility::checkSession();
			if(!isset($_SESSION[$this->formSessionVar])) return;
			$this->unsetOTPSessionVariables();
		}

		function enqueue_script_on_page()
		{
			wp_register_script( 'wccheckout', MOV_URL . 'js/wccheckout.min.js' , array('jquery') ,'1.1',true);
			wp_localize_script( 'wccheckout', 'otp_for_selected_gateways', array(
				'paymentMethods' => $this->paymentMethods,
				'ask_otp' => ($this->guestCheckOutOnly && is_user_logged_in() ? false : true),
			));
			wp_enqueue_script('wccheckout');
		}

		function processOTPEntered()
		{
			$this->validateOTPRequest();	
		}

		function validateOTPRequest()
		{
			do_action('Websms_validate_otp','order_verify');
		}

		public function unsetOTPSessionVariables()
		{
			unset($_SESSION[$this->txSessionId]);
			unset($_SESSION[$this->formSessionVar]);
		}

		public function is_ajax_form_in_play($isAjax)
		{
			WebsmsUtility::checkSession();
			return isset($_SESSION[$this->formSessionVar]) ? TRUE : $isAjax;
		}

		function handleFormOptions()
		{
			update_option('mo_customer_validation_wc_checkout_enable',
				isset( $_POST['mo_customer_validation_wc_checkout_enable']) ? $_POST['mo_customer_validation_wc_checkout_enable'] : 0);
			update_option('mo_customer_validation_wc_checkout_type',
				isset(  $_POST['mo_customer_validation_wc_checkout_type']) ? $_POST['mo_customer_validation_wc_checkout_type'] : '');
			update_option('mo_customer_validation_wc_checkout_guest',
				isset(  $_POST['mo_customer_validation_wc_checkout_guest']) ? $_POST['mo_customer_validation_wc_checkout_guest'] : '');
			update_option('mo_customer_validation_wc_checkout_button',
				isset(  $_POST['mo_customer_validation_wc_checkout_button']) ? $_POST['mo_customer_validation_wc_checkout_button'] : '');
			update_option('mo_customer_validation_wc_checkout_popup',
				isset(  $_POST['mo_customer_validation_wc_checkout_popup']) ? $_POST['mo_customer_validation_wc_checkout_popup'] : '');
		}
	}
	new WooCommerceCheckOutForm;