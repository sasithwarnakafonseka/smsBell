<div class="Websms_wrapper">
	<!-- <strong>Signup For Get API Details - <a href="http://WSW/">Get Your API</a></strong>	 -->
	<table class="form-table">
		<tr valign="top">
			
			<th scrope="row"><?php _e('Account ID',WebsmsConstants::TEXT_DOMAIN); ?>
				<span class="tooltip" data-title="Enter Lanka Bell Account ID"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td style="vertical-align: top;">
				<?php if($islogged){echo $bellsms_API_key;}?>
				<input type="text" name="Websms_gateway[bellsms_API_key]" id="Websms_gateway[bellsms_API_key]" value="<?php echo $bellsms_API_key; ?>" data-id="bellsms_API_key" class="<?php echo $hidden?>">
				<input type="hidden" name="action" value="save_websms_lk_settings" />
				<span class="<?php echo $hidden?>"><?php _e( 'Enter Lanka Bell Account ID', 'Websms' ); ?></span>
			</td>
		</tr>

		<tr valign="top">
			<th scrope="row"><?php _e( 'Account Password', WebsmsConstants::TEXT_DOMAIN ) ?>
				<span class="tooltip" data-title="Lanka Bell Account Password"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td >
				<?php if($islogged){echo $Websms_API_Token;}?>
				<input type="text" name="Websms_gateway[Websms_API_Token]" id="Websms_gateway[Websms_API_Token]" value="<?php echo $Websms_API_Token; ?>" data-id="Websms_API_Token" class="<?php echo $hidden?>">
				<span class="<?php echo $hidden?>"><?php _e( 'Enter Lanka Bell Account Password', WebsmsConstants::TEXT_DOMAIN ); ?></span>
			</td>
		</tr>
		<!-- <tr valign="top">
			<th scrope="row">
			<?php _e( 'WSW Sender Id', WebsmsConstants::TEXT_DOMAIN ) ?>
			<span class="tooltip" data-title="Only available for transactional route"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td >
				<?php if($islogged){echo $Websms_Sender_ID;}?>
				<input type="text" name="Websms_gateway[Websms_Sender_ID]" id="Websms_gateway[Websms_Sender_ID]" value="<?php echo $Websms_Sender_ID; ?>" data-id="Websms_Sender_ID" class="<?php echo $hidden?>">
				<span class="<?php echo $hidden?>"><?php _e( 'Your <b>WSW</b> API Token', WebsmsConstants::TEXT_DOMAIN ); ?></span>
			</td>
		</tr> -->
		<tr valign="top">
			<th scrope="row">
			</th>
			<td >
				<?php if($islogged){?>
				<a href="#" class="button-primary" onclick="logout(); return false;"><?php echo _e( 'Reset Account', WebsmsConstants::TEXT_DOMAIN );?></a>
				<?php }?>
			</td>
		</tr>
	</table>
</div>
				