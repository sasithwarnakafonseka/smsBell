 <div class="cvt-accordion">
				 <div class="accordion-section">			      
					<?php 
					 foreach($wcbk_order_statuses as $ks => $vs)
					 {
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="Websms_wcbk_general[wcbk_admin_notification_<?php echo $vs; ?>]" id="Websms_wcbk_general[wcbk_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((bellsms_get_option( 'wcbk_admin_notification_'.$vs, 'Websms_wcbk_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Order is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="Websms_tokens"><?php echo WebsmsWcBooking::getWCBookingvariables(); ?></div>
								<textarea name="Websms_wcbk_message[wcbk_admin_sms_body_<?php echo $vs; ?>]" id="Websms_message[admin_sms_body_<?php echo $vs; ?>]" <?php echo((bellsms_get_option( 'wcbk_admin_notification_'.$vs, 'Websms_wcbk_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo bellsms_get_option('wcbk_admin_sms_body_'.$vs, 'Websms_wcbk_message', defined('WebsmsMessages::DEFAULT_WCBK_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_WCBK_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_WCBK_ADMIN_SMS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
				</div>
		   </div>