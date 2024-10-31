<?php
if (!class_exists('Mybooker_Entry')) {

    class Mybooker_Entry
    {
        private $id;
        private static $instance = null;

        /**
         * Gets an instance of our plugin.
         *
         * @return Mybooker_Entry
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function __construct() {

        }

        /**
         * get all entry data
         * @param  int $id [entry id]
         * @return array   [entry data]
         */
        public function mybooker_get_entry($id)
        {
            global $wpdb;

            $entry = array();
            if ($id) {
                $table_name = $wpdb->prefix . 'booking_entries'; // do not forget about tables prefix
                $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
            }
            return $entry;
        }


        /**
         * get all entry data by email
         * @param  int email [booking_email]
         * @return array   [entry data]
         */
        public function mybooker_get_entry_by_email($email, $status = '')
        {
            global $wpdb;

            $entry = array();

            if ($email) {
                $table_name = $wpdb->prefix . 'booking_entries'; // do not forget about tables prefix
                $entry = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE booking_email = '%s' AND status != 0 ORDER BY id DESC ", $email), ARRAY_A);
            }
            return $entry;
        }

        /**
         * delete entry
         * @param  int email [booking_email]
         * @return array   [entry data]
         */
        public function mybooker_entry_delete()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'booking_entries';
            $response   =  array('success' => false, 'message' => '');

            //check if delete request
            //@TODO : check function name entry_delete
            if (isset($_POST['action']) && 'mybooker_entry_delete' == $_POST['action']) {

                $ids = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';

                if (!is_array($ids)) {
                    $ids = explode(',', $ids);
                }
                if (!empty($ids)) {
                    $result = [];

                    foreach($ids as $id) {
                        $result[] = $wpdb->update($table_name, array('status' => 0), array('id' => $id));
                    }

                    if(!$result) {
                        $response['message'] = esc_html__('Could not delete booking', 'mybooker');
                    }else{
                        $response['success'] = true;
                        $response['message'] = esc_html__('Your booking have been deleted', 'mybooker');
                    }
                }
            }
            echo wp_json_encode($response);
            die();
        }


        /**
         * delete entry
         * @param  int email [booking_email]
         * @return array   [entry data]
         */
        public function mybooker_entry_update()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'booking_entries';
            $response   =  array('success' => false, 'message' => '');

            //check if delete request
            if (isset($_POST['action']) && 'mybooker_entry_update' == $_POST['action'] ) {
                if(isset($_POST['id']) && isset($_POST['datetime'])) {

                    $date     = implode(',', mybooker_sanitize($_POST['datetime']));
                    $id       = intval($_POST['id']);

                    $result = $wpdb->update($table_name, array('booking_datetime' => $date), array('id' => $id));

                    if($result) {
                        $response['success'] = true;
                        $response['message'] = esc_html__('Your booking have been deleted', 'mybooker');
                    }else{
                        $response['message'] = esc_html__('can not delete slot', 'mybooker');
                    }
                }
            }
            echo wp_json_encode($response);
            die();
        }
        /**
         * return data array according to pattern
         * @param  [id] $id [description]
         * @return [type]     [description]
         */
        public function mybooker_get_entry_data($id)
        {
            $entry = $this->mybooker_get_entry($id);

            $default = array(
                         'id' => 0,
                         'item_id' => '',
                         'item' => '',
                         'time' => '',
                         'booking_name' => '', //ok
                         'booking_email' => '', //ok
                         'booking_datetime' => '',
                         'booking_note' => '',
                         'price' => '',
                         'currency' => mybooker_get_currency(),
                         'slots' => '',
                         'total' => '',
                         'status' => ''
                    );

            $data = shortcode_atts($default, $entry);

            if ($data['id']) {
                if ($data['item_id']) {
                    $item           = Mybooker_Form::get_instance($data['item_id']);
                    $item_data      = $item->mybooker_get_form_data();
                    $data['price']  = esc_attr(intval($item_data['item-price'])).' '.esc_attr($data['currency']);
                    $data['item_name'] = $item->name;
                }

                $booked_slots = $this->mybooker_get_entry_slots($entry);

                if (!empty($booked_slots)) {

                    //turn array to string
                    $slot_str = '';
                    $format   = 'H:i';

                    $i = 0; $i < count($booked_slots);

                    foreach ($booked_slots as $d => $sl) {
                        $slot_str .= $d.PHP_EOL;
                        foreach ($sl as $k => $v) {
                            $slot_str .= date_i18n($format, strtotime($v[0]))." - ".date_i18n($format, strtotime($v[1])).PHP_EOL;
                        }
                        if($i < count($booked_slots) - 1) {
                            $slot_str .= PHP_EOL;
                        }
                        $i++;
                    }

                    $data['booking_datetime'] = $slot_str;
                    $data['slots'] = $this->mybooker_get_slots_count($booked_slots);
                    $data['total'] = esc_attr(intval($data['price'])*intval($data['slots'])).' '.esc_attr($data['currency']);
                }

                //return form data
                $bk_value = array();

                if($entry['form_value'] != '') {
                  $convert_to_array = explode(',', $entry['form_value']);

                  for($i=0; $i < count($convert_to_array ); $i++){
                      $key_value = explode(':', $convert_to_array [$i]);
                      $bk_value[$key_value [0]] = $key_value [1];
                  }
                }

                $data['booking_note'] = isset($bk_value['booking_note']) ? $bk_value['booking_note'] : '';

                $status = array(
                                '1' => 'Pending',
                                '2' => 'Payment Failed',
                                '3' => 'Success',
                                '4' => 'Cancelled',
                                '5' => 'Paid',
                                );
                $data['status'] = $status[$entry['status']];
            }

            return $data;
        }

        /**
         * Get booking entry detail
         * @return array form_value
         */
        public function mybooker_get_entry_slots($entry)
        {
            $booked_slots = array();
            $format = 'H:i';

            $slots = array();

            if($entry['booking_datetime'] != '' && strstr($entry['booking_datetime'], ',')) {
              foreach (explode(',', $entry['booking_datetime']) as $k => $dt) {
                  $slots[] = explode('|', $dt);
              }
            }else{
              $slots[] = explode('|', $entry['booking_datetime']);
            }

            foreach ($slots as $key => $data) {
                $start  = date_i18n("Y-m-d", strtotime($data[0]));
                $end    = date_i18n("Y-m-d", strtotime($data[1]));

                if ($start == $end) {
                    if (!empty($booked_slots[$start]) && !in_array(date_i18n($format, strtotime($data[0])), $booked_slots[$start])) {
                        array_push($booked_slots[$start], [
                                                    date_i18n($format, strtotime($data[0])),
                                                    date_i18n($format, strtotime($data[1]))
                                                 ]);
                    } else {
                        $booked_slots[$start] = array([
                                                    date_i18n($format, strtotime($data[0])),
                                                    date_i18n($format, strtotime($data[1]))
                                                 ]);
                    }
                }
            }
            return $booked_slots;
        }

        /**
         * return slot count
         * @param  [array] $booked_slots [booked slot array]
         * @return [int]                 [count number]
         */
        public function mybooker_get_slots_count($booked_slots)
        {
            $count = call_user_func_array('array_merge_recursive', $booked_slots);
            //return count($count[0]);
            return count($count);
        }

        /**
         * Simple function that validates data and retrieve bool on success
         * and error message(s) on error
         *
         * @param $item
         * @return bool|string
         */
        public static function mybooker_validate_entry($item = array())
        {
            $message = '';
            if ($item['booking_name'] == null) {
                $message = esc_html__('Please enter your name', 'mybooker');
            }
            if ($item['booking_email'] == null) {
                $message = esc_html__('Please enter your email', 'mybooker');
            }elseif(!is_email($item['booking_email'])) {
                $message = esc_html__('E-Mail is in wrong format', 'mybooker');
            }

            if($item['booking_datetime'] == null) {
                $message = esc_html__('Please select a time', 'mybooker');
            }

            if ($message == null) {
                return true;
            }

            return $message;
        }

        /**
         * Save entry with form data
         * @return string [message]
         */
        public static function mybooker_entry_save()
        {
            //global $wp_session;
            global $wpdb;

            $table_name = $wpdb->prefix . 'booking_entries'; // do not forget about tables prefix
            //$response = [];
            $response['success'] = false;


            // this is default $item which will be used for new records
            $default = array(
                         'id' => 0,
                         'item_id' => '',
                         'time' => '',
                         'booking_name' => '',
                         'booking_email' => '',
                         'booking_type' => '',
                         'booking_datetime' => '',
                         'form_value' => '',
                         'status' => 1,
                         'et_status' => 'unread'
                    );

            //@@ TODO : check this
            $item = shortcode_atts($default, $_POST);

            // validate data, and if all ok save item to database
            $item['time']               = current_time('Y-m-d H:i:s');
            //$item['form_value']         = serialize($_POST['wpx_entry']);
            $form_entry = '';

            if(!isset($_POST['wpx_entry'])) {
              array_walk(mybooker_sanitize($_POST['wpx_entry']),
                      function (&$v, $k) {
                          $v = $k.':'.$v;
                      }
                  );
              $form_entry = implode(',', mybooker_sanitize($_POST['wpx_entry']));
            }

            $item['form_value']         = $form_entry;
            // process input datetime
            $datetime = array();

            if(!isset($_POST['booking_datetime']) && $_POST['booking_datetime'] == '') {
              return;
            }

            //get booked data by selected item
            if(!isset($_POST['item_id']) && !intval($_POST['item_id'])) {
              return;
            }
            $exist     = Mybooker_Form::get_instance(intval($_POST['item_id']));
            $exist_data = $exist->mybooker_get_form_data();

            //get booked entries by user_email
            if(!isset($_POST['booking_email']) && $_POST['booking_email'] == '') {
              return;
            }
            $user_entries  = $exist->mybooker_form_entries_by_user(sanitize_email($_POST['booking_email']));

            //get start time of minimun selected day
            $max_selected = $exist->mybooker_get_range_end(); //date_i18n('Y-m-d', strtotime(min(call_user_func_array('array_merge', $datetime))));
            $min_selected = current_time('Y-m-d'); //date_i18n('Y-m-d', strtotime(max(call_user_func_array('array_merge', $datetime))));
            $slots_rg    = $exist->mybooker_booked_slots_with_range($user_entries, $min_selected, $max_selected);

            // exit;
            $max_allow = '';

            if(!is_user_logged_in()) {
                $max_allow = $exist_data['max-allow-guest'];
            }else{
                $max_allow = $exist_data['max-allow'];
            }

            //count slot
            $c = 0;
            foreach($slots_rg as $k => $v) {
                $c += count($v);
            }

            // double booking verification
            $duplicated = false;

            //get booked entries by user_email
            $entries  = $exist->mybooker_get_form_entries();

            if(count($entries) > 0) {
                foreach($datetime as $a => $b) {
                    $ct = 0;
                    foreach ($entries as $entry) {

                        $slots = array();

                        if($entry['booking_datetime'] != '' && strstr($entry['booking_datetime'], ',')) {
                          foreach (explode(',', $entry['booking_datetime']) as $k => $dt) {
                              $slots[] = explode('|', $dt);
                          }
                        }else{
                          $slots[] = explode('|', $entry['booking_datetime']);
                        }

                        foreach ($slots as $k => $data) {
                            if( in_array($b[0], $data) && in_array($b[1], $data)) {
                                $ct++;
                            }
                        }
                    }
                    if(($ct + 1) > $exist_data['max-user']) {
                        $duplicated = true;
                        break;
                    }
                }
            }

            $item['booking_datetime']   = sanitize_text_field($_POST['booking_datetime']);

            // if id is zero insert otherwise update
            $item_valid = self::mybooker_validate_entry($item);

            //return invalid form if reach counter
            if($max_allow != '' && (count($datetime) + $c) > $max_allow) {
                $item_valid = esc_html__('Your last booking reach limitation, please contact admin for more detail', 'mybooker');
            }

            //check if dupplicated
            if ($duplicated === true) {
                $item_valid = esc_html__('Your booking is duplicated, please refresh the page and try again', 'mybooker');
            }

            //check if email exit & user not register
            if(email_exists(sanitize_email($_POST['booking_email'])) && is_email(sanitize_email($_POST['booking_email']))) {

                if(!is_user_logged_in()) {
                    $item_valid = esc_html__('The email was used with a member, please try to login and continue', 'mybooker');
                }else{
                    $current_user = wp_get_current_user();

                    if($current_user->user_email != sanitize_email($_POST['booking_email'])) {
                        $item_valid = esc_html__('The email is not yours, please use the registered email to book', 'mybooker');
                    }
                }
            }

            if ($item_valid === true) {

                //set booking status is success if not require payment
                if(is_user_logged_in()) {
                    $item['status'] = 3;
                }

                if ($item['id'] == 0) {
                    //@TODO : prevent double booking on saving
                    $result = $wpdb->insert($table_name, $item);
                    $item['id'] = $wpdb->insert_id;

                    if ($result) {
                        $response['success'] = true;
                        $response['message'] = esc_html__('Item was successfully saved', 'mybooker');

                    } else {
                        $response['message'] = esc_html__('There was an error while saving item', 'mybooker');
                    }

                } else {

                    $result = $wpdb->update_i18n($table_name, $item, array('id' => $item['id']));

                    if ($result) {
                        $response['success'] = true;
                        $response['message'] = esc_html__('Item was successfully updated', 'mybooker');
                    } else {
                        $response['message'] = esc_html__('There was an error while updating item', 'mybooker');
                    }
                }

                //sending email
                if($response['success'] = true && $item['id'] != '') {
                    try {
                        $mail = new Mybooker_Email($item['id']);
                        $mail->mybooker_send_message();
                    } catch (Exception $e) {
                        //echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
                }

            } else {
                // if $item_valid not true it contains error message(s)
                $response['message'] = $item_valid;
            }
            return $response;
        }

        /**
         * Render form HTML base on form Setting
         * @param  array  $agrs [form settings array]
         * @return [text]       [return form HTML]
         */

        public function mybooker_render_entry_form($item_ids)
        {
            global $locale;
            $settings = get_option('mybooker_settings');

            if(!empty($item_ids)) {

                $return = array('success' => false, 'message' => '');

                if(isset($_POST['wpx_entry_nonce']) && wp_verify_nonce($_POST['wpx_entry_nonce'], 'wpx_add_entry_meta_nonce')) {
                    $return = apply_filters('mybooker_entry_response', $return);
                }

    						$args = array(
    							'post_type'      => 'booking_form',
    							'order'          => 'DESC',
    							'post_status'    => 'any',
    							'post__in'			 => $item_ids,
    							'posts_per_page' => 20
    						);

    						$q = new WP_Query($args);
    						$items = $q->get_posts();

    						$class = 'hidden';
    						if(count($items) > 1) {
    								$class = '';
    						}

                //get first item in array
                $first = 0;

    						$output = '<select name="item_id" id="item-selected">';
    						for ($i = 0; $i < count($items); $i++) {

                    if($i == 0) {
                        $first = $items[$i]->ID;
                    }

    								$data 	= get_post_meta($items[$i]->ID, '_wpx_form', true);
                    $max = '';
                    $range = '';

                    //get maximum allowed for member
                    if(!is_user_logged_in()) {
                        $max = $data['max-allow-guest'];
                        $range = $data['date-range-guest'];
                    }else{
                        $max = $data['max-allow'];
                        $range = $data['date-range'];
                    }

    								$output .= '<option value='.esc_attr($items[$i]->ID).'
                    data-max="'.esc_attr($max).'" data-msg-counter="'.sprintf(esc_html__( 'Maximum active bookings allowed: %s', 'mybooker' ), esc_attr($max) ).'"
                    data-range="'.esc_attr($range).'" data-msg-range="'.sprintf(esc_html__( 'You can only select within %s day(s).', 'mybooker' ), esc_attr($range) ).'"
                    data-price="'.esc_attr($data['item-price']).'" '.esc_attr((isset($_POST['item_id']) && $items[$i]->ID == intval($_POST['item_id'])) ? 'selected' : null).'>'.esc_attr($items[$i]->post_title).'</option>';
    						}
    						$output .='</select>';

    						?>
    						<!-- New Post Form -->
    						<form id="new_post" name="new_post" method="post" action="<?php echo esc_url(get_permalink()); ?>" class="booking-form">
                      <div class="col-left">
                          <strong><?php echo esc_html__('Please select a date', 'mybooker'); ?></strong><a href="#" id="today" class="fl-right" data-day="<?php echo esc_attr(current_time('Y-m-d')); ?>"><?php echo esc_html__('Today', 'mybooker'); ?></a>
        									<div id="datepicker"></div>

        									<p class="item-selected <?php echo esc_attr($class) ?>">
        										<strong><?php echo esc_html__('Please select', 'mybooker'); ?></strong><br />
        										<?php echo wp_kses_post($output); ?>
        									</p>
                      </div>
    									<!--  render schedule table -->
                      <div class="col-right">
                           <?php $obj 	= Mybooker_Form::get_instance($first); ?>
                           <strong><?php echo esc_html__('Please select a time', 'mybooker'); ?></strong><br />
                           <?php $na_text = $settings['booking_na_text']; ?>
    									     <div class="schedule_wrapper"
                                data-short="<?php echo esc_attr(!empty($na_text) && $na_text['short'] != '' ? $na_text['short'] : mull); ?>"
                                data-long="<?php echo esc_attr(!empty($na_text) && $na_text['long'] != '' ? $na_text['long'] : null); ?>">
                              <?php echo wp_kses_post($obj->mybooker_render_form_schedule());?>
                            </div>
                           <input type="hidden" name="booking_datetime" value="<?php echo esc_attr(isset( $_POST['booking_datetime'] ) ? sanitize_text_field($_POST['booking_datetime']) : null)  ?>" data-msg-required="<?php echo esc_html__('Please select a time', 'mybooker'); ?>" />
                      </div>

                      <div class="pfields">
    									<strong><?php echo esc_html__('Enter your details', 'mybooker'); ?></strong>
    									<p><label for="booking_name"><?php echo esc_html__('Your name', 'mybooker'); ?><span class="req">*</span></label><br />
                          <?php
                            $user = [];

                            if(!isset($_GET['success']) || $_GET['success'] !== '1') {

                                if(is_user_logged_in()) {
                                    $current_user = wp_get_current_user();

                                    if($current_user->user_firstname != '') {

                                        $user['name'] = $current_user->user_firstname;
                                    }else{
                                        if(isset( $_POST['booking_name'] ) ) {
                                            $user['name'] = sanitize_text_field($_POST['booking_name']);
                                        }
                                    }
                                    $user['email'] = $current_user->user_email;
                                }else{
                                    if(isset( $_POST['booking_name'] ) ) {
                                        $user['name'] = sanitize_text_field($_POST['booking_name']);
                                    }
                                    if($_POST['booking_email']) {
                                        $user['email'] = sanitize_email($_POST['booking_email']);
                                    }
                                }
                            }

                          ?>
    											<input type="text" name="booking_name" id="booking_name" value="<?php echo esc_attr(@$user['name'] != null ? trim($user['name']):null); ?>" data-msg-required="Please enter something here!"/>
    									</p>
    									<p><label for="booking_email"><?php echo esc_html__('Your e-mail', 'mybooker'); ?><span class="req">*</span></label><br />
    											<input type="email" name="booking_email" id="booking_email" value="<?php echo esc_attr(!empty( $user ) ? $user['email'] : null)  ?>" data-rule-email="true"  data-msg-required="Please enter your email address" data-msg-email="Please enter a valid email address"/>
    									</p>

    									<p><label for="booking_note"><?php echo esc_html__('Message', 'mybooker'); ?></label><br />
    											<textarea id="booking_note"  name="wpx_entry[booking_note]" rows="3" cols="50"><?php echo esc_attr(isset( $_POST['wpx_entry[booking_note]'] ) ? sanitize_textarea_field($_POST['wpx_entry[booking_note]']) : null)  ?></textarea>
    									</p>
                    </div> <!-- personal fields -->

                    <div class="form-bottom">
        								<input type="hidden" name="booking_date"  id="wpx_entry_date" value="<?php echo esc_attr(isset( $_POST['booking_date'] ) ? sanitize_text_field($_POST['booking_date']) : date_i18n(mybooker_opt_dateformat()))  ?>"/>
        								<input type="hidden" name="booking_time" id="wpx_entry_time" class="" value="<?php echo esc_attr(current_time('H:i')); ?>"  />

                        <input type="hidden" name="booking_type" id="wpx_entry_type" class="" value="<?php echo esc_attr(is_user_logged_in()? '2':'1') ?>"  />
                        <input type="hidden" name="wpx_entry[item-price]" id="wpx_item_price" class="" value="<?php echo esc_attr(isset( $_POST['wpx_entry']['item-price'] ) ? sanitize_text_field($_POST['wpx_entry']['item-price']) : null )  ?>"  />
                        <input type="hidden" name="wpx_entry[slots]" id="wpx_entry_slots" class="" value="<?php echo esc_attr(isset( $_POST['wpx_entry']['slots'] ) ? sanitize_text_field($_POST['wpx_entry']['slots']) : null)  ?>"  />
                        <input type="hidden" name="wpx_entry[total]" id="wpx_entry_total" class="" value="<?php echo esc_attr(isset( $_POST['wpx_entry']['total'] ) ? sanitize_text_field($_POST['wpx_entry']['total']) : null)  ?>"  />
                        <input type="hidden" name="booking_payment" id="wpx_entry_payment" class="" value="<?php echo esc_attr(!is_user_logged_in()? '1':'') ?>"  />
                        <input type="hidden" name="stripeToken" id="stripe_token" class="" value=""  />
                        <input type="hidden" name="wpx_entry[currency]" id="wpx_entry_currency" class="" data-currency="<?php echo esc_attr($settings['booking_currency_type']) ?>" value="<?php echo esc_attr(mybooker_get_currency()); ?>"  />
        								<input type="hidden" name="action" value="wpx_entry_saving" />
        								<?php $wpx_add_entry_nonce = wp_create_nonce('wpx_add_entry_meta_nonce'); ?>
        								<input type="hidden" name="wpx_entry_nonce" value="<?php echo esc_attr($wpx_add_entry_nonce); ?>" />

        								<p class="booking_sum"><strong><?php echo esc_html__('Booking details', 'mybooker'); ?></strong><br />
        								<span><?php echo esc_html__('Your booking', 'mybooker'); ?> : <span class="item-name"></span></span><br />
        								<span><?php echo esc_html__('Booking cost', 'mybooker'); ?> : <span class="item-cost"></span></span><br />
        								<span><?php echo esc_html__('Number of bookings', 'mybooker'); ?> : <span class="booked-slot"></span></span><br />
        								<span><?php echo esc_html__('Total price', 'mybooker'); ?> : <span class="total-cost"></span></span><br /></p>
                        <?php
                            //display return message
                            if(isset($_GET['success']) && $_GET['success'] == '1') {
                                $return['message'] = esc_html__('Item was successfully saved', 'mybooker');
                            }
                            if($return['message'] != '') {
                                echo '<p class="fmsg">'.esc_attr($return['message']).'</p>';
                            }

                        ?>
                        <p><input type="submit" class="sbtn" value="<?php echo esc_html__('Book', 'mybooker'); ?>" tabindex="6" id="wpx-submit" name="submitf" /></p>
                        <?php
                        if(!is_user_logged_in()) {
                            //set stripe language
                            $supported = ['da','nl','en','fi','fr','de','it','ja','nb','pl','pt','zh','es','sv'];
                            //default language
                            $lang = 'auto';
                            //return correct language
                            if(in_array(substr( $locale, 0, 2 ), $supported)) {
                                $lang = substr( $locale, 0, 2 );
                            }
                        }
                        ?>
                    </div>
    						</form>
            <?php
                if($return['success'] === true) {
                    unset($_POST);
                    wp_redirect(get_permalink().'/?success=1');
                    exit();

                }
						} // end ids array verify

        }//end form rendering

        /**
         * get member entries
         * @return [html] return html table
         */
        public function mybooker_user_entries() {

            if(is_user_logged_in()) {

                $current_user = wp_get_current_user();
                $email = $current_user->user_email;

                //get booked entries by user_email
                $user_entries  = $this->mybooker_get_entry_by_email($email);
                if(count($user_entries) > 0) {
                $out = '<form id="wpx-entries-table" method="GET">
                        <p class="ent_notice"></p>';
                    if(!empty($user_entries)) {
                      $out = '<table><tr>
                              <th>'.esc_html__('#', 'mybooker').'</th>
                              <th>'.esc_html__('Item', 'mybooker').'</th>
                              <th>'.esc_html__('Total slots', 'mybooker').'</th>';
                      $out.=  '<th>'.esc_html__('Created date', 'mybooker').'</th>
                              <th></th></tr>';
                      foreach ($user_entries as $entry) {
                          if($entry['id'] != '') {
                              $data   						= $this->mybooker_get_entry_data($entry['id']);
                              $delete_entry_nonce = wp_create_nonce('delete_entry');

                              //@TODO : Fix this
                              $out .= '<tr class="slrow" data-id="'.esc_attr($data['id']).'" data-time="'.esc_attr($entry['booking_datetime']).'">';
                              $out .= '<td>'.esc_attr($data['id']).'</td>';
                              $out .= '<td>'.esc_attr($data['item_name']).'</td>';

                              $sl = array();

                              if($entry['booking_datetime'] != '' && strstr($entry['booking_datetime'], ',')) {
                                foreach (explode(',', $entry['booking_datetime']) as $k => $dt) {
                                    $sl[] = explode('|', $dt);
                                }
                              }else{
                                $sl[] = explode('|', $entry['booking_datetime']);
                              }

                              $slot_str = '';
                              foreach ($sl as $d => $v) {
                                  if(date_i18n('Y-m-d', strtotime($v[0])) == date_i18n('Y-m-d', strtotime($v[1]))) {
                                      $slot_str .= '<span class="slotctn">';
                                      //slot date
                                      $slot_str .= esc_attr(date_i18n('Y-m-d', strtotime($v[0]))).'<br />';
                                      //slot time
                                      $slot_str .= esc_attr(date_i18n('H:i', strtotime($v[0])))." - ".esc_attr(date_i18n('H:i', strtotime($v[1])));
                                      $slot_str .= '<a href="#" class="remsl" data-time="'.esc_attr(implode('|', $v)).'">'.esc_html__('Delete', 'mybooker').'</a><br/>';
                                      $slot_str .= '</span>';
                                  }

                              }
                              $out .= '<td>'.$slot_str.'</td>';
                              $out .= '<td>'.esc_attr(date_i18n('Y-m-d H:i', strtotime($data['time']))).'</td>';
                              $out .= '<td><a class="rementr" href="?action=delete&id='.esc_attr($data['id']).'&delnonce='.esc_attr($delete_entry_nonce).'">'.esc_html__('Delete all', 'mybooker').'</a></td>';
                              $out .= '</tr>';
                          }
                      }
                      $out .= '</table>';
                    }
                $out .= '</form>';
              }else{
                $out = '<p class="ent_notice">'.esc_html__('You do not have any booking yet', 'mybooker').'</p>';
              }
            }else{
                $out = esc_html__('Please login to view the page', 'mybooker');
            }

            return $out;
        }
    }
}
add_action('mybooker_entry_response', array('Mybooker_Entry', 'mybooker_entry_save'));

add_action( 'wp_ajax_nopriv_mybooker_entry_delete', array('Mybooker_Entry', 'mybooker_entry_delete') );
add_action( 'wp_ajax_mybooker_entry_delete', array('Mybooker_Entry', 'mybooker_entry_delete') );

add_action( 'wp_ajax_nopriv_mybooker_entry_update', array('Mybooker_Entry', 'mybooker_entry_update') );
add_action( 'wp_ajax_mybooker_entry_update', array('Mybooker_Entry', 'mybooker_entry_update') );
