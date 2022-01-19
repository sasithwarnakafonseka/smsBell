<div id="cf7si-sms-sortables" class="meta-box-sortables ui-sortable">
	<h3><?php _e("Admin SMS Notifications"); ?></h3>
	<fieldset>
		<legend><?php _e("In the following fields, you can use these tags:"); ?>
			<br />
			<?php $data['form']->suggest_mail_tags(); ?>
		</legend>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wpcf7-sms-recipient"><?php _e("To:"); ?></label>
					</th>
					<td>
						<input type="text" id="wpcf7-sms-recipient" name="wpcf7Websms-settings[phoneno]" class="wide" size="70" value="<?php echo $data['phoneno']; ?>">
						<br/> <?php _e("<small>Enter Numbers By <code>,</code> for multiple</small>"); ?>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcf7-mail-body"><?php _e("Message body:"); ?></label>
					</th>
					<td>
						<textarea id="wpcf7-mail-body" name="wpcf7Websms-settings[text]" cols="100" rows="6" class="large-text code"><?php echo trim($data['text']); ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	
	<hr/>
	<h3><?php _e("Visitor SMS Notifications"); ?></h3>
	<fieldset>
		<legend><?php _e("In the following fields, you can use these tags:"); ?>
			<br />
			<?php $data['form']->suggest_mail_tags(); ?>
		</legend>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="wpcf7-sms-recipient"><?php _e("Visitor Mobile: "); ?></label>
					</th>
					<td>
						<input type="text" id="wpcf7-sms-recipient" name="wpcf7Websms-settings[visitorNumber]" class="wide" size="70" value="<?php echo @$data['visitorNumber']; ?>">
						<br/> <?php _e("<small>Use <b>CF7 Tags</b> To Get Visitor Mobile Number | Enter Numbers By <code>,</code> for multiple</small>");?>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcf7-mail-body"><?php _e("Message body:"); ?></label>
					</th>
					<td>
						<textarea id="wpcf7-mail-body" name="wpcf7Websms-settings[visitorMessage]" cols="100" rows="6" class="large-text code"><?php echo @$data['visitorMessage']; ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>