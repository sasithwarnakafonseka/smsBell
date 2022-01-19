<?php

class WebsmsMessages
{	
	const OTP_BUYER_MESSAGE 	= "Thanks for purchasing\nYour [order_id] is now [order_status]\nThank you";
	const OTP_ADMIN_MESSAGE  	= "You have a new Order\nThe [order_id] is now [order_status]\n";
	const OTP_INVALID_NO 		= 'your verification code is [otp]. Only valid for 15 min.';
	const DEFAULT_ADMIN_SMS_PROCESSING = '[store_name]: You have a new order #[order_id] for order value Rs. [order_amount]. Please check your admin dashboard for complete details.';
	const DEFAULT_ADMIN_SMS_STATUS_CHANGED = '[store_name]: status of order #[order_id] has been changed to [order_status].';
	
	const DEFAULT_ADMIN_SMS_COMPLETED = '[store_name]: Your order #[order_id] Rs. [order_amount]. is completed.';
	const DEFAULT_ADMIN_SMS_ON_HOLD = '[store_name]: Your order #[order_id] Rs. [order_amount]. is On Hold Now.';
	const DEFAULT_ADMIN_SMS_CANCELLED = '[store_name]: Your order #[order_id] Rs. [order_amount]. is Cancelled.';
	const DEFAULT_BUYER_SMS_PROCESSING = 'Hello [billing_first_name], thank you for placing your order #[order_id] with [store_name].';
	const DEFAULT_BUYER_SMS_COMPLETED = 'Hello [billing_first_name], your order #[order_id] with [store_name] has been dispatched and shall deliver to you shortly.';
	const DEFAULT_BUYER_SMS_ON_HOLD = 'Hello [billing_first_name], your order #[order_id] with [store_name] has been put on hold, our team will contact you shortly with more details.';
	const DEFAULT_BUYER_SMS_CANCELLED = 'Hello [billing_first_name], your order #[order_id] with [store_name] has been cancelled due to some un-avoidable conditions. Sorry for the inconvenience caused.';
	const DEFAULT_BUYER_SMS_STATUS_CHANGED = 'Hello [billing_first_name], status of your order #[order_id] with [store_name] has been changed to [order_status].';
	const DEFAULT_WARRANTY_STATUS_CHANGED = 'Hello [billing_first_name], status of your warranty request no. [rma_number] against [order_id] with [store_name] has been changed to [rma_status].';
	const DEFAULT_BUYER_NOTE 	= 'Hello [billing_first_name], a new note has been added to your order #[order_id]: [note]';
	const DEFAULT_BUYER_OTP 	= 'Your verification code is [otp]';
	
	const OTP_SENT_PHONE 		= 'A OTP (One Time Passcode) has been sent to <strong>##phone##</strong> . Please enter the OTP in the field below to verify your phone.';
	const ERROR_OTP_PHONE  		= "There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin.";
	const ERROR_PHONE_FORMAT	= '##phone## is not a valid phone number. Please enter a valid Phone Number';
		
	//Contact Form 7 messages
	const PHONE_MISMATCH	    = "The phone number OTP was sent to and the phone number in contact submission do not match.";
	
	//WP Login Form Messages
	const PHONE_NOT_FOUND	 	= 'Sorry, but you don\'t have a registered phone number.';
	const REGISTER_PHONE_LOGIN 	= 'A new security system has been enabled for you. Please register your phone to continue.';
	const PHONE_EXISTS			= 'Phone Number is already in use. Please use another number.';
	
	//WooCommerce Messages
	const ENTER_PHONE_CODE		= 'Please enter the verification code sent to your phone';
	const ENTER_VERIFY_CODE		= '<strong>Verify Code</strong> is a required field';
	
	//Registration Messages
	const INVALID_OTP 			= 'Invalid one time passcode. Please enter a valid passcode.';
		
	//EDD Default Template
	const DEFAULT_EDD_BUYER_SMS_STATUS_CHANGED = 'EDD:Hello [first_name], status of your [order_id] with [store_name] has been changed to [order_status].';
	const DEFAULT_EDD_ADMIN_SMS_STATUS_CHANGED = '[store_name]: status of order #[order_id] has been changed to [order_status].';
	//woocommerce booking Default Template
	const DEFAULT_WCBK_BUYER_SMS_STATUS_CHANGED = 'Hello [first_name], status of your booking [booking_id] with [store_name] has been changed to [booking_status].';
	const DEFAULT_WCBK_ADMIN_SMS_STATUS_CHANGED = '[store_name]: status of order #[booking_id] has been changed to [booking_status].';
	
	//wp Affiliate Default Template
	const DEFAULT_WPAM_BUYER_SMS_STATUS_CHANGED ='Hello [first_name], status of your affiliate account [affiliate_id] with [store_name] has been changed to [affiliate_status].';
	const DEFAULT_WPAM_ADMIN_SMS_STATUS_CHANGED='[store_name]: status of order #[affiliate_id] has been changed to [affiliate_status].';
	const DEFAULT_WPAM_BUYER_SMS_TRANS_STATUS_CHANGED ='Hello [first_name],commission has been [transaction_type] for [commission_amt] to your affiliate account [affiliate_id] against order #[order_id].';
	const DEFAULT_WPAM_ADMIN_SMS_TRANS_STATUS_CHANGED ='[store_name]: commission has been [transaction_type] for [commission_amt] to affiliate account [affiliate_id] against order #[order_id].';
	
	//learnpress
	const DEFAULT_LPRESS_BUYER_SMS_STATUS_CHANGED='Hello [username], status of your [order_id] with [store_name] has been changed to [order_status].';
    const DEFAULT_LPRESS_ADMIN_SMS_STATUS_CHANGED='[store_name]: status of order #[order_id] has been changed to [order_status].';
    const DEFAULT_USER_COURSE_ENROLL='Congratulation [username], you have enrolled course - [course_name]';
    const DEFAULT_ADMIN_COURSE_ENROLL='Hi Admin [username] has enrolled course - [course_name]';
    const DEFAULT_NEW_TEACHER_REGISTER='Congratulation [username], You have become an instructor.';
    const DEFAULT_ADMIN_NEW_TEACHER_REGISTER='Hi admin, an instructor [username] has been joined.';
    const DEFAULT_USER_COURSE_FINISHED='Congratulation you have finished course - [course_name]';
    const DEFAULT_ADMIN_COURSE_FINISHED='Hi Admin [username] has finished course - [course_name]';
	
	const DEFAULT_NEW_USER_REGISTER ='Hello [username], Thank you for registering with [store_name].';
	const DEFAULT_ADMIN_NEW_USER_REGISTER ='[store_name]: New user signup.
Name: [username]
Email: [email]
Phone: [billing_phone]';
	
		

	function __construct()
	{
		//created an array instead of messages instead of constant variables for Translation reasons.
		define("SALRT_MESSAGES", serialize( array(			
			//General Messages
			"VALID_PHONE_TXT" => "Validate your Phone Number",
			"ERROR_OTP_PHONE" => "There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin.",
			"OTP_RANGE" => "Only digits within range 4-8 are allowed.",
			"GO_BACK" => "&larr; Go Back",
			"ENTER_PHONE_FORMAT"  => "Enter a number in the following format : 9xxxxxxxxx",
			"VERIFY_CODE_TXT"  => "Verify Code :",
			"SEND_OTP"  => "Send OTP",
			"RESEND_OTP"  => "Resend OTP",
			"VALIDATE_OTP"  => "Validate OTP",
			"RESEND"  => "Resend",
			"Enter_Verify_Code"  => "Enter Verification Code",
			"ENABLE_LINK"  => "Please Enter a Phone Number to enable this link",
			"Phone"  => "Phone",
			"ENTER_MOB_NO"  => "Please enter your mobile number",
			"INVALID_OTP"  => "Invalid one time passcode. Please enter a valid passcode.",
			"ENTER_PHONE_CODE"  => "Please enter the verification code sent to your phone.",
			"ENTER_VERIFY_CODE"  => "<strong>Verify Code</strong> is a required field",
			/*translation required*/
			"WPAM_AFFILIATE_approveApplication"  => "Your Affiliate Application has been approved. Thank you so much.", 
			"WPAM_AFFILIATE_blockApplication"  => "Your Affiliate Application has been blocked. Thank you.",
			"WPAM_AFFILIATE_declineApplication"  => "Your Affiliate Application has been declined. Thank you.",
			"WPAM_AFFILIATE_activateAffiliate"  => "Your Affiliate Application has been activated. Thank you.",
			"WPAM_AFFILIATE_deactivateAffiliate"  => "Your Affiliate Application has been deactivated. Thank you.",	
			"WPAM_AFFILIATE_Transaction_ADMIN_approveApplication"  => "An Affiliate %s status has been approved.",
			"WPAM_AFFILIATE_Transaction_ADMIN_blockApplication"  => "An Affiliate %s status has been blocked.",
			"WPAM_AFFILIATE_Transaction_ADMIN_declineApplication"  => "An Affiliate %s status has been declined.",
			"WPAM_AFFILIATE_Transaction_ADMIN_activateAffiliate"  => "An Affiliate %s status has been activated.",
			"WPAM_AFFILIATE_Transaction_ADMIN_deactivateAffiliate"  => "An Affiliate %s status has been deactivated.",
			"WPAM_AFFILIATE_Transaction"  => "Dear %s , The payment for %s has been added to your affiliated account(%s).Please check.",
			"WPAM_AFFILIATE_Transaction_ADMIN_INFO"  => "The payment has been adjusted to account %s for %s.",
			"MAX_OTP_LIMIT"  => "Max OTP resend limit exceeded. Try after 15 minutes.",
			
			/*translation required*/
		)));
	}
	
	
	
	
	public static function showMessage($message , $data=array())
	{
		$displayMessage = "";
		$messages = explode(" ",$message);
		$msg = unserialize(SALRT_MESSAGES);
		return __($msg[$message],WebsmsConstants::TEXT_DOMAIN);
		/* foreach ($messages as $message) 
		{
			if(!WebsmsUtility::isBlank($message))
			{
				//$formatMessage = constant( "self::".$message );
				$formatMessage = $msg[$message];
			    foreach($data as $key => $value)
			    {
			        $formatMessage = str_replace("{{" . $key . "}}", $value ,$formatMessage);
			    }
			    $displayMessage.=$formatMessage;
			}
		}
	    return $displayMessage; */
	}
}	
new WebsmsMessages;