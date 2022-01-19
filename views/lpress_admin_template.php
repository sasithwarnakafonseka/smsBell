 <div class="cvt-accordion">
				 <div class="accordion-section">			      
					<?php 
					 foreach($lpress_statuses as $ks => $vs)
					 {
						?>		
						<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_<?php echo $ks; ?>"><input type="checkbox" name="Websms_lpress_general[lpress_admin_notification_<?php echo $vs; ?>]" id="Websms_lpress_general[lpress_admin_notification_<?php echo $vs; ?>]" class="notify_box" <?php echo ((bellsms_get_option( 'lpress_admin_notification_'.$vs, 'Websms_lpress_general', 'on')=='on')?"checked='checked'":''); ?>/><label><?php _e('when Order is '.ucwords(str_replace('-', ' ', $vs )), WebsmsConstants::TEXT_DOMAIN ) ?></label>
						<span class="expand_btn"></span>
						</a>		 
						<div id="accordion_<?php echo $ks; ?>" class="cvt-accordion-body-content">
							<table class="form-table">
								<tr valign="top">
								<td><div class="Websms_tokens"><?php echo WebsmsLearnPress::getLPRESSvariables(); ?></div>
								<textarea name="Websms_lpress_message[lpress_admin_sms_body_<?php echo $vs; ?>]" id="Websms_lpress_message[lpress_admin_sms_body_<?php echo $vs; ?>]" <?php echo((bellsms_get_option( 'lpress_admin_notification_'.$vs, 'Websms_lpress_general', 'on')=='on')?'' : "readonly='readonly'"); ?>><?php 
						  echo bellsms_get_option('lpress_admin_sms_body_'.$vs, 'Websms_lpress_message', defined('WebsmsMessages::DEFAULT_LPRESS_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) ? constant('WebsmsMessages::DEFAULT_LPRESS_ADMIN_SMS_'.str_replace('-', '_', strtoupper($vs))) : WebsmsMessages::DEFAULT_LPRESS_ADMIN_SMS_STATUS_CHANGED); 
								
								?></textarea>
								</td>
								</tr>
							</table>
						</div>
						 <?php
					 }
					 ?>	
					 
				<!--course enroll student-->
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_admin_course_enroll">
				<input type="checkbox" name="Websms_lpress_general[admin_course_enroll]" id="Websms_lpress_general[admin_course_enroll]" class="notify_box" <?php echo (($admin_notification_course_enroll=='on')?"checked='checked'":'')?>/>
				<label><?php _e( 'When a student enrolls course', WebsmsConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>
				<div id="accordion_admin_course_enroll" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<td>
						<div class="Websms_tokens"><?php echo WebsmsLearnPress::getLPRESSvariables('courses'); ?></div>
						<textarea name="Websms_lpress_message[sms_body_course_enroll_admin_msg]" id="Websms_lpress_message[sms_body_course_enroll_admin_msg]"><?php echo $sms_body_course_enroll_admin_msg; ?></textarea>
						</td>
						</tr>
					</table>
				</div>
				<!--/course enroll student-->
				
				<!--course finished student-->
				<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_admin_course_finished">
				<input type="checkbox" name="Websms_lpress_general[admin_course_finished]" id="Websms_lpress_general[admin_course_finished]" class="notify_box" <?php echo (($student_notification_course_finished=='on')?"checked='checked'":'')?>/>
				<label><?php _e( 'When a student finishes course', WebsmsConstants::TEXT_DOMAIN ) ?></label>
				<span class="expand_btn"></span>
				</a>
				<div id="accordion_admin_course_finished" class="cvt-accordion-body-content">
					<table class="form-table">
						<tr valign="top">
						<td>
						<div class="Websms_tokens"><?php echo WebsmsLearnPress::getLPRESSvariables('courses'); ?></div>
						<textarea name="Websms_lpress_message[sms_body_course_finished_admin_msg]" id="Websms_lpress_message[sms_body_course_finished_admin_msg]"><?php echo $sms_body_course_finished_admin_msg; ?></textarea>
						</td>
						</tr>
					</table>
				</div>
				<!--/course finished student-->				
				
				<!--become_a_teacher-->
					<a class="cvt-accordion-body-title" href="javascript:void(0)" data-href="#accordion_admin_become_a_teacher">
					<input type="checkbox" name="Websms_lpress_general[admin_become_teacher]" id="Websms_lpress_general[admin_become_teacher]" class="notify_box" <?php echo (($admin_become_teacher=='on')?"checked='checked'":'')?>/>
					<label><?php _e( 'When new teacher created', WebsmsConstants::TEXT_DOMAIN ) ?></label>
					<span class="expand_btn"></span>
					</a>
					<div id="accordion_admin_become_a_teacher" class="cvt-accordion-body-content">
						<table class="form-table">
							<tr valign="top">
							<td>
							<div class="Websms_tokens"><?php echo WebsmsLearnPress::getLPRESSvariables('teacher'); ?></div>
							<textarea name="Websms_lpress_message[sms_body_admin_become_teacher_msg]" id="Websms_lpress_message[sms_body_admin_become_teacher_msg]"><?php echo $sms_body_admin_become_teacher_msg; ?></textarea>
							</td>
							</tr>
						</table>
					</div>
				<!--/become_a_teacher-->
				
				</div>
				
		   </div>