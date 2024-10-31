<?php
class Mybooker_Admin_Booking_Entry
{
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

    public static function mybooker_entries_list()
    {
        global $wpdb;
        $table = new Mybooker_Entries_List_Table();
        $table->prepare_items();

        $message = '';
        if ('delete' === $table->current_action()) {
            if(isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
              $message = '<div class="updated below-h2" id="message"><p>' . sprintf(esc_html__('Items deleted: %d', 'mybooker'), count($_REQUEST['id'])) . '</p></div>';
            }
        } ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h1 class="wp-heading-inline">
              <?php echo esc_html__('Booking Entries', 'mybooker')?>
            </h1>
            <hr class="wp-header-end">
            <?php echo wp_kses_post($message); ?>
            <?php $table->views();?>
            <form id="wpx-entries-table" method="GET">
                <input type="hidden" name="page" value="<?php echo esc_attr(isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : null); ?>"/>
                <?php $table->search_box('Search', 'search');?>
                <?php $table->display(); ?>
            </form>
        </div>
        <?php
    }

    public static function count_entries() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'booking_entries';
        $result     = $wpdb->get_var("SELECT count(id) FROM " . $table_name . " WHERE et_status = 'unread' ");

        return $result;
    }

    public static function mybooker_entry_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_entries';

        $form     = new Mybooker_Entry();
        $format   = 'H:i'; //get_option('time_format');
        $options  = get_option('mybooker_settings');
        $message  = '';

        $wpx_add_entry_nonce = wp_create_nonce('wpx_add_booking_entry_nonce');
        ?>

        <div class="wrap">
           <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>

           <?php
           if (isset($_REQUEST['id'])) {
               $entry = $form->mybooker_get_entry(intval($_REQUEST['id']));

               if (!$entry) {
                   $message = esc_html__('Item not found', 'mybooker');
               } else {

          ?>
                 <h2><?php echo esc_html__('Booking Entry', 'mybooker').' #'.esc_attr($entry['id']); ?>
                    <a class="add-new-h2" href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=mybooker')); ?>"><?php echo esc_html__('back to list', 'mybooker')?></a>
                 </h2>

                 <?php
                   //booked detail
                   $output = '';
                   $output .= '<strong>'.esc_html__('Name', 'mybooker').' : '.esc_attr($entry['booking_name']).'</strong>';
                   $output .= '<p>'.esc_html__('E-mail', 'mybooker').' : '.esc_attr($entry['booking_email']).'</p>';

                   //booked item
                   $item = Mybooker_Form::get_instance($entry['item_id']);
                   $item_data = $item->mybooker_get_form_data();

                   $output .= '<p>'.esc_html__('Item Name', 'mybooker').' : '.esc_attr($item->name).'</p>';
                   $output .= '<p>'.esc_html__('Booking Type', 'mybooker').' : '.mybooker_get_booking_type($entry['booking_type']).'</p>';

                   $slots = array();

                   if($entry['booking_datetime'] != '' && strstr($entry['booking_datetime'], ',')) {
                     foreach (explode(',', $entry['booking_datetime']) as $k => $dt) {
                         $slots[] = explode('|', $dt);
                     }
                   }else{
                     $slots[] = explode('|', $entry['booking_datetime']);
                   }

                   $output .= esc_html__('Booked Slots', 'mybooker').'<br />';

                   foreach ($slots as $key => $data) {
                       $start  = date_i18n("Y-m-d", strtotime($data[0]));
                       $end    = date_i18n("Y-m-d", strtotime($data[1]));

                       if ($start == $end) {
                           if (!empty($booked_slots[$start]) && !in_array(date_i18n("H:i", strtotime($data[0])), $booked_slots[$start])) {
                               $output .= esc_attr($start.' ['.date_i18n($format, strtotime($data[0])) .' - '.date_i18n($format, strtotime($data[1])).']').'<br />';
                           } else {
                               $output .= esc_attr($start.' ['.date_i18n($format, strtotime($data[0])).' - '.date_i18n($format, strtotime($data[1])).']').'<br />';
                           }
                       }
                   }

                   $output .= '<p>'.esc_html__('Total slots', 'mybooker').' : '.esc_attr(count($slots)).'</p>';

                   $output .= '<p>'.esc_html__('Item Price', 'mybooker').' : '.esc_attr($item_data['item-price']).' '.esc_attr(mybooker_get_currency()).'</p>';
                   $output .= '<p>'.esc_html__('Total Cost', 'mybooker').' : '.esc_attr(count($slots)*$item_data['item-price']).' '.esc_attr(mybooker_get_currency()).'</p>';

                   if($entry['booking_email'] != '' && !email_exists($entry['booking_email'])) {
                       $output .= '<p>'.esc_html__('Payment Method', 'mybooker').' : '.esc_attr($entry['pm_method']).'</p>';
                       $output .= '<p>'.esc_html__('Payment Transaction', 'mybooker').' : '.esc_attr($entry['pm_transaction']).'</p>';
                   }

                   $status = array(
                                   '1' => 'Pending',
                                   '2' => 'Payment Failed',
                                   '3' => 'Success',
                                   '4' => 'Cancelled',
                                   '5' => 'Paid',
                                   );
                   $output .= '<p>'.esc_html__('Status', 'mybooker').' : '.esc_attr($status[$entry['status']]).'</p>';

                   /* Booking form data */
                   if($entry['form_value'] != '') {
                     $convert_to_array = explode(',', $entry['form_value']);

                     for($i=0; $i < count($convert_to_array ); $i++){
                         $key_value = explode(':', $convert_to_array [$i]);
                         $bk_value[$key_value [0]] = $key_value[1];
                     }
                   }
               }

               if (!empty($message)) {
                   echo '<div id="message" class="updated"><p>'.esc_attr($message).'</p></div>';
               } ?>
               <form id="form" method="POST">

                   <input type="hidden" name="nonce" value="<?php echo esc_attr($wpx_add_entry_nonce); ?>"/>
                   <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                   <input type="hidden" name="id" value="<?php echo esc_attr($entry['id']); ?>"/>


                   <div class="" id="poststuff">
                       <div id="post-body">
                           <div id="post-body-content">
                               <?php /* And here we call our custom meta box */
                                   echo wp_kses_post($output);
                                ?>
                           </div>
                       </div>
                   </div>
               </form>
           <?php
               if($entry['et_status'] == 'unread') {
                   $result = $wpdb->update($table_name, array('et_status' => 'read'), array('id' => intval($entry['id'])));
               }
           } // end checking $_REQUEST['id']
            ?>
        </div>
    <?php
    }

    /**
   * This function renders our custom meta box
   * $item is row
   *
   * @param $item
   */
    public function mybooker_entry_form_meta_box_handler($item)
    {
        ?>
        <table class="form-table">
            <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php echo esc_html__('Name', 'mybooker')?></label>
                </th>
                <td>
                    <input id="name" name="booking_name" type="text" style="width: 95%" value="<?php echo esc_attr($item['booking_name'])?>"
                           size="50" placeholder="<?php echo esc_html__('Your name', 'mybooker')?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php echo esc_html__('E-Mail', 'mybooker')?></label>
                </th>
                <td>
                    <input id="email" name="booking_email" type="email" style="width: 95%" value="<?php echo esc_attr($item['booking_email'])?>"
                           size="50" placeholder="<?php echo esc_html__('Your E-Mail', 'mybooker')?>" required>
                </td>
            </tr>
            </tbody>
        </table>
    <?php
  }
}
add_action('mybooker_entry_form', array( 'Mybooker_Admin_Booking_Entry', 'mybooker_entry_page' ));
