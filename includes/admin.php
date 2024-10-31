<?php
require_once(sprintf("%s/includes/admin-settings.php", MY_BOOKER_PLUGIN_DIR_PATH));
require_once(sprintf("%s/includes/admin-booking-item.php", MY_BOOKER_PLUGIN_DIR_PATH));
require_once(sprintf("%s/includes/admin-booking-items-list.php", MY_BOOKER_PLUGIN_DIR_PATH));
require_once(sprintf("%s/includes/admin-booking-entry.php", MY_BOOKER_PLUGIN_DIR_PATH));
require_once(sprintf("%s/includes/admin-booking-entries-list.php", MY_BOOKER_PLUGIN_DIR_PATH));
/**
* - add menu
* - init script and styles
**/

function mybooker_admin_init() {
    load_plugin_textdomain('mybooker', false, dirname(MY_BOOKER_PLUGIN_BASENAME) . '/languages');
    do_action('mybooker_admin_init');
}
add_action('admin_init', 'mybooker_admin_init');

function mybooker_add_admin_page() {
    $notification_count = Mybooker_Admin_Booking_Entry::count_entries();
    $admin_page = new Mybooker_Admin();

    add_menu_page(
        esc_html__('Bookings', 'mybooker'),
        $notification_count ? sprintf('Bookings <span class="awaiting-mod">%d</span>', $notification_count) : 'Bookings',
        $admin_page->get_capability(),
        $admin_page->get_slug(),
        array('Mybooker_Admin_Booking_Entry','mybooker_entries_list'),
        'dashicons-calendar',
        6
    );

    add_submenu_page(
        '',
        esc_html__('Add Entry', 'mybooker'),
        esc_html__('Add Entry', 'mybooker'),
        'activate_plugins',
        'mybooker_entry_form',
        array('Mybooker_Admin_Booking_Entry', 'mybooker_entry_page'),
        8
    );

    add_submenu_page(
        'mybooker',
        esc_html__('Booking Items', 'mybooker'),
        esc_html__('Booking Items', 'mybooker'),
        'activate_plugins',
        'booking_form_list',
        array('Mybooker_Admin_Booking_Form', 'mybooker_forms_list'),
        7
    );
    add_submenu_page(
        '',
        esc_html__('Add Item', 'mybooker'),
        esc_html__('Add Item', 'mybooker'),
        'activate_plugins',
        'mybooker_form_new',
        array('Mybooker_Admin_Booking_Form', 'mybooker_form'),
        6
    );
    add_submenu_page(
        $admin_page->get_slug(),
        $admin_page->get_page_title(),
        esc_html__('Settings', 'mybooker'),
        'manage_options',
        'mybooker_settings',
        array($admin_page, 'render_page')
    );
}
add_action('admin_menu', 'mybooker_add_admin_page');
add_action('mybooker_admin_init', 'enqueue_admin_assets');

add_action('admin_post_mybooker_admin_form_response', array('Mybooker_Admin_Booking_Form', 'mybooker_admin_form_response_handle'));
add_action('admin_post_nopriv_mybooker_admin_form_response', array('Mybooker_Admin_Booking_Form', 'mybooker_admin_form_response_handle'));

add_action('media_buttons','add_sc_select',11);
function add_sc_select(){
    ?>
     <div id="booking-form" style="display:none;">
          <?php
          $args = array(
            'post_type'      => 'booking_form',
            'order'          => 'DESC',
            'post_status'    => 'any',
            'posts_per_page' => 20
          );

          $q = new WP_Query($args);
          $items = $q->get_posts();
          ?>
          <table class="form-table">
              <tbody>
                  <tr>
                    <th scope="row">
                        <label for="item_id"><?php echo esc_html__('Select items')?><label>
                    </th>
                    <td>
                        <?php
                        for ($i = 0; $i < count($items); $i++) {
                            echo '<input type="checkbox" name="item_id[]" class="fitem" value='.esc_attr($items[$i]->ID).' /><label>'.esc_attr($items[$i]->post_title).'</label><br />';
                        }
                        ?>
                    </td>
                  </tr>
              </tbody>
          </table>
          <a href="#" class="button button-primary button-large" id="fadd">
              <?php echo esc_html__('Add Form', 'mybooker'); ?>
          </a>
     </div>
     <a href="#TB_inline?&width=400&height=250&inlineId=booking-form" title="<?php echo esc_html__('Add Booking Form', 'mybooker'); ?>" class="thickbox button"><?php echo esc_html__('Add Booking Form', 'mybooker'); ?></a>
     <?php
}

add_action('admin_head', 'button_js');
function button_js() {
?>
    <script type="text/javascript">
    jQuery(document).ready(function(){
      var items   = [];
      var string  = '';
      jQuery(".fitem").on('change', function() {
          if(jQuery(this).prop("checked")) {
             if(items.indexOf(jQuery(this).val()) == -1 ) {
               items.push(jQuery(this).val());
             }
          }else{
            if(items.indexOf(jQuery(this).val()) !== -1) {
                items.splice(items.indexOf(jQuery(this).val()), 1);
            }
          }
      });
      jQuery("#fadd").on("click", function() {
          if(items.length > 0) {
              string = "[mybooker_form item='" + items.join(',')+ "']";
              send_to_editor(string);
          }else{
              alert("Please select");
              return false;
          }
      });

    });
    </script>
<?php
}

function enqueue_admin_assets()
{
    wp_enqueue_script('jquery-ui-datepicker');

    wp_enqueue_script('mybooker-timepicker-js', MY_BOOKER_PLUGIN_DIR_URL.'admin/js/jquery.timepicker.js', array('jquery'));
    wp_enqueue_script('mybooker-admin-js', MY_BOOKER_PLUGIN_DIR_URL.'admin/js/mybooker-admin.js', array('jquery'));

    wp_register_style('jquery-ui-theme', MY_BOOKER_PLUGIN_DIR_URL.'admin/js/jquery-ui.css');
    wp_enqueue_style('jquery-ui-theme');

    wp_enqueue_style('mybooker-timepicker-css', MY_BOOKER_PLUGIN_DIR_URL.'admin/js/jquery.timepicker.min.css');
    wp_enqueue_style('mybooker-admin-css', MY_BOOKER_PLUGIN_DIR_URL.'admin/mybooker-admin.css');
}
