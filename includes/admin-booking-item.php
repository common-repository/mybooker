<?php
class Mybooker_Admin_Booking_Form
{
  /**
   * Path to the admin page templates.
   * @var string
   */
    private static $instance = null;
    private $options;
    /**
     * Gets an instance of our plugin.
     *
     * @return Mybooker_Admin_Booking_Form
     */
    public static function get_instance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    // Our code will go here
    public function __construct()
    {

    }

    public static function mybooker_form()
    {
        if (isset($_GET[ 'id' ]) && intval($_GET[ 'id' ])) {
            $id = intval($_GET[ 'id' ]);
            $item = get_post($id);
            $data = get_post_meta($id, '_wpx_form', true);
        }
        //Generate a custom nonce value.
        $wpx_add_meta_nonce = wp_create_nonce('wpx_add_booking_form_nonce');
        // Build the Form?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
            <?php
                if(isset($_REQUEST['id'])) {
                    echo sprintf(esc_html__('Item: #%d', 'mybooker'), intval($_REQUEST['id']));
                }else{
                    echo esc_html__('Add New Item', 'mybooker');
                }
            ?>
            </h1>
            <?php
              if(isset($_REQUEST['mybooker_plugin_admin_notices'])) {
                  echo wp_kses_post(self::mybooker_plugin_admin_notices());
              }
            ?>
            <hr class="wp-header-end">

            <div class="wpx_add_booking_form" id="poststuff">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="wpx_add_booking_form" >
              <div id="titlediv">
                  <div id="titlewrap">
                    	<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo esc_html__('Enter title here', 'mybooker'); ?></label>
                      <input type="text" name="post_title" required value="<?php echo esc_attr((!empty($item) && $item->post_title != '') ? $item->post_title : ''); ?>" size="30" id="title" spellcheck="true" autocomplete="off">
                  </div><!-- #titlewrap -->
              </div>

              <div id="wpx_form_feedback"></div>
              <h3><?php echo esc_html__('Item details', 'mybooker') ?></h3>
              <p><?php echo esc_html__('Please enter item details', 'mybooker') ?></p>
              <table class="form-table">
                  <tbody>
                      <tr>
                        <th scope="row">
                            <label for="wpx-form[item-price]"> <?php echo esc_html__('Price per slot', 'mybooker'); ?> </label><br>
                        </th>
                        <td>
                            <input class="small-text code"  id="wpx-form-item-price" type="text" name="wpx-form[item-price]" value="<?php echo esc_attr((!empty($data) && $data['item-price'] != '') ? $data['item-price'] : null); ?>" />
                            <span class=""><?php echo esc_html(mybooker_get_currency()); ?></span>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <?php echo esc_html__('Available for', 'mybooker'); ?>
                        </th>
                        <td>
                          <fieldset>
                          <?php
                              $cl = array(  1 => esc_html__('Guest', 'mybooker'),
                                            2 => esc_html__('Member (Login)', 'mybooker')
                                          );
                              $checked = '';

                              foreach ($cl as $k => $v) {
                                  if (!empty($data['allow-user']) && in_array($k, $data['allow-user'])) {
                                      $checked = 'checked';
                                  } else {
                                      $checked = '';
                                  }
                                  echo '<label for="wpx-form-allow-user"><input class="regular-text" id="wpx-form-allow-user" type="checkbox" name="wpx-form[allow-user][]" value="'.esc_attr($k).'" '.esc_attr($checked).' />'.esc_attr($v).'</label><br />';
                              } ?>
                          </fieldset>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <label for="wpx-form[max-user]"> <?php echo esc_html__('Maximum bookable slot(s) per user per day. Leave blank for no maximum settings.', 'mybooker'); ?> </label><br>
                        </th>
                        <td>
                            <input class="small-text code" required id="wpx-form-max-user" type="text" name="wpx-form[max-user]" value="<?php echo esc_attr((!empty($data) && $data['max-user'] != '') ? $data['max-user'] : null); ?>" />
                            <p class="description"><?php echo esc_html__('Sets a maximum for bookable slots per day for any user. This setting overrides any settings for maximum bookable slot per guest or member. Leave blank for no maximum settings.', 'mybooker'); ?></p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <label for="wpx-form[max-allow-guest]"> <?php echo esc_html__('Maximum bookings for guest users', 'mybooker'); ?> </label><br>
                        </th>
                        <td>
                            <input class="small-text code" id="wpx-form-max-allow-guest" type="text" name="wpx-form[max-allow-guest]" value="<?php echo esc_attr((!empty($data) && $data['max-allow-guest'] != '') ? $data['max-allow-guest'] : null); ?>" />
                            <p class="description"><?php echo esc_html__('Sets a maximum for bookable slots per day for a guest user. Set to ‘0’ (zero) for not allowing guests to make a booking. Leave blank for no maximum settings.', 'mybooker'); ?></p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <label for="wpx-form[max-allow]"> <?php echo esc_html__('Maximum bookings for member users', 'mybooker'); ?> </label><br>
                        </th>
                        <td>
                            <input class="small-text code" id="wpx-form-max-allow" type="text" name="wpx-form[max-allow]" value="<?php echo esc_attr((!empty($data) && $data['max-allow'] != '') ? $data['max-allow'] : null ); ?>" />
                            <p class="description"><?php echo esc_html__('Sets a maximum for bookable slots per day for a member user. Set to ‘0’ (zero) for not allowing members to make a booking. Leave blank for no maximum settings.', 'mybooker'); ?></p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <label for="wpx-form[date-range-guest]"> <?php echo esc_html__('Booking range for guests', 'mybooker'); ?> </label><br>
                        </td>
                        <td>
                            <input class="small-text code" id="wpx-form-date-range-guest" type="text" name="wpx-form[date-range-guest]" value="<?php echo esc_attr((!empty($data) && $data['date-range-guest'] != '') ? $data['date-range-guest'] : null); ?>" />
                            <span class=""><?php echo esc_html__('day(s)', 'mybooker'); ?></span>
                            <p class="description"><?php echo esc_html__('Sets a maximum period (days) counted from "today" where it is possible for a guest to make a booking. Leave blank for no maximum settings.', 'mybooker'); ?></p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <label for="wpx-form[date-range]"> <?php echo esc_html__('Booking range for members', 'mybooker'); ?> </label><br>
                        </td>
                        <td>
                            <input class="small-text code" id="wpx-form-date-range" type="text" name="wpx-form[date-range]" value="<?php echo esc_attr((!empty($data) && $data['date-range']) ? $data['date-range'] : null ); ?>" />
                            <span class=""><?php echo esc_html__('day(s)', 'mybooker'); ?></span>
                            <p class="description"><?php echo esc_html__('Sets a maximum period (days) counted from "today" where it is possible for a member to make a booking. Leave blank for no maximum settings.', 'mybooker'); ?></p>
                        </td>
                      </tr>
                  </tbody>
              </table>

              <h3><?php echo esc_html__('Date & Time') ?></h3>
              <p><?php echo esc_html__('Date & Time configuration') ?></p>
              <table class="form-table">
                  <tbody>
                      <tr>
                        <th scope="row">
                            <?php echo esc_html__('Non bookable days.', 'mybooker'); ?>
                        </th>
                        <td>
                            <fieldset>
                            <?php
                                $sl_day = '';
                                $week_days = array(1 => esc_html__('Monday', 'mybooker'),
                                                                           2 => esc_html__('Tuesday', 'mybooker'),
                                                                           3 => esc_html__('Wednesday', 'mybooker'),
                                                                           4 => esc_html__('Thursday', 'mybooker'),
                                                                           5 => esc_html__('Friday', 'mybooker'),
                                                                           6 => esc_html__('Saturday', 'mybooker'),
                                                                           7 => esc_html__('Sunday', 'mybooker'));
                                foreach ($week_days as $v => $d) {
                                    if (!empty($data['na-days']) && in_array($v, $data['na-days'])) {
                                        $sl_day = 'checked';
                                    } else {
                                        $sl_day = '';
                                    }
                                    echo '<label for="wpx-form-na-days"><input class="regular-text" id="wpx-form-na-days" type="checkbox" name="wpx-form[na-days][]" value="'.esc_attr($v).'" '.esc_attr($sl_day).'/>'.esc_attr($d).'</label><br />';
                                } ?>
                                <p class="description">
                                  <?php echo esc_html__('Select any day(s) where bookings cannot be made. This setting repeats itself and stops at the set "Stop date" (see below) if set', 'mybooker'); ?>
                                </p>
                            </fieldset>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <?php echo esc_html__('Stop date for Non bookable days.', 'mybooker'); ?>
                        </td>
                        <td>
                            <fieldset>
                            <input autocomplete="off" class="code datepicker from" type="text" name="wpx-form[stop-at][date]" value="<?php echo esc_attr((!empty($data) && $data['stop-at']['date']) ? $data['stop-at']['date'] : null); ?>" />
                            <input autocomplete="off" class="code timepicker fromt small" size="6" type="text" name="wpx-form[stop-at][time]" value="<?php echo esc_attr((!empty($data) && $data['stop-at']['time']) ? $data['stop-at']['time'] : null); ?>" />
                            <p class="description">
                              <?php echo esc_html__('From the Stop date and going forward Non bookable days becomes bookable again.', 'mybooker'); ?>
                            </p>
                            </fieldset>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <?php echo esc_html__('Closed date(s)', 'mybooker'); ?>
                        </td>
                        <td>
                            <?php
                                  if(!empty($data['close-range'])) :
                                    foreach ($data['close-range'] as $index => $range) :
                                          echo '<fieldset class="cl" data-idx="'.esc_attr($index).'">';
                                          echo '<label for="wpx-form[close-from]">'. esc_html__('From', 'mybooker').'</label>';
                                          echo '<input autocomplete="off" class="code datepicker from" type="text" name="wpx-form[close-range]['.esc_attr($index).'][from]" value="'.esc_attr($range['from'] != '' ? $range['from'] : null).'" />';
                                          echo '<input autocomplete="off" class="code timepicker fromt small" size="6" type="text" name="wpx-form[close-range]['.esc_attr($index).'][ftime]" value="'.esc_attr($range['ftime'] != '' ? $range['ftime'] : null).'" />';
                                          echo '<label for="wpx-form[close-from]">'. esc_html__('To', 'mybooker').'</label>';
                                          echo '<input autocomplete="off" class="code datepicker to" type="text" name="wpx-form[close-range]['.esc_attr($index).'][to]" value="'.esc_attr($range['to'] != '' ? $range['to'] : null).'" />';
                                          echo '<input autocomplete="off" class="code timepicker tot small" size="6" type="text" name="wpx-form[close-range]['.esc_attr($index).'][ttime]" value="'.esc_attr($range['ttime'] != '' ? $range['ttime'] : null).'" />';
                                          if($index > 0) {
                                              echo '<a href="#" class="rmel">-</a>';
                                          }
                                          echo '</fieldset>';
                                    endforeach;
                                  else:
                            ?>
                                  <fieldset class="cl" data-idx="0">
                                  <label for="wpx-form[close-to]"> <?php echo esc_html__('From', 'mybooker'); ?> </label>
                                  <input autocomplete="off" class="code datepicker from" type="text" name="wpx-form[close-range][0][from]" value="" />
                                  <input autocomplete="off" class="code timepicker fromt small" size="6" type="text" name="wpx-form[close-range][0][ftime]" value="" />
                                  <label for="wpx-form[close-to]"> <?php echo esc_html__('To', 'mybooker'); ?> </label>
                                  <input autocomplete="off" class="code datepicker to" type="text" name="wpx-form[close-range][0][to]" value="" />
                                  <input autocomplete="off" class="code timepicker tot small" size="6" type="text" name="wpx-form[close-range][0][ttime]" value="" />
                                  </fieldset>
                            <?php
                                  endif;
                            ?>
                            <a href="#" class="plusel">+</a>
                            <p class="description">
                              <?php echo esc_html__('Select any day(s) and specific time(s)  where bookings cannot be made.', 'mybooker'); ?>
                            </p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <?php echo esc_html__('Open Hours for booking', 'mybooker'); ?>
                        </td>
                        <td>
                            <label for="wpx-form[time-open]"> <?php echo esc_html__('From', 'mybooker'); ?> </label>
                            <input autocomplete="off" size="6" class="code timepicker" required  id="wpx-form-time-open" type="text" name="wpx-form[time-open]" value="<?php echo esc_attr((!empty($data) && $data['time-open'] != '') ? $data['time-open'] : null) ?>" />
                            <label for="wpx-form[time-close]"> <?php echo esc_html__('To', 'mybooker'); ?> </label>
                            <input autocomplete="off" size="6" class="code timepicker" required  id="wpx-form-time-close" type="text" name="wpx-form[time-close]" value="<?php echo esc_attr((!empty($data) && $data['time-close'] != '') ? $data['time-close'] : null)?>" />
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <label for="wpx-form[time-duration]"> <?php echo esc_html__('Booking duration.', 'mybooker'); ?> </label>
                        </td>
                        <td>
                            <input class="small-text code" required id="wpx-form-time-duration" type="text" name="wpx-form[time-duration]" value="<?php echo esc_attr((!empty($data) && $data['time-duration'] != '') ? $data['time-duration'] : null)?>" />
                            <p class="description"><?php echo esc_html__('Minute(s)', 'mybooker'); ?></p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">
                            <?php echo esc_html__('Non bookable time', 'mybooker'); ?>
                        </td>
                        <td>
                          <label for="wpx-form[time-open]"> <?php echo esc_html__('From', 'mybooker'); ?> </label>
                          <input autocomplete="off" size="6" class="timepicker code" id="wpx-form-break-start" type="text" name="wpx-form[break-start]" value="<?php echo esc_attr((!empty($data) && $data['break-start'] != '') ? $data['break-start'] : null)?>" />
                          <label for="wpx-form[time-open]"> <?php echo esc_html__('To', 'mybooker'); ?> </label>
                          <input autocomplete="off" size="6" class="timepicker code" id="wpx-form-break-end" type="text" name="wpx-form[break-end]" value="<?php echo esc_attr((!empty($data) &&  $data['break-end'] != '') ? $data['break-end'] : null)?>" />
                          <p class="description"><?php echo esc_html__('Select a time period during the day where bookings cannot be made. This setting repeats itself and will effect all following day(s) from "today".', 'mybooker'); ?></p>
                        </td>
                      </tr>
                  </tbody>
              </table>
              <input type="hidden" name="action" value="mybooker_admin_form_response">
              <input type="hidden" name="wpx_add_booking_meta_nonce" value="<?php echo esc_attr($wpx_add_meta_nonce) ?>" />
              <input type="hidden" name="form_id" value="<?php echo isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null; ?>" />

              <?php if(isset($_REQUEST['id'])) { ?>
                  <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_html__('Save item', 'mybooker'); ?>"></p>
              <?php }else{ ?>
                  <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_html__('Create booking item', 'mybooker'); ?>"></p>
              <?php  } ?>
            </form>
            </div>
        </div>
    <?php
    }

    /**
     * [the_form_response description]
     * @return [type] [description]
     */
    public static function mybooker_admin_form_response_handle()
    {
        if (isset($_POST['wpx_add_booking_meta_nonce']) && wp_verify_nonce($_POST['wpx_add_booking_meta_nonce'], 'wpx_add_booking_form_nonce')) {
            $args['post_title'] = isset($_POST['post_title'])
                    ? sanitize_text_field($_POST['post_title']) : null;

            $args['post_type'] = 'booking_form';

            if(empty($_POST['wpx-form'])) {
              wp_die();
            }

            $data = mybooker_sanitize($_POST['wpx-form']);

            if (isset($_POST['form_id']) && $_POST['form_id'] != '') {
                //update post
                $args['ID'] = intval($_POST['form_id']);
                $post_id = wp_update_post(wp_parse_args($args));
                update_post_meta($post_id, '_wpx_form', $data);
                update_post_meta($post_id, '_item_price', $data['item-price']);
            } else {
                //save as new post
                $post_id = wp_insert_post(wp_parse_args($args));
                $post    = get_post($post_id);
                add_post_meta($post_id, '_wpx_form', $data);
            }

            if (isset($_POST['ajaxrequest']) && $_POST['ajaxrequest'] === 'true') {
                wp_die();
            }

            // server response
            $admin_notice = "success";
            self::mybooker_custom_redirect($admin_notice, $post_id);
            exit;
        } else {
            wp_die(esc_html__('Invalid nonce specified', 'mybooker'), esc_html__('Error', 'mybooker'), array(
              'response' 	=> 403,
              'back_link' => 'admin.php?page=' . 'mybooker',
          ));
        }
    }

    public function item_valid()
    {
    }
    /**
     * Custom redirect
     * @since    1.0.0
     */
    public static function mybooker_custom_redirect($admin_notice, $response)
    {
        wp_redirect(esc_url_raw(add_query_arg(
            array(
                  'mybooker_plugin_admin_notices' => $admin_notice,
                  'id' => $response,
                  ),
            admin_url('admin.php?page='. 'mybooker_form_new')
            )));
    }

    /**
     * Print amin notices
     */
    public static function mybooker_plugin_admin_notices()
    {
        if (isset($_REQUEST['mybooker_plugin_admin_notices']) && $_REQUEST['mybooker_plugin_admin_notices'] == "success") {
            $html =	'<div class="notice notice-success is-dismissible">';
            $html .= '<p><strong>'.esc_html__('Item saved successful.', 'mybooker').'</strong></p>';

            if(isset($_REQUEST['response']) && $_REQUEST['response'] != '') {
              $html .= '<pre>' . esc_attr($_REQUEST['response'], true) . '</pre>';
            }

            $html .= '</div>';
            return $html;
            // handle other types of form notices
        } else {
            return;
        }
    }

    public static function mybooker_forms_list()
    {
        global $wpdb;
        $table = new Mybooker_Form_List_Table();
        $message = '';
        $table->prepare_items();
        if ('delete' === $table->current_action()) {
            if(isset($_REQUEST['id']) && count($_REQUEST['id']) > 0) {
              $message = '<div class="updated below-h2" id="message"><p>' . sprintf(esc_html__('Items deleted: %d', 'mybooker'), count($_REQUEST['id'])) . '</p></div>';
            }
        } ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php echo esc_html__('Booking Item', 'mybooker')?>
              <a class="add-new-h2" href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=mybooker_form_new')); ?>"><?php echo esc_html__('Add new', 'mybooker')?></a>
            </h2>
            <?php
              if($message != '') {
                echo wp_kses_post($message);
              }
            ?>
            <form id="form-table" method="get">
                <?php $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : ''; ?>
                <input type="hidden" name="page" value="<?php echo esc_attr($page) ?>"/>
                <?php $table->display() ?>
            </form>
        </div>
      <?php
    }
}
