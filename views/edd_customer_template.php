<!-- accordion -->	
<div class="cvt-accordion">
	<div class="accordion-section">
	<?php 
	 foreach($edd_order_statuses as $ks => $vs)
	 {
		  $current_val = (is_array($edd_order_statuses) && array_key_exists($vs, $edd_order_statuses)) ? $edd_order_statuses[$vs] : $vs;
		 ?>		
		<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>"><input type="checkbox" name="Websms_edd_general[edd_order_status_<?php echo $vs; ?>]" id="Websms_edd_general[edd_order_status_<?php echo $vs; ?>]" class="notify_box" 
		<?php echo ((bellsms_get_option( 'edd_order_status_'.$vs, 'Websms_edd_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e( 'when Order is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
		<span class="expand_btn"></span>
		</a>		 
		<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
			<table class="form-table">
				<tr valign="top">
				<td><div class="Websms_tokens"><?php echo WebsmsEdd::getEDDVariables(); ?></div>
				<textarea name="Websms_edd_message[edd_sms_body_<?php echo $vs; ?>]" id="Websms_edd_message[edd_sms_body_<?php
				echo $vs; ?>]" <?php echo(($current_val==$vs)?'' : "readonly='readonly'"); ?>><?php 	
		
					echo bellsms_get_option('edd_sms_body_'.$vs, 'Websms_edd_message', defined('WebsmsMessages::DEFAULT_EDD_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_EDD_BUYER_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_EDD_BUYER_SMS_STATUS_CHANGED); ?></textarea>
				</td>
				</tr>
			</table>
		</div>
		 <?php
	 }
	 ?>	
	</div>
</div>
<!--end accordion-->	