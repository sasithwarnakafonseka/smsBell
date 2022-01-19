<!-- accordion -->	
		   <div class="cvt-accordion">
			<div class="accordion-section">
				
			<?php 
			 foreach($order_statuses as $ks => $vs)
			 {
				$prefix = 'wc-';
				$vs = $ks;
				if (substr($vs, 0, strlen($prefix)) == $prefix) {
					$vs = substr($vs, strlen($prefix));
				}
				$current_val = (is_array($Websms_notification_status) && array_key_exists($vs, $Websms_notification_status)) ? $Websms_notification_status[$vs] : $vs;
				
                
				 ?>		
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>"><input type="checkbox" name="Websms_general[order_status][<?php echo $vs; ?>]" id="Websms_general[order_status][<?php echo $vs; ?>]" class="notify_box" <?php echo (($current_val==$vs)?"checked='checked'":''); ?> value="<?php echo $vs; ?>"/><label><?php _e( 'when Order is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>		 
				<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<td><div class="Websms_tokens"><?php echo $getvariables; ?></div>
						<textarea name="Websms_message[sms_body_<?php echo $vs; ?>]" id="Websms_message[sms_body_<?php
						echo $vs; ?>]" <?php echo(($current_val==$vs)?'' : "readonly='readonly'"); ?>><?php 	
				
							echo bellsms_get_option('sms_body_'.$vs, 'Websms_message', defined('WebsmsMessages::DEFAULT_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_BUYER_SMS_STATUS_CHANGED); ?></textarea>
						</td>
				        </tr>
					</table>
				</div>
				 <?php
			 }
			 ?>	
			 
			 
			 
			
				<?php if ($hasWoocommerce) {?>
					<!-- accordion --5-->
					<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_5">
					<input type="checkbox" name="Websms_general[buyer_notification_notes]" id="Websms_general[buyer_notification_notes]" class="notify_box" <?php echo (($Websms_notification_notes=='on')?"checked='checked'":'')?>/>
					<label><?php _e( 'When a new note is added to order', WebsmsConstants::TEXT_DOMAIN ) ?></label>
					<span class="expand_btn"></span>
					</a>
					<div id="accordion_5" class="cvt-accordion-body-content">
						<table class="form-table">
							<tr valign="top">
							<td>
							<div class="Websms_tokens"><?php echo $getvariables; ?><a href="#" val="[note]">order note</a> </div>
							<textarea name="Websms_message[sms_body_new_note]" id="Websms_message[sms_body_new_note]"><?php echo $sms_body_new_note; ?></textarea>
							</td>
							</tr>
						</table>
					</div>
				<?php }?>
				<!-- accordion --6-->
				<?php
				//if any child is checked then select all check box will checked
				if($Websms_notification_checkout_otp=='on' || $Websms_notification_signup_otp=='on' || $Websms_notification_login_otp=='on')
				{
					$selectallchecked = 'checked';
				}
				else{
					$selectallchecked = '';
				}
				
				?>
				
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_6"> <input type="checkbox" name="selectall" id="selectall" <?php echo $selectallchecked;?> > <label><?php _e( 'Select All OTP', WebsmsConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>
				<div id="accordion_6" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<th scrope="row">
						  <?php if ($hasWoocommerce) {?>
						  <input type="checkbox" name="Websms_general[buyer_checkout_otp]" id="Websms_general[buyer_checkout_otp]" class="notify_box" <?php echo (($Websms_notification_checkout_otp=='on')?"checked='checked'":'')?>/><?php _e( 'OTP for Checkout', WebsmsConstants::TEXT_DOMAIN ) ?><br /><br />
						  <?php }?>
						  <?php if ($hasWoocommerce || $hasWPmembers || $hasUltimate || $hasWPAM || $hasLearnPress) {?>
							<input type="checkbox" name="Websms_general[buyer_signup_otp]" id="Websms_general[buyer_signup_otp]" class="notify_box" <?php echo (($Websms_notification_signup_otp=='on')?"checked='checked'":'')?>/><?php _e( 'OTP for Registration', WebsmsConstants::TEXT_DOMAIN ) ?><br /><br />
						  <?php }?>	
						  <?php if ($hasWoocommerce || $hasWPAM) {?>
							<input type="checkbox" name="Websms_general[buyer_login_otp]" id="Websms_general[buyer_login_otp]" class="notify_box" <?php echo (($Websms_notification_login_otp=='on')?"checked='checked'":'')?>/><?php _e( 'OTP for Login', WebsmsConstants::TEXT_DOMAIN ) ?><br /><br />
						 <?php }?>	
						<!--Login with OTP-->
						 <?php if ($hasWoocommerce || $hasWPAM) {?>
							<input type="checkbox" name="Websms_general[login_with_otp]" id="Websms_general[login_with_otp]" class="notify_box" <?php echo (($login_with_otp=='on')?"checked='checked'":'')?>/><?php _e( 'Login with OTP', WebsmsConstants::TEXT_DOMAIN ) ?><br /><br />
						 <?php }?>	
						<!--/-Login with OTP-->
						<!--OTP FOR Reset Password-->
						 <?php if ($hasWoocommerce || $hasWPAM) {?>
							<input type="checkbox" name="Websms_general[reset_password]" id="Websms_general[reset_password]" class="notify_box" <?php echo (($enable_reset_password=='on')?"checked='checked'":'')?>/><?php _e( 'OTP For Reset Password', WebsmsConstants::TEXT_DOMAIN ) ?>
						 <?php }?>	<br /><br />
						<!--/-OTP FOR Reset Password-->
						
						</th>
						<td>
						<div class="Websms_tokens"><a href="#" val="[otp]">OTP</a> </div>
						<textarea name="Websms_message[sms_otp_send]" id="Websms_message[sms_otp_send]"><?php echo $sms_otp_send; ?></textarea>
							<span><?php _e('You can also define OTP length between 3-8', WebsmsConstants::TEXT_DOMAIN); ?>, eg <code>[otp length="6"]</code></span>
						</td>
				        </tr>
					</table>
				</div>
				<!--user registration-->
				<?php if ($hasWoocommerce) {?>
					<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_7">
					<input type="checkbox" name="Websms_general[registration_msg]" id="Websms_general[registration_msg]" class="notify_box" <?php echo (($Websms_notification_reg_msg=='on')?"checked='checked'":'')?>/>
					<label><?php _e( 'When a new user is registered', WebsmsConstants::TEXT_DOMAIN ) ?></label>
					<span class="expand_btn"></span>
					</a>
					<div id="accordion_7" class="cvt-accordion-body-content">
						<table class="form-table">
							<tr valign="top">
							<td>
							<div class="Websms_tokens"><a href="#" val="[username]">Username</a> | <a href="#" val="[store_name]">Store Name</a>| <a href="#" val="[email]">Email</a>| <a href="#" val="[billing_phone]">Billing Phone</a></div>
							<textarea name="Websms_message[sms_body_registration_msg]" id="Websms_message[sms_body_registration_msg]"><?php echo $sms_body_registration_msg; ?></textarea>
							</td>
							</tr>
						</table>
					</div>
				<?php }?>
				<!--/user registration-->
			</div>
		  </div>
		  <!--end accordion-->	