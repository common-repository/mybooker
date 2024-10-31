<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Mybooker_Form_List_Table extends WP_List_Table
{
    protected $options;

    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'booking_item',
            'plural' => 'booking_items',
            'ajax'     => false //does this table support ajax?
        ));

        $this->options = get_option('mybooker_settings');
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }
    /**
     * [OPTIONAL] this is example, how to render specific column
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_date($item)
    {
        return date_i18n(get_option('date_format'), strtotime($item->post_date));
    }

    public function column_maxslot($item)
    {
        $meta = get_post_meta($item->ID, '_wpx_form', true);
        return $meta['max-user'];
    }

    public function column_duration($item)
    {
        $meta = get_post_meta($item->ID, '_wpx_form', true);
        return $meta['time-duration'].' '.esc_html__('min', 'mybooker');
    }

    public function column_item_price($item)
    {
        $meta = get_post_meta($item->ID, '_wpx_form', true);
        $cur = array(
                    'usd' => esc_html__('USD', 'mybooker'),
                    'euro' => esc_html__('EURO', 'mybooker'),
                    );

        $setting = $this->options;

        if (! $meta['item-price']) {
            return esc_html__('n/a');
        } else {
            return number_format(esc_attr($meta['item-price']), 0, '.', ',').' '.esc_attr($cur[$setting['booking_currency_type']]);
        }
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_title($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $delete_nonce = wp_create_nonce('delete_fom');
        $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';

        $actions = array(
            'edit'    => sprintf('<a href="?page=mybooker_form_new&id=%s">%s</a>', esc_attr($item->ID), esc_html__('Edit', 'mybooker')),
            'delete'  => sprintf('<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">'.esc_html__('Delete', 'mybooker').'</a>', $page, 'delete', absint($item->ID), esc_attr($delete_nonce))
        );
        return sprintf(
            '%s %s',
            sprintf('<a href="?page=mybooker_form_new&id=%s">%s</a>', esc_attr($item->ID), '<strong>'.esc_attr($item->post_title).'</strong>'),
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />',
            esc_attr($item->ID)
        );
    }
    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'title' => esc_html__('Item Name', 'mybooker'),
            'item_price' => esc_html__('Price', 'mybooker'),
            'maxslot' => esc_html__('Max per slot', 'mybooker'),
            'duration' => esc_html__('Duration', 'mybooker'),
            'date' => esc_html__('Date', 'mybooker'),

        );
        return $columns;
    }
    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'date' => array('time', false),
        );
        return $sortable_columns;
    }
    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-delete' => 'Delete'
        );
        return $actions;
    }
    public function get_items($per_page, $orderby)
    {
        $args = array(
          'post_type'=> 'booking_form',
          'orderby' => $orderby,
          'order'    => 'DESC',
          'posts_per_page' => $per_page,
      );

        //$the_query = new WP_Query( $args );
        $q = new WP_Query($args);
        $posts = $q->posts;
        return $posts;
    }
    /**
     * [REQUIRED] This is the most important method
     * It will get rows from database and prepare them to be showed in table
     */
    public function prepare_items()
    {
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? sanitize_sql_orderby($_REQUEST['orderby']) : 'date';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? sanitize_text_field($_REQUEST['order']) : 'asc';

        $per_page = 10; // constant, how much records will be shown per page
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $total_items = @count($this->items);

        $this->items = $this->get_items($per_page, $orderby);//$posts;//$wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.

            if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'delete_fom')) {
                if(isset($_REQUEST['id'])) {
                   wp_delete_post(intval($_REQUEST['id']), true);
                }else{
                   die('There is something wrong, please try again');
                }
            } else {
                die('There is something wrong, please try again');
            }
        }
        if ('bulk-delete' === $this->current_action()) {
            if(!empty($_REQUEST['item'])) {
                $delete_ids = (array) $_REQUEST['item'];
                foreach ($delete_ids as $id) {
                    wp_delete_post($id, true);
                }
            }
        }
    }
}
