 <div class="cvt-accordion">
				 <div class="accordion-section">			      
					<?php 
					 foreach($wpam_statuses as $ks => $vs)
					 {
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="Websms_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" id="Websms_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((bellsms_get_option( 'wpam_admin_notification_'.$vs, 'Websms_wpam_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Affiliate is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="Websms_tokens"><?php echo AffiliateManagerForm::getWPAMvariables('affiliate'); ?></div>
								<textarea name="Websms_wpam_message[wpam_admin_sms_body_<?php echo $vs; ?>]" id="Websms_message[admin_sms_body_<?php echo $vs; ?>]" <?php echo((bellsms_get_option( 'wpam_admin_notification_'.$vs, 'Websms_wpam_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo bellsms_get_option('wpam_admin_sms_body_'.$vs, 'Websms_wpam_message', defined('WebsmsMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_WPAM_ADMIN_SMS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
				</div>
				
				
				<!--transaction status-->
				  <div class="accordion-section">			      
					<?php 
					 foreach($wpam_transaction as $ks => $vs)
					 {
						 
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="Websms_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" id="Websms_wpam_general[wpam_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((bellsms_get_option( 'wpam_admin_notification_'.$vs, 'Websms_wpam_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Transaction is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="Websms_tokens"><?php echo AffiliateManagerForm::getWPAMvariables('transaction'); ?></div>
								<textarea name="Websms_wpam_message[wpam_admin_sms_body_<?php echo $vs; ?>]" id="Websms_message[admin_sms_body_<?php echo $vs; ?>]" <?php echo((bellsms_get_option( 'wpam_admin_notification_'.$vs, 'Websms_wpam_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo bellsms_get_option('wpam_admin_sms_body_'.$vs, 'Websms_wpam_message', defined('WebsmsMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_WPAM_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_WPAM_ADMIN_SMS_TRANS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
				</div>
				<!--/transaction status-->
				
				
				
		   </div>