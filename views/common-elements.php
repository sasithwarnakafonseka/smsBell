<?php
	function extra_post_data($data=null)
	{
		/* $mo_fields 		= array('option','Websms_customer_validation_otp_token','Websms_otp_token_submit','Websms-validate-otp-choice-form'); */
		/* $extrafields1 	= array('user_login','user_email','register_nonce','option','register_tml_nonce'); 
		$extrafields2 	= array('register_nonce','option','form_id','timestamp');  */

		if  (	isset($_SESSION[FormSessionVars::WC_DEFAULT_REG])
				|| 	isset($_SESSION[FormSessionVars::CRF_DEFAULT_REG])
				|| 	isset($_SESSION[FormSessionVars::UULTRA_REG])
				|| 	isset($_SESSION[FormSessionVars::UPME_REG])
				|| 	isset($_SESSION[FormSessionVars::PIE_REG])
				|| 	isset($_SESSION[FormSessionVars::PB_DEFAULT_REG])
				|| 	isset($_SESSION[FormSessionVars::NINJA_FORM])
				|| 	isset($_SESSION[FormSessionVars::USERPRO_FORM])
				||	isset($_SESSION[FormSessionVars::EVENT_REG])
				||  isset($_SESSION[FormSessionVars::BUDDYPRESS_REG])
				||  isset($_SESSION[FormSessionVars::WP_DEFAULT_LOGIN])
				||  isset($_SESSION[FormSessionVars::WP_LOGIN_REG_PHONE])
				|| isset($_SESSION[FormSessionVars::UM_DEFAULT_REG])
				|| isset($_SESSION[FormSessionVars::AFFILIATE_MANAGER_REG])
				|| isset($_SESSION[FormSessionVars::WP_DEFAULT_LOST_PWD])
				|| isset($_SESSION[FormSessionVars::LEARNPRESS_DEFAULT_REG])
			)
		{
			/* foreach ($_POST as $key => $value)
			{
				if(!in_array($key,$mo_fields))
					show_hidden_fields($key,$value);
				
				if(isset($_REQUEST['g-recaptcha-response']))
					 echo '<input type="hidden" name="g-recaptcha-response" value="'.$_POST['g-recaptcha-response'].'" />';
				if(isset($_POST['attendee']))
				{
					$i = 0;
				    while($i<count($_POST['attendee'])){
				    	echo ' <input type="hidden" name="attendee['.$i.'][first_name]" value="'.$_POST["attendee"][$i]["first_name"].'">';
				    	echo ' <input type="hidden" name="attendee['.$i.'][last_name]" value="'.$_POST["attendee"][$i]["last_name"].'">';
				    	$i++;
					}
				}
			} */
			show_hidden_fields($_REQUEST);
		}
		elseif  (	(isset($_SESSION[FormSessionVars::WC_SOCIAL_LOGIN]))
					&& !WebsmsUtility::isBlank($data)
				)
		{
			/* foreach ($data as $key => $value)
			{
				if(!in_array($key, $extrafields2))
					show_hidden_fields($key,$value);
			} */
			show_hidden_fields($data);
		}elseif (	(isset($_SESSION[FormSessionVars::TML_REG])
					|| 	isset($_SESSION[FormSessionVars::WP_DEFAULT_REG]))
					&& !WebsmsUtility::isBlank($_POST)
				)
		{
			/* foreach ($_POST as $key => $value)
			{
				if(!in_array($key, $extrafields1))
					show_hidden_fields($key,$value);
			} */
			show_hidden_fields($_POST);
		}
	}
	
	/**
	 * get Nestedkey and single value from multidimensional array.
	 * created @ 29-06-2019
	 */
	function get_nestedkey_singleVal(array $inputs,$field_key='',&$output=array())
	{		
		foreach($inputs as $input_key => $input_val)
		{
			 if(!is_array($input_val))
			 { 
				 $index = ($field_key!='') ? $field_key.'['.$input_key.']' : $input_key;
				 $output[$index] = $input_val;
			 }
			 else
			 {
				if($field_key!=''){
					get_nestedkey_singleVal($input_val,$field_key.'['.$input_key.']',$output);
				}
				else
				{
					get_nestedkey_singleVal($input_val,$field_key.$input_key,$output);
				}
			 }
		}
	}
	
	function show_hidden_fields($data)
	{
		//form_id removed for um form
		//timestamp removed for um form
		$mo_fields = array('option','Websms_customer_validation_otp_token','Websms_otp_token_submit','Websms-validate-otp-choice-form','user_login','user_email','register_nonce','option','register_tml_nonce','register_nonce','option','submit','Websms_reset_password_btn','Websms_user_newpwd','Websms_user_cnfpwd');
		$results=array();
		get_nestedkey_singleVal($data, '', $results);//adding key
		foreach($results as $fieldname => $result_val)
		{
			if(!in_array($fieldname,$mo_fields))
			{
				//if(!($fieldname == "woocommerce-login-nonce" && $result_val == ""))
				if(!(in_array($fieldname,array("woocommerce-login-nonce","woocommerce-reset-password-nonce")) && $result_val == ""))
					echo '<input type="hidden" name="'.$fieldname.'" value="'.$result_val.'" />'.PHP_EOL;
			}
		}
	}
	
	/* function show_hidden_fields($key,$value)
	{
		if(is_array($value) && $key=='wcmp_vendor_fields' && isset($_POST['wcmp_vendor_fields'])) //wc_marketplace
		{
			foreach ($value as $k => $wcmp_val)
			{
				if(is_array($wcmp_val))
				{
					foreach ($wcmp_val as $t => $val){
						echo '<input type="hidden" name="wcmp_vendor_fields['.$k.']['.$t.']" value="'.$val.'" />';
					}
				}
				
			}
		} 
		elseif(is_array($value)){
			foreach ($value as $t => $val)
				echo '<input type="hidden" name="'.$key.'[]" value="'.$val.'" />';
		}
		elseif(!is_object($value)){	
			echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}
	} */

	function Websms_site_otp_validation_form($user_login,$user_email,$phone_number,$message,$otp_type,$from_both)
	{
		
		$otp_resend_timer = bellsms_get_option( 'otp_resend_timer', 'Websms_general', '15');
		$max_otp_resend_allowed = bellsms_get_option( 'max_otp_resend_allowed', 'Websms_general', '4');
		$params=array(
			'css_url'=>MOV_CSS_URL, 
			'message'=>$message, 
			'user_email'=>$user_email, 
			'phone_number'=>WebsmscURLOTP::checkPhoneNos($phone_number), 
			'otp_type'=>$otp_type, 
			'from_both'=>$from_both, 
			'otp_resend_timer'=>$otp_resend_timer, 
			'max_otp_resend_allowed'=>$max_otp_resend_allowed, 
		);
		echo get_bellsms_template('template/register-otp-template.php',$params);
		exit();
	}
	
	
	function Websms_external_phone_validation_form($goBackURL,$user_email,$message,$form,$usermeta)
	{
		$img = "<div style='display:table;text-align:center;'><img src='".MOV_LOADER_URL."'></div>";
		$params=array(
			'css_url'=>MOV_CSS_URL, 
			'message'=>$message, 
			'user_email'=>$user_email,
			'goBackURL'=>$goBackURL,  
			'form'=>$form, 
			'usermeta'=>$usermeta, 
			'img'=>$img, 
		);
		echo get_bellsms_template('template/otp-popup-hasnophoneno.php',$params);
		exit();
	}
	
	function WebsmsAskForResetPassword($username,$phone_number,$message,$otp_type,$from_both)
	{
		$params=array(
			'css_url'=>MOV_CSS_URL, 
			'message'=>$message, 
			'username'=>$username, 
			'phone_number'=>WebsmscURLOTP::checkPhoneNos($phone_number), 
			'otp_type'=>$otp_type, 
			'from_both'=>$from_both, 
			'user_email'=>'', 
		);
		echo get_bellsms_template('template/reset-password-template.php',$params);
		exit();
	}
	