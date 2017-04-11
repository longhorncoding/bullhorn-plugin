<?php
class BullhornSettingPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Bullhorn Settings Admin', 
            'Bullhorn', 
            'manage_options', 
            'bullhorn-setting', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'bullhorn_option' );
        ?>
        <div class="wrap">
            <h1>Bullhorn REST API Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'bullhorn_option' );
                do_settings_sections( 'bullhorn-setting' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'bullhorn_option', // Option group
            'bullhorn_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Contact Bullhorn to request client ID and secret for authorization.', // Title
            array( $this, 'print_section_info' ), // Callback
            'bullhorn-setting' // Page
        );  

		add_settings_field(
            'client_id', // ID
            'CLIENT ID', // Title 
            array( $this, 'client_id_callback' ), // Callback
            'bullhorn-setting', // Page
            'setting_section_id' // Section           
        ); 
        add_settings_field(
            'client_secret', 
            'CLIENT SECRET', 
            array( $this, 'client_secret_callback' ), 
            'bullhorn-setting', 
            'setting_section_id'
        );  
		
        add_settings_field(
            'bullhorn_user', 
            'Bullhorn Username', 
            array( $this, 'bullhorn_user_callback' ), 
            'bullhorn-setting', 
            'setting_section_id'
        );  
        add_settings_field(
            'bullhorn_pass', 
            'Bullhorn Password', 
            array( $this, 'bullhorn_pass_callback' ), 
            'bullhorn-setting', 
            'setting_section_id'
        );      
		add_settings_field(
            'bullhorn_thank', 
            'Thank You Message', 
            array( $this, 'bullhorn_thank_callback' ), 
            'bullhorn-setting', 
            'setting_section_id'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['client_id'] ) )
            $new_input['client_id'] = sanitize_text_field( $input['client_id'] );

        if( isset( $input['client_secret'] ) )
            $new_input['client_secret'] = sanitize_text_field( $input['client_secret'] );
		
		if( isset( $input['bullhorn_user'] ) )
            $new_input['bullhorn_user'] = sanitize_text_field( $input['bullhorn_user'] );
		if( isset( $input['bullhorn_pass'] ) )
            $new_input['bullhorn_pass'] = sanitize_text_field( $input['bullhorn_pass'] );
		if( isset( $input['bullhorn_thank'] ) )
            $new_input['bullhorn_thank'] = $input['bullhorn_thank'];

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function client_id_callback()
    {
        printf(
            '<input type="text" id="client_id" style="width:300px" name="bullhorn_option[client_id]" value="%s" />',
            isset( $this->options['client_id'] ) ? esc_attr( $this->options['client_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function client_secret_callback()
    {
        printf(
            '<input type="text" id="client_secret" style="width:300px" name="bullhorn_option[client_secret]" value="%s" />',
            isset( $this->options['client_secret'] ) ? esc_attr( $this->options['client_secret']) : ''
        );
    }
	public function bullhorn_user_callback()
    {
        printf(
            '<input type="text" id="bullhorn_user" name="bullhorn_option[bullhorn_user]" value="%s" />',
            isset( $this->options['bullhorn_user'] ) ? esc_attr( $this->options['bullhorn_user']) : ''
        );
    }
	public function bullhorn_pass_callback()
    {
        printf(
            '<input type="text" id="bullhorn_pass" name="bullhorn_option[bullhorn_pass]" value="%s" />',
            isset( $this->options['bullhorn_pass'] ) ? esc_attr( $this->options['bullhorn_pass']) : ''
        );
    }
	public function bullhorn_thank_callback()
    {
        printf(
            '<textarea id="bullhorn_thank" name="bullhorn_option[bullhorn_thank]" style="width:500px" rows="10">%s</textarea>',
            isset( $this->options['bullhorn_thank'] ) ? esc_attr( $this->options['bullhorn_thank']) : ''
        );
    }
}

if( is_admin() )
    $bullhorn_settings_page = new BullhornSettingPage();
