
<div id="warranty_settings_Websms" class="cvt-accordion Websms_box">
<?php 

		 $wc_warranty = new WooCommerce_Warranty();
		 $wc_warrant_status = $wc_warranty->get_default_statuses();
		 
			  
			 foreach($wc_warrant_status as $ks => $vs)
			 {
				$vs = str_replace(' ', '-', strtolower($vs));			
				$wc_warranty_checkbox = bellsms_get_option('warranty_status_'.$vs, 'Websms_warranty','');
				$wc_warranty_text = bellsms_get_option('sms_text_'.$vs, 'Websms_warranty','');
				
				 ?>		
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_cust_<?php echo $ks; ?>">
				<input type="checkbox" name="Websms_warranty[warranty_status_<?php echo $vs; ?>]" onclick="toggleReadonly(this)" id="Websms_warranty[warranty_status_<?php echo $vs; ?>]" class="notify_box" <?php echo (($wc_warranty_checkbox=='on')?"checked='checked'":''); ?> /><label><?php _e( 'when Order is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>		 
				<div id="accordion_cust_<?php echo $ks; ?>" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<td>
						<div class="Websms_tokens"><a href="#" val="[order_id]">Order Id</a> | <a href="#" val="[rma_number]">RMA Number</a> | <a href="#" val="[rma_status]">RMA status</a> | <a href="#" val="[order_amount]">Order Total</a> | <a href="#" val="[billing_first_name]">First Name</a> | <a href="#" val="[store_name]">Store Name</a> | <a href="#" val="[item_name]">Product Name</a> </div>
						<textarea name="Websms_warranty[sms_text_<?php echo $vs; ?>]" id="Websms_warranty[sms_text_<?php
						echo $vs; ?>]" <?php echo(($wc_warranty_text==$vs)?'' : "readonly='readonly'"); ?> class="fullwidth"><?php 	
				
							echo bellsms_get_option('sms_text_'.$vs, 'Websms_warranty', '') ? bellsms_get_option('sms_text_'.$vs, 'Websms_warranty', '') : WebsmsMessages::DEFAULT_WARRANTY_STATUS_CHANGED; ?></textarea>
						</td>
				        </tr>
					</table>
				</div>
				 <?php
			 }
			 ?>	
		 
		 
		 <script>
		 
		 
		 function insertAtCaret(textFeildValue, txtbox_id) {
				var textObj = document.getElementById(txtbox_id);
				if (document.all) {
					if (textObj.createTextRange && textObj.caretPos) {
						var caretPos = textObj.caretPos;
						caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? textFeildValue + ' ' : textFeildValue;
					}
					else {
						textObj.value = textObj.value + textFeildValue;
					}
				}
				else {
					if (textObj.setSelectionRange) {
						var rangeStart = textObj.selectionStart;
						var rangeEnd = textObj.selectionEnd;
						var tempStr1 = textObj.value.substring(0, rangeStart);
						var tempStr2 = textObj.value.substring(rangeEnd);
			
						textObj.value = tempStr1 + textFeildValue + tempStr2;
					}
					else {
						alert("This version of Mozilla based browser does not support setSelectionRange");
					}
				}
			}
		 
		 //accordion jquery
		 jQuery(document).ready(function() {
					function close_accordion_section() {
							jQuery('.cvt-accordion .expand_btn').removeClass('active');
							jQuery('.cvt-accordion .cvt-accordion-body-content').slideUp(300).removeClass('open');
						}
						
						jQuery('.expand_btn').click(function(e) {
						var currentAttrValue = jQuery(this).parent().attr('data-href');
						if(jQuery(e.target).is('.active')) {
						   close_accordion_section();
						}
						
						else {
						    close_accordion_section();
						    jQuery(this).addClass('active');
						    jQuery('.cvt-accordion ' + currentAttrValue).slideDown(300).addClass('open'); 
						}
						
						e.preventDefault();
					});
					jQuery('.Websms_tokens a').click(function() {
						insertAtCaret(jQuery(this).attr('val'), jQuery(this).parents('td').find('textarea').attr('id'));
						return false;
					});
					
				});
				
				
				
				
				//checkbox click function
				jQuery('.cvt-accordion-body-title input[type="checkbox"]').click(function(e) {
				
					   var childdiv = jQuery(this).parent().attr('data-href');   //if child div have multiple checkbox
				
						if (!jQuery(this).is(':checked')) {
							//select all child div checkbox
							 jQuery(childdiv).find('.notify_box').each(function() {
									this.checked = false; 
							  });
							  
							  jQuery(this).parent().find('.expand_btn.active').trigger('click'); //expand accordion
							
						}
						else {
							//uncheck all child  div checkbox
							 jQuery(childdiv).find('.notify_box').each(function() {
									this.checked = true; 
							  });
							  
							  jQuery(this).parent().find('.expand_btn').not('.active').trigger('click');  //expand accordion
						}
				});
				
				
			
				
				
		 </script>
		 
		 
		 
		 
		 
		 
		 

</div>