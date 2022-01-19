<?php
echo '<style>.modal{display:none;position:fixed;z-index:999999999999;padding-top:100px;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{position:relative;background-color:#fefefe;margin:auto;padding:0;border:1px solid #888;width:40%;box-shadow:04px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);-webkit-animation-name:animatetop;-webkit-animation-duration:0.4s;animation-name:animatetop;animation-duration:0.4s}@media  only screen and (max-width: 767px){.modal-content{width:100%}}@-webkit-keyframes animatetop{from{top:-300px;opacity:0}to{top:0;opacity:1}}@keyframes animatetop{from{top:-300px;opacity:0}to{top:0;opacity:1}}.modal-header{background-color:#5cb85c;color:white;}.modal-footer{background-color:#5cb85c;color:white;}.close{float:none;text-align: right;font-size: 30px;cursor: pointer;text-shadow: 0 1px 0 #fff;line-height: 1;font-weight: 700;padding: 2px 5px 5px;}.close:hover {color: #999;}.otp_input{margin-bottom:12px;}.otp_input[type="number"]::-webkit-outer-spin-button, .otp_input[type="number"]::-webkit-inner-spin-button {-webkit-appearance: none;margin: 0;}
.otp_input[type="number"] {-moz-appearance: textfield;}

</style>';
			echo ' <div id="'.$modalName.'" class="modal"><div class="modal-content"><div class="close" id="close">x</div><div class="modal-body"><div id="'.$alert_msg_div.'" style="margin:1em;">EMPTY</div><div id="Websms_validate_field" style="margin:1em"><input type="number" name="'.$otp_input_field_nm.'" autofocus="true" placeholder="" id="Websms_customer_validation_otp_token" class="input-text otp_input" autofocus="true" pattern="[0-9]{3,8}" title="'.$otp_range.'"/>';
			
			echo '<button type="button" name="Websms_otp_validate_submit" style="color:grey; pointer-events:none;" id="'.$validate_otp_btn.'" class="button alt" value="'.$VALIDATE_OTP.'">'.$VALIDATE_OTP.'</button>
			<a style="float:right" id="'.$resend_btn_id.'" onclick="'.$resendFunc.'()">'.$RESEND.'</a><span id="'.$timer_div.'" style="min-width:80px; float:right">00:00 sec</span><br />
			</div></div></div></div>';
			
			echo '<script>
			jQuery("#'.$modalName.' #Websms_customer_validation_otp_token").on("input",function(){
			if(jQuery("#'.$modalName.' #Websms_customer_validation_otp_token").val().match(/^\d{3,8}$/)) { 
				jQuery("#'.$modalName.' #'.$validate_otp_btn.'").removeAttr("style");
			} else{jQuery("#'.$modalName.' #'.$validate_otp_btn.'").css({"color":"grey","pointer-events":"none"}); }}); var interval; jQuery("#'.$modalName.' #close").click(function(){jQuery("#'.$modalName.'").hide();});';
			
            echo '
			jQuery("form #'.$modalName.'").on("focus", "input[type=number]", function (e) {
				jQuery(this).on("wheel.disableScroll", function (e) {
					e.preventDefault();
				});
			});
			jQuery("form #'.$modalName.'").on("blur", "input[type=number]", function (e) {
			jQuery(this).off("wheel.disableScroll");
			});
			</script>';
			
?>			