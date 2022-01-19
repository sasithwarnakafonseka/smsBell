<?php

if (! defined( 'ABSPATH' )) exit;

class WebsmsEdd
{
		
		public function __construct() {
			require_once WP_PLUGIN_DIR.'/easy-digital-downloads/includes/payments/class-edd-payment.php';
			require_once plugin_dir_path( __DIR__ ).'/includes/edd.php';
			add_action( 'edd_purchase_form_user_info_fields', __CLASS__ . '::Websms_edd_display_checkout_fields' );
			add_action( 'edd_checkout_error_checks', __CLASS__ . '::Websms_edd_validate_checkout_fields', 10, 2 );
			add_filter( 'edd_purchase_form_required_fields', __CLASS__ . '::Websms_edd_required_checkout_fields' );
			add_filter( 'edd_payment_meta', __CLASS__ . '::Websms_edd_store_custom_fields');
			add_action( 'edd_payment_personal_details_list', __CLASS__ . '::Websms_edd_view_order_details', 10, 2 );
			add_action( 'edd_add_email_tags', __CLASS__ . '::Websms_edd_add_phone_tag' );
			add_filter( 'edd_update_payment_status', __CLASS__ . '::trigger_after_update_edd_status');
		}
		
		
		/*edd plugins add phone number*/
		public static  function Websms_edd_display_checkout_fields() {
			
			if( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
			}
			$billing_phone = is_user_logged_in() ? $current_user->billing_phone     : '';
		?>
			<p id="edd-phone-wrap">
				<label class="edd-label" for="edd-phone">Phone Number</label>
				<span class="edd-description">
					Enter your phone number so we can get in touch with you.
				</span>
				<input class="edd-input" type="text" name="billing_phone" id="edd-phone" placeholder="Phone Number" value="<?php echo $billing_phone;?>" />
			</p>
			<?php
		}
		
		

		/**
		 * Make phone number required
		 * Add more required fields here if you need to
		 */
		public static function Websms_edd_required_checkout_fields( $required_fields ) {
			$required_fields['billing_phone'] = array(
				'error_id' => 'invalid_phone',
				'error_message' => 'Please enter a valid Phone number'
			);


			return $required_fields;
		}
		

		/**
		 * Set error if phone number field is empty
		 * You can do additional error checking here if required
		 */
		public static function Websms_edd_validate_checkout_fields( $valid_data, $data ) {
			if ( empty( $data['billing_phone'] ) ) {
				edd_set_error( 'invalid_phone', 'Please enter your phone number.' );
			}
		}
		

		/**
		 * Store the custom field data into EDD's payment meta
		 */
		public static  function Websms_edd_store_custom_fields( $payment_meta ) {

			if( did_action( 'edd_purchase' ) ) {
				$payment_meta['phone'] = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';
			}

			return $payment_meta;
		}
		


		/**
		 * Add the phone number to the "View Order Details" page
		 */
		public static function Websms_edd_view_order_details( $payment_meta, $user_info ) {
			$phone = isset( $payment_meta['phone'] ) ? $payment_meta['phone'] : 'none';
		?>
			<div class="column-container">
				<div class="column">
					<strong>Phone: </strong>
					 <?php echo $phone; ?>
				</div>
			</div>
		<?php
		}
		

		/**
		 * Add a {phone} tag for use in either the purchase receipt email or admin notification emails
		 */
		public static function Websms_edd_add_phone_tag() {

			edd_add_email_tag( 'phone', 'Customer\'s phone number', 'Websms_edd_tag_phone' );
		}
		

		/**
		 * The {phone} email tag
		 */
		public static function Websms_edd_tag_phone( $payment_id ) {
			$payment_data = edd_get_payment_meta( $payment_id );
			return $payment_data['phone'];
		}
		
		/*edd plugins add phone number ends*/	
		
		
	   public static function getEDDVariables()
	   {
			$variables = array(
							'[order_id]' 	=> 'Order Id',
							'[order_status]' 	=> 'Order Status',
							'[edd_payment_total]' => 'Order amount',
							'[store_name]' 		=> 'Store Name',
							'[edd_payment_mode]' 	=> 'Payment Mode',
							'[edd_payment_gateway]' => 'Payment Gateway',
							'[first_name]' 		=> 'Billing First Name',
							'[last_name]' 		=> 'Billing Last Name',
							'[item_name]' 		=> 'Item Name',
							'[currency]' 		=> 'Currency',
			);
			
			
			$ret_string = '';
			foreach($variables as $vk => $vv)
			{
				$ret_string .= sprintf( "<a href='#' val='%s'>%s</a> | " , $vk , __($vv,WebsmsConstants::TEXT_DOMAIN));
			}
			return $ret_string;
	   }
		
		/**send sms after payment actions**/
		
		public static function trigger_after_update_edd_status($payment_id)
		{
			
			$payments = new EDD_Payment( $payment_id );
			$status  = edd_get_payment_status($payment_id,true);
			$admin_send = bellsms_get_option('edd_admin_notification_'.$status, 'Websms_edd_general');
			$cst_send = bellsms_get_option('edd_order_status_'.$status, 'Websms_edd_general');
			
			if($cst_send=='on')
			{
				$content = bellsms_get_option( 'edd_sms_body_'.$status, 'Websms_edd_message');
				$content = self::pharse_sms_body( $content, $payment_id);
				$meta    = $payments->get_meta();
				if(array_key_exists('phone',$meta) && $meta['phone']!='')
				{
					$edd_data=array();
					$edd_data['number'] = $meta['phone'];
					$edd_data['sms_body'] = $content;
					WebsmscURLOTP::sendsms($edd_data);
				}
			}
			
			if($admin_send=='on')
			{
				
				$admin_phone_number     = bellsms_get_option( 'sms_admin_phone', 'Websms_message', '' );
				$content = bellsms_get_option( 'edd_admin_sms_body_'.$status, 'Websms_edd_message');
				$content = self::pharse_sms_body( $content, $payment_id);
				if($admin_phone_number!='')
				{
					$edd_data=array();
					$edd_data['number'] = $admin_phone_number;
					$edd_data['sms_body'] = $content;
					WebsmscURLOTP::sendsms($edd_data);
				}
			}
		}
		
		public static function pharse_sms_body( $content, $payment_id) 
		{
			
			$payments = new EDD_Payment( $payment_id );
			$user_info    = $payments->get_meta();
			$order_variables    = get_post_custom( $payment_id);
			$order_status  = edd_get_payment_status($payment_id,true);
			
			$variables = array
			(
				'[order_id]' => $payment_id,
				'[order_status]' 	=> $order_status,
				'[store_name]' 		=> get_bloginfo(),
				'[first_name]' 		=> $user_info['user_info']['first_name'],
				'[last_name]' 		=> $user_info['user_info']['last_name'],
			);
			$content = str_replace( array_keys($variables), array_values($variables), $content );
			
			
			foreach ($order_variables as &$value) {
				$value = $value[0];
			}
			unset($value);
			
			$order_variables = array_combine(
				array_map(function($key){ return '['.ltrim($key, '_').']'; }, array_keys($order_variables)),
				$order_variables
			);
			$content = str_replace( array_keys($order_variables), array_values($order_variables), $content );
			return $content;
		}
		
		
		
}
new WebsmsEdd;
