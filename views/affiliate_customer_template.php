<!-- accordion -->	
<div class="cvt-accordion">
	<div class="accordion-section">
	<?php 
	 foreach($wpam_statuses as $ks => $vs)
	 {
		 $current_val = (is_array($wpam_statuses) && array_key_exists($vs, $wpam_statuses)) ? $wpam_statuses[$vs] : $vs;
		 ?>		
		<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>"><input type="checkbox" name="Websms_wpam_general[wpam_order_status_<?php echo $vs; ?>]" id="Websms_wpam_general[wpam_order_status_<?php echo $vs; ?>]" class="notify_box" 
		<?php echo ((bellsms_get_option( 'wpam_order_status_'.$vs, 'Websms_wpam_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e( 'when Affiliate is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
		<span class="expand_btn"></span>
		</a>		 
		<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
			<table class="form-table">
				<tr valign="top">
				<td><div class="Websms_tokens"><?php echo AffiliateManagerForm::getWPAMvariables('affiliate'); ?></div>
				<textarea name="Websms_wpam_message[wpam_sms_body_<?php echo $vs; ?>]" id="Websms_wpam_message[wpam_sms_body_<?php
				echo $vs; ?>]" <?php echo(($current_val==$vs)?'' : "readonly='readonly'"); ?>><?php 	
		
					echo bellsms_get_option('wpam_sms_body_'.$vs, 'Websms_wpam_message', defined('WebsmsMessages::DEFAULT_WPAM_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_WPAM_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_WPAM_BUYER_SMS_STATUS_CHANGED); ?></textarea>
				</td>
				</tr>
			</table>
		</div>
		 <?php
	 }
	 ?>
	
	<!--transaction status-->
		<?php 
		 foreach($wpam_transaction as $ks => $vs)
		 {
			 
			  $current_val = (is_array($wpam_transaction) && array_key_exists($vs, $wpam_transaction)) ? $wpam_transaction[$vs] : $vs;
			 ?>		
			<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>"><input type="checkbox" name="Websms_wpam_general[wpam_order_status_<?php echo $vs; ?>]" id="Websms_wpam_general[wpam_order_status_<?php echo $vs; ?>]" class="notify_box" 
			<?php echo ((bellsms_get_option( 'wpam_order_status_'.$vs, 'Websms_wpam_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e( 'when Transaction is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
			<span class="expand_btn"></span>
			</a>		 
			<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
				<table class="form-table">
					<tr valign="top">
					<td><div class="Websms_tokens"><?php echo AffiliateManagerForm::getWPAMvariables('transaction'); ?></div>
					<textarea name="Websms_wpam_message[wpam_sms_body_<?php echo $vs; ?>]" id="Websms_wpam_message[wpam_sms_body_<?php
					echo $vs; ?>]" <?php echo(($current_val==$vs)?'' : "readonly='readonly'"); ?>><?php 	
			
						echo bellsms_get_option('wpam_sms_body_'.$vs, 'Websms_wpam_message', defined('WebsmsMessages::DEFAULT_WPAM_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_WPAM_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_WPAM_BUYER_SMS_TRANS_STATUS_CHANGED); ?></textarea>
					</td>
					</tr>
				</table>
			</div>
			 <?php
		 }
		 ?>	
	<!--/transaction status-->
		
	</div>
</div>
<!--end accordion-->	