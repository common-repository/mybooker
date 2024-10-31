<?php

function mybooker_get_form( $id ) {
	 return Mybooker_Form::get_instance( $id );
}

function mybooker_form_shortcode($atts = '')
{
		ob_start();

		$atts = shortcode_atts(array(
          'item' => '',
          'title' => ''
    ), $atts, 'mybooker_form');

		if(!empty($atts['item'])) {


				$data = explode(',', $atts['item']);

				foreach ($data as $id) {
						$obj 	= Mybooker_Form::get_instance($id);
						if(($obj && !$obj->mybooker_is_allow()) || !$obj) {
								//remove not allow item
								$data = array_diff($data, [$id]);
						}
				}


				if(!empty($data)) {
						$form = new Mybooker_Entry();
						$form->mybooker_render_entry_form($data);
				}else{
						echo esc_html__('Your shortcode or the form was not setup correctly, please check again', 'mybooker');
				}

		}else{
				echo esc_html__('Your shortcode or the form was not setup correctly, please check again', 'mybooker');
		}

		return ob_get_clean();
}

function mybooker_member_entries($atts = '') {
		ob_start();
		$atts = shortcode_atts(array(
					'item' => '',
					'title' => ''
		), $atts, 'mybooker_entries');

		$table = new Mybooker_Entry();
		echo wp_kses_post($table->mybooker_user_entries());
		return ob_get_clean();
}

function mybooker_get_currency() {
	 $cur = array(
							'usd' => esc_html__('USD', 'mybooker'),
							'eur' => esc_html__('EURO', 'mybooker'),
							'dkk' => esc_html__('DKK', 'mybooker')
							);
		$options  = get_option('mybooker_settings');
		return $cur[$options['booking_currency_type']];
}

/**
 * get wordpress option date format
 * @TODO : add option to plugin settings to customize
 */
function mybooker_opt_dateformat() {
		return get_option( 'date_format' );
}

/**
 * get wordpress option date format
 * @TODO : add option to plugin settings to customize
 */
function mybooker_opt_timeformat() {
		return get_option( 'time_format' );
}
/**
 * Convert the php date format string to a js date format
 */
function mybooker_get_js_date_format($sFormat) {
	  switch( $sFormat ) {
	      //Predefined WP date formats
				// @TODO : order change with localize
	      case 'F j, Y':
	          return( 'MM dd, yy' );
	          break;
				case 'Y-m-d':
	          return( 'yy-mm-dd' );
	          break;
	      case 'm/d/Y':
	          return( 'mm/dd/yy' );
	          break;
	      case 'd/m/Y':
	          return( 'dd/mm/yy' );
	          break;
				default:
						return( 'yy-mm-dd' );
	   }
}
/**
 * Convert the php date format string to a js date format
 */
function mybooker_get_js_time_format($sFormat) {
	  switch( $sFormat ) {
	      //Predefined WP date formats
				// @TODO : order change with localize
	      case 'g:i a':
	          return( 'h:mm a' );
	          break;
				case 'g:i A':
	          return( 'h:mm A' );
	          break;
	      case 'H:i':
	          return( 'HH:mm' );
	          break;
				default:
						return( 'HH:mm' );
	   }
}
/**
 * return booking type in readable value
 * @param int $type
 * @return text return type in text value
 */
function mybooker_get_booking_type($type) {

		$name = '';

		$type_arr = array(
							'1' => esc_html__('Guest', 'mybooker'),
							'2' => esc_html__('Member', 'mybooker'),
							);

		if($type_arr[$type]) {
				$name = $type_arr[$type];
		}

		return $name;
}
//testing - should be remove
function mybooker_action_wp_mail_failed($wp_error)
{
    //echo print_r($wp_error, true); exit;
}

function mybooker_sanitize( $input ) {

	// Initialize the new array that will hold the sanitize values
	$new_input = array();
	// Loop through the input and sanitize each of the values
	foreach ( $input as $key => $val ) {
		if(!is_array($val)) {
			$new_input[ $key ] = sanitize_text_field( $val );
		}else{
			foreach ( $val as $k => $v ) {
				if(!is_array($v)) {
					$new_input[ $key ][$k] = sanitize_text_field( $v );
				}else{
					foreach ( $v as $x => $y ) {
						$new_input[ $key ][$k][$x] = sanitize_text_field( $y );
					}
				}
			}
		}
	}

	return $new_input;

}
// add the action
add_action('wp_mail_failed', 'mybooker_action_wp_mail_failed', 10, 1);
