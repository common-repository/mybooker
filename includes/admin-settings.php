<?php
class Mybooker_Admin
{
    /**
     * Path to the admin page templates.
     * @var string
     */
    private $template_path;
    public $options;

    /**
     * Constructor.
     * @param string $template_path
     */
    public function __construct()
    {
        $this->configure();
        $this->options = get_option('mybooker_settings');
    }

    /**
     * Configure the admin page using the Settings API.
     */
    public function configure()
    {
        register_setting('booking_email', 'mybooker_settings', 'rfi_email_validate');
        register_setting('booking_form', 'mybooker_settings');
        register_setting('booking_payment', 'mybooker_settings');
        register_setting('booking_currency', 'mybooker_settings');
        add_settings_section(
              'booking_form_section',
              esc_html__('Booking Form Configuration', 'mybooker'),
              array($this, 'booking_form_section_callback'),
              'booking_form'
          );

        add_settings_field(
              'booking_na_text',
              esc_html__('Non bookable text.', 'mybooker'),
              array($this, 'booking_form_na_text_render'),
              'booking_form',
              'booking_form_section'
          );

        add_settings_field(
            'booking_style_enable',
            esc_html__('Enable Custom Style', 'mybooker'),
            array($this, 'booking_form_style_render'),
            'booking_form',
            'booking_form_section'
        );

        add_settings_section(
              'booking_email_section',
              esc_html__('Booking Email Configuration', 'mybooker'),
              array($this, 'booking_email_section_callback'),
              'booking_email'
          );

        add_settings_field(
              'booking_email_from',
              esc_html__('From e-mail', 'mybooker'),
              array($this, 'booking_email_from_render'),
              'booking_email',
              'booking_email_section'
          );

        add_settings_field(
              'booking_email_cc',
              esc_html__('Also Send E-mail To', 'mybooker'),
              array($this, 'booking_email_cc_render'),
              'booking_email',
              'booking_email_section'
          );

        add_settings_field(
              'booking_email_name',
              esc_html__('From Name', 'mybooker'),
              array($this, 'booking_email_name_render'),
              'booking_email',
              'booking_email_section'
          );

        add_settings_field(
              'booking_email_subject',
              esc_html__('Email subject', 'mybooker'),
              array($this, 'booking_email_subject_render'),
              'booking_email',
              'booking_email_section'
          );

        add_settings_field(
              'booking_email_message',
              esc_html__('Email Message', 'mybooker'),
              array($this, 'booking_email_message_render'),
              'booking_email',
              'booking_email_section'
          );

        /** new section **/
        add_settings_section(
              'booking_currency_section',
              esc_html__('Default Currency', 'mybooker'),
              array($this, 'booking_currency_section_callback'),
              'booking_currency'
          );

        add_settings_field(
              'booking_currency_type',
              esc_html__('Select Currency', 'mybooker'),
              array($this, 'booking_currency_type_render'),
              'booking_currency',
              'booking_currency_section'
          );

    }

    /**
     * Get the capability required to view the admin page.
     *
     * @return string
     */
    public function get_capability()
    {
        return 'install_plugins';
    }

    /**
     * Get the title of the admin page in the WordPress admin menu.
     *
     * @return string
     */
    public function get_menu_title()
    {
        return esc_html__('Bookings', 'mybooker');
    }

    /**
     * Get the title of the admin page.
     *
     * @return string
     */
    public function get_page_title()
    {
        return esc_html__('Bookings', 'mybooker');
    }

    /**
     * Get the parent slug of the admin page.
     *
     * @return string
     */
    public function get_parent_slug()
    {
        return '';
    }

    /**
     * Get the slug used by the admin page.
     *
     * @return string
     */
    public function get_slug()
    {
        return 'mybooker';
    }

    /**
    * Renders the option field.
    */
    public function render_option_field()
    {
        $this->render_template('option_field');
    }

    /**
     * Render the plugin's admin page.
     */
    // public function render_page()
    // {
    //     $this->render_template('page');
    // }

    /**
     * Render the top section of the plugin's admin page.
     */
    public function render_section()
    {
        $this->render_template('section');
    }

    /**
   * Renders the given template if it's readable.
   *
   * @param string $template
   */
    private function render_template($template)
    {
        $template_path = $this->template_path . '/' . $template . '.php';

        if (!is_readable($template_path)) {
            return;
        }

        include $template_path;
    }

    public function booking_form_na_text_render()
    {
        //$options = get_option('mybooker_settings');?>
        <fieldset>
            <label for="mybooker_settings[booking_na_text][short]" class="w7"><?php echo esc_html__('Short text', 'mybooker') ?></label>
            <input type='text' name='mybooker_settings[booking_na_text][short]' class="small-text" value='<?php echo esc_attr($this->options['booking_na_text']['short']); ?>'>
            <br />
            <label for="mybooker_settings[booking_na_text][long]" class="w7"><?php echo esc_html__('Long text', 'mybooker') ?></label>
            <input type='text' name='mybooker_settings[booking_na_text][long]' class="regular-text" value='<?php echo esc_attr($this->options['booking_na_text']['long']); ?>'>
            <p class="description"><?php echo esc_html__('Short text will be shown in the booking slot and long text is shown on mouse over. Non bookable text is generic and have effect for all non bookable items.', 'mybooker') ?></p>
        </fieldset>
    	 <?php
    }

    public function booking_form_style_render() {
    ?>
        <input type='checkbox' name='mybooker_settings[booking_style_enable]' <?php esc_attr(checked($this->options['booking_style_enable'], 1)); ?> value='1'>
        <span class="description"><?php echo esc_html__('Please check to enable plugin custom style.', 'mybooker') ?></span>
    <?php
    }


    public function booking_email_from_render() {
    ?>
      	<input type='text' name='mybooker_settings[booking_email_from]' class="regular-text" value='<?php echo esc_attr($this->options['booking_email_from']); ?>'>
      	<p class="description"><?php echo esc_html__('Enter the email you want to display as "from" in the email send', 'mybooker') ?></p>
    	 <?php
    }


    public function booking_email_cc_render() {
    ?>
      	<input type='text' name='mybooker_settings[booking_email_cc]' class="regular-text" value='<?php echo esc_attr($this->options['booking_email_cc']); ?>'>
      	<p class="description"><?php echo esc_html__('If you would like a copy of the email send to the the user who made the booking please enter the email address here. The copy is send Bcc', 'mybooker') ?></p>
    	 <?php
    }


    public function booking_email_name_render(){
    ?>
      	<input type='text' name='mybooker_settings[booking_email_name]' class="regular-text" value='<?php echo esc_attr($this->options['booking_email_name']); ?>'>
      	<p class="description"><?php echo esc_html__('Enter the name you want to display as from in the email send', 'mybooker') ?></p>
    <?php
    }

    public function booking_email_subject_render()
    {
        //$options = get_option('mybooker_settings');
        ?>
      	<input type='text' name='mybooker_settings[booking_email_subject]' class="regular-text" value="<?php echo esc_attr($this->options['booking_email_subject']); ?>">
      	<p class="description"><?php echo esc_html__('Enter the subject for the email send', 'mybooker'); ?></p>
    	  <?php
    }

    public function booking_email_message_render()
    {
        ?>
    	<textarea name="mybooker_settings[booking_email_message]" rows="10" cols="80" id="mybooker_settings[booking_email_message]" class="medium-text code"><?php echo esc_attr($this->options['booking_email_message']); ?></textarea>
    	<p class="date-time-doc">
        <?php echo esc_html__('You can use tags to add booking details into the email message content.', 'mybooker') ?>
        <br /><?php echo esc_html__('Allowed tags : ', 'mybooker') ?>
        <em>
          <?php echo esc_html__('[booking_email], [booking_name], [booking_datetime], [item_name], [price], [slots], [total]', 'mybooker') ?>
        </em><br />
        <!-- <a href="#">Documentation on tags formatting</a> -->
      </p>
    <?php
    }


    public function booking_payment_stripe_test_mode_render() {
    ?>
    	  <input type='checkbox' name='mybooker_settings[booking_payment_stripe_test_mode]' <?php checked(esc_attr($this->options['booking_payment_stripe_test_mode']), 1); ?> value='1'>
        <span class="description"><?php echo esc_html__('Please check to enable plugin custom style.', 'mybooker') ?></span>
    	  <?php
    }


    public function booking_payment_stripe_api_key_render()
    {
        //$options = get_option('mybooker_settings');?>
    	<input type='text' name='mybooker_settings[booking_payment_stripe_api_key]' class="regular-text" value='<?php echo esc_attr($this->options['booking_payment_stripe_api_key']); ?>'>
    	<?php
    }
    public function booking_payment_stripe_api_secret_render()
    {
        //$options = get_option('mybooker_settings');?>
    	<input type='text' name='mybooker_settings[booking_payment_stripe_api_secret]' class="regular-text" value='<?php echo esc_attr($this->options['booking_payment_stripe_api_secret']); ?>'>
    	<?php
    }

    public function booking_payment_stripe_api_test_key_render()
    {
        //$options = get_option('mybooker_settings');?>
    	<input type='text' name='mybooker_settings[booking_payment_stripe_api_test_key]' class="regular-text" value='<?php echo esc_attr($this->options['booking_payment_stripe_api_test_key']); ?>'>
    	<?php
    }
    public function booking_payment_stripe_api_test_secret_render()
    {
        //$options = get_option('mybooker_settings');?>
    	<input type='text' name='mybooker_settings[booking_payment_stripe_api_test_secret]' class="regular-text" value='<?php echo esc_attr($this->options['booking_payment_stripe_api_test_secret']); ?>'>
    	<?php
    }

    public function booking_currency_type_render()
    {
        $options = get_option('mybooker_settings'); ?>
      	<select name='mybooker_settings[booking_currency_type]'>
        		<option value='usd' <?php selected(esc_attr($options['booking_currency_type']), 'usd'); ?>><?php echo esc_html__('USD', 'mybooker') ?></option>
        		<option value='eur' <?php selected(esc_attr($options['booking_currency_type']), 'eur'); ?>><?php echo esc_html__('EURO', 'mybooker') ?></option>
            <option value='dkk' <?php selected(esc_attr($options['booking_currency_type']), 'dkk'); ?>><?php echo esc_html__('DKK', 'mybooker') ?></option>
      	</select>
        <?php
    }

    public function booking_email_section_callback()
    {
        echo esc_html__('Please add email configuration to setup booking email transaction', 'mybooker');
    }

    public function booking_payment_section_callback()
    {
        echo esc_html__('Please create Stripe API details and add here to use with booking public functions', 'mybooker');
    }

    public function booking_currency_section_callback() {
        echo esc_html__('Select default currency for booking public functions', 'mybooker');
    }

    public function booking_form_section_callback() {

    }

    public function rfi_email_validate($input)
    {
        $validated = sanitize_email($input);
        if ($validated !== $input) {
            $type = 'error';
            $message = esc_html__('Email was invalid', 'require-featured-image');
            add_settings_error(
                'rfi_email',
                esc_attr('settings_updated'),
                $message,
                $type
            );
        }
        return $validated;
    }

    public function render_page()
    {
        echo '
    	<div class="wrap">
    		<h1 class="title">'.esc_html__('Booking Settings', 'mybooker').'</h1>';

        if (isset($_GET[ 'tab' ])) {
            $active_tab === $_GET[ 'tab' ];
        } // end if
        ?>
        <?php if (!empty($_GET['updated'])) : ?>
        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
            <p><strong><?php echo esc_html__('Settings saved.', 'mybooker') ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'mybooker') ?></span></button>
        </div>
        <?php endif; ?>
    			<form action='options.php' method='post'>
    				<?php
                settings_fields('booking_form');
                do_settings_sections('booking_form');

                settings_fields('booking_email');
                do_settings_sections('booking_email');

                settings_fields('booking_payment');
                settings_fields('booking_currency');
                do_settings_sections('booking_payment');
                do_settings_sections('booking_currency');

                submit_button(esc_html__('Save', 'mybooker'));
            ?>
    			</form>
    	</div>
    	<?php
    }
}
?>
