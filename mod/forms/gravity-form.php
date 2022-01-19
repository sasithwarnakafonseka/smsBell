<?php
GFForms::include_feed_addon_framework();

class GF_websms_lk extends GFFeedAddOn {

	protected $_version = "2.0.0";
	protected $_min_gravityforms_version = "1.8.20";
	protected $_slug = "gravity-forms-wsw.lk";
	protected $_full_path = __FILE__;
	protected $_title = "smsBell";
	protected $_short_title = "smsBell";
	protected $_multiple_feeds = false;

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
	

	public function feed_settings_title() {
		return __( 'smsBell', 'Websms-gravity-forms' );
	}
	
	public function feed_settings_fields() {
    	return array(
        array(
            'title'  => 'Customer SMS Settings',
            'fields' => array(                
				array(
                    'label'             => 'Customer Numbers',
                    'type'              => 'text',
                    'name'              => 'Websms_gForm_cstmer_nos',
                    'tooltip'           => 'Enter Customer Numbers',
                    'class'             => 'medium merge-tag-support mt-position-right',
                    'feedback_callback' => array( $this, 'is_valid_setting' )
                ),
                array(
                    'label'   => 'Customer Templates',
                    'type'    => 'textarea',
                    'name'    => 'Websms_gForm_cstmer_text',
                    'tooltip' => 'Enter your Customer SMS Content',
                    'class'   => 'medium merge-tag-support mt-position-right'
                ),
            )
        ),
        array(
            'title'  => 'Admin SMS Settings',
            'fields' => array(
               array(
                    'label'             => 'Admin Numbers',
                    'type'              => 'text',
                    'name'              => 'Websms_gForm_admin_nos',
                    'tooltip'           => 'Enter admin Numbers',
                    'class'             => 'medium merge-tag-support mt-position-right',
                    'feedback_callback' => array( $this, 'is_valid_setting' )
                ),
                array(
                    'label'   => 'Admin Templates',
                    'type'    => 'textarea',
                    'name'    => 'Websms_gForm_admin_text',
                    'tooltip' => 'Enter your admin SMS Content',
                    'class'   => 'medium merge-tag-support mt-position-right'
                ),
            )
        ),
	  );
	}
}
new GF_websms_lk();