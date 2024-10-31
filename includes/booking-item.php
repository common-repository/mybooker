<?php
if (!class_exists('Mybooker_Form')) {

    class Mybooker_Form
    {
        const post_type = 'booking_form';

        public $id;
        public $name;

        protected $date_zone;
        protected $date_format;
        protected $time_format;
        protected $start_of_week;

        protected $duration;
        protected $open_time;
        protected $close_time;

        protected $date_range;
        protected $close_range;

        /////////////////////
        // protected $date_from;
        // protected $date_to;
        ////////////////////

        protected $na_days;

        protected $break_start;
        protected $break_end;

        protected $sc_atts = array();
        protected $form_data = array();
        protected static $instance = null;

        /**
         * max booking per slots
         * avaliable for user
         * weekday
         * close + open dates
         * date range
         * close + open time
         * break time
         */

        /**
        * Gets an instance of form.
        * @return Mybooker_Form
        */
        public static function get_instance($post)
        {
            $post = get_post($post);

            if (! $post or self::post_type != get_post_type($post)) {
                return false;
            }
            return self::$instance = new self($post);
        }

        /**
         * construct class
         * @param int $post [post id]
         */
        public function __construct($post = null)
        {
            $post = get_post($post);

            if ($post and self::post_type == get_post_type($post)) {
                $this->id   = $post->ID;
                $this->name = $post->post_title;
                $this->form_data = $this->mybooker_get_form_meta();

                $data = $this->form_data;

                $this->duration = $data['time-duration'];
                $this->open_time = $data['time-open'];
                $this->close_time = $data['time-close'];


                $this->date_range = $data['date-range'];

                if(!is_user_logged_in()) {
                    $this->date_range = $data['date-range-guest'];
                }

                $this->close_range = $data['close-range'];

                if($data['break-start'] != '') {
                    $bs = new DateTime($data['break-start']);
                    $this->break_start = $bs->format('H:i');
                }

                if($data['break-end'] != '') {
                    $be = new DateTime($data['break-end']);
                    $this->break_end = $be->format('H:i');
                }
                if(isset($data['na-days']) &&  $data['na-days'] != '') {
                    $this->na_days = $data['na-days'];
                }
                $this->stop_at = $data['stop-at'];
            }

            $this->date_format = get_option('date_format');
            $this->time_format = get_option('time_format');
            $this->time_zone = get_option('gmt_offset'); //gmt_offset, timezone_string
            $this->start_of_week = get_option('start_of_week');
        }

        /**
         * get id
         * @return int [form id]
         */
        public function id() {
            return $this->id;
        }

        /**
         * get duration
         */
        public function mybooker_get_duration() {

            $data = $this->form_data;

            if(is_null($data['time-duration'])) {
                return false;
            }
            return $data['time-duration'];
        }

        /**
         * get form name
         * @return string [form name]
         */

        public function mybooker_get_form_name() {
            return $this->name;
        }

        /**
         * get form data
         * @return array [form data]
         */
        public function mybooker_get_form_data() {
            return $this->form_data;
        }

        /**
         * Register new post type for form
         * @return [type] [description]
         */
        public static function mybooker_register_post_type()
        {
            $labels = array(
                  'name'                  => _x('Booking Items', 'Post Type General Name', 'mybooker'),
                  'singular_name'         => _x('Booking Item', 'Post Type Singular Name', 'mybooker'),
                  //'menu_name'             => esc_html__('Booking Item', 'mybooker'),
                  'name_admin_bar'        => esc_html__('Booking Item', 'mybooker'),
                  'archives'              => esc_html__('Items Archives', 'mybooker'),
                  'attributes'            => esc_html__('Form Attributes', 'mybooker'),
                  'parent_item_colon'     => esc_html__('Parent Form:', 'mybooker'),
                  'all_items'             => esc_html__('All Items', 'mybooker'),
                  'add_new_item'          => esc_html__('Add New Item', 'mybooker'),
                  'add_new'               => esc_html__('Add Item', 'mybooker'),
                  'new_item'              => esc_html__('New Item', 'mybooker'),
                  'edit_item'             => esc_html__('Edit Item', 'mybooker'),
                  'update_item'           => esc_html__('Update Item', 'mybooker'),
                  'view_item'             => esc_html__('View Item', 'mybooker'),
                  'view_items'            => esc_html__('View Items', 'mybooker'),
                  'search_items'          => esc_html__('Search Items', 'mybooker'),
                  'not_found'             => esc_html__('Not found', 'mybooker'),
                  'not_found_in_trash'    => esc_html__('Not found in Trash', 'mybooker'),
                  'featured_image'        => esc_html__('Featured Image', 'mybooker'),
                  'set_featured_image'    => esc_html__('Set featured image', 'mybooker'),
                  'remove_featured_image' => esc_html__('Remove featured image', 'mybooker'),
                  'use_featured_image'    => esc_html__('Use as featured image', 'mybooker'),
                  'insert_into_item'      => esc_html__('Insert into item', 'mybooker'),
                  'uploaded_to_this_item' => esc_html__('Uploaded to this item', 'mybooker'),
                  'items_list'            => esc_html__('Items list', 'mybooker'),
                  'items_list_navigation' => esc_html__('Items list navigation', 'mybooker'),
                  'filter_items_list'     => esc_html__('Filter Items list', 'mybooker'),
              );
            $args = array(
                  'label'                 => esc_html__('Booking Item', 'mybooker'),
                  'labels'                => $labels,
                  'supports'              => array( 'title', 'editor', 'custom-fields' ),
                  'taxonomies'            => array( 'category', 'post_tag' ),
                  'hierarchical'          => true,
                  'public'                => true,
                  'show_ui'               => false,
                  'show_in_menu'          => false,
                  'menu_position'         => 5,
                  'show_in_admin_bar'     => true,
                  'show_in_nav_menus'     => true,
                  'can_export'            => true,
                  'has_archive'           => true,
                  'exclude_from_search'   => false,
                  'publicly_queryable'    => true,
                  'query_var'             => 'booking_form',
                  'capability_type'       => 'post',
              );
            register_post_type(self::post_type, $args);
        }

        /**
         * Get form setting from form post meta
         * @param  array  $agrs [description]
         * @return [type]       [description]
         */
        public function mybooker_get_form_meta()
        {
            $id = $this->id;

            if (is_null($id)) {
                return false;
            }
            return get_post_meta($id, '_wpx_form', true);
        }

        /**
         * get items
         * @param  array  $incl [description]
         * @return [type]       [description]
         */
        public function get_items($incl = array()) {

            $args = array(
              'post_type'      => self::post_type,
              'order'          => 'DESC',
              'post_status'    => 'any',
              'posts_per_page' => 3
            );

            if(!empty($incl)) {
                $args['post__in'] = $incl;
            }
            $items = new WP_Query($args);
            return $items->get_posts();
        }

        /**
         * get entries with form id
         * @param  int $id [form id]
         * @return array   [entries data array]
         */
        public function mybooker_get_form_entries($status = null)
        {
            global $wpdb;
            $id = $this->id;
            $table_name = $wpdb->prefix . 'booking_entries';
            $entries 		= array();
            if ($id) {

                //default status paid
                $where = 'status = 3';

                if($status != null) {
                    $where = 'status = '.esc_sql($status);
                }

                //$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE item_id = %d AND %d", $id, $where);
                $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE item_id = %d AND $where", $id);
                $entries = $wpdb->get_results($sql, ARRAY_A);
            }

            return $entries;
        }

        /**
         * get entries with form id
         * @param  int $id [form id]
         * @return array   [entries data array]
         */
        public function mybooker_form_entries_by_user($email = null)
        {
            global $wpdb;
            $id         = $this->id;
            $table_name = $wpdb->prefix . 'booking_entries';
            $entries 		= array();

            if ($id) {

                $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE item_id = %d AND status = '3' AND booking_email = '$email'", $id);
                $entries = $wpdb->get_results($sql, ARRAY_A);

            }

            return $entries;
        }

        /**
         * get booked slots from entries data
         * @param  array $entries [entries array data]
         * @return array          [booked slots array]
         */
        public function mybooker_booked_slots($entries) {

            $booked_slots = array();
            $format = 'H:i';//get_option('time_format');

            foreach ($entries as $entry) {

                $slots = array();

                if($entry['booking_datetime'] != '' && strstr($entry['booking_datetime'], ',')) {
                  foreach (explode(',', $entry['booking_datetime']) as $k => $dt) {
                      $slots[] = explode('|', $dt);
                  }
                }else{
                  $slots[] = explode('|', $entry['booking_datetime']);
                }

                foreach ($slots as $key => $data) {
                    //slot's start date
                    $start  = date("Y-m-d", strtotime($data[0]));
                    //slot's end date
                    $end    = date("Y-m-d", strtotime($data[1]));

                    if($start == $end) {

                        if(!empty($booked_slots[$start]) && !in_array(date($format, strtotime($data[0])), $booked_slots[$start])) {
                          array_push($booked_slots[$start], [
                                                      date($format, strtotime($data[0])),
                                                      date($format, strtotime($data[1])),
                                                      ['type' => mybooker_get_booking_type($entry['booking_type']), 'name' => $entry['booking_name']]
                                                   ]);
                        }else{
                          $booked_slots[$start] = array([
                                                      date($format, strtotime($data[0])),
                                                      date($format, strtotime($data[1])),
                                                      ['type' => mybooker_get_booking_type($entry['booking_type']), 'name' => $entry['booking_name']]
                                                   ]);
                        }

                    }
                }
            }

            return $booked_slots;
        }

        /**
         * get booked slots from entries data
         * @param  array $entries [entries array data]
         * @return array          [booked slots array]
         */
        public function mybooker_booked_slots_with_range($entries, $from, $to) {

            $booked_slots = array();
            $format = 'H:i';//get_option('time_format');

            foreach ($entries as $entry) {

                $slots = array();

                if($entry['booking_datetime'] != '' && strstr($entry['booking_datetime'], ',')) {
                  foreach (explode(',', $entry['booking_datetime']) as $k => $dt) {
                      $slots[] = explode('|', $dt);
                  }
                }else{
                  $slots[] = explode('|', $entry['booking_datetime']);
                }

                foreach ($slots as $key => $data) {
                    //slot's start date
                    $start  = date("Y-m-d", strtotime($data[0]));
                    //slot's end date
                    $end    = date("Y-m-d", strtotime($data[1]));

                    if(($to != '' && $start == $end && $start >= $from && $end <= $to) || ($to == '' && $start == $end && $start >= $from)) {

                        if(!empty($booked_slots[$start]) && !in_array(date($format, strtotime($data[0])), $booked_slots[$start])) {
                          array_push($booked_slots[$start], [
                                                      date($format, strtotime($data[0])),
                                                      date($format, strtotime($data[1])),
                                                      ['type' => mybooker_get_booking_type($entry['booking_type']), 'name' => $entry['booking_name']]
                                                   ]);
                        }else{
                          $booked_slots[$start] = array([
                                                      date($format, strtotime($data[0])),
                                                      date($format, strtotime($data[1])),
                                                      ['type' => mybooker_get_booking_type($entry['booking_type']), 'name' => $entry['booking_name']]
                                                   ]);
                        }

                    }
                }
            }

            return $booked_slots;
        }

        /**
         * get dates array from date range
         * @param  string $start  [description]
         * @param  string $end    [description]
         * @param  string $format [description]
         * @return array        [description]
         */
        public function mybooker_dates_from_range($start, $end, $format = 'Y-m-d')
        {
            $array = array();
            $interval = new DateInterval('P1D');

            $realEnd = new DateTime($end);
            $realEnd->add($interval);
            $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
            foreach ($period as $date) {
                $array[] = $date->format($format);
            }
            return $array;
        }

        /**
         * get time slots from time range
         * @param  [type] $duration [description]
         * @param  [type] $start    [description]
         * @param  [type] $end      [description]
         * @param  [type] $exclude  [description]
         * @param  string $format   [description]
         * @return [type]           [description]
         */
        public function mybooker_get_schedule_slots()
        {
            $time = [];
            $format = 'H:i';//get_option('time_format');
            $duration = $this->duration;

            $open      = new DateTime($this->open_time);
            $close        = new DateTime($this->close_time);

            $start_time = $open->format($format);
            $end_time   = $close->format($format);

            $i = 0;

            while (strtotime($start_time) <= strtotime($end_time)) {

                $eof        = date($format, strtotime('+'.$duration.' minutes', strtotime($start_time)));
                if(strtotime($eof) == strtotime('00:00')) {
                    break;
                }
                /* exclude bread time */
                if($this->break_end && $this->break_start) {
                    if(strtotime($start_time) < strtotime($this->break_end) && strtotime($eof) > strtotime($this->break_end)) {
                         $start_time = $this->break_end;
                    }
                }

                $start      = $start_time;
                $end        = date($format, strtotime('+'.$duration.' minutes', strtotime($start_time)));
                $start_time = date($format, strtotime('+'.$duration.' minutes', strtotime($start_time)));

                /* exclude bread time */
                if($this->break_end && $this->break_start) {
                    if(strtotime($eof) > strtotime($this->break_start) && strtotime($eof) < strtotime($this->break_end)) {
                         $end = $this->break_end;
                    }
                }

                if (strtotime($start_time) <= strtotime($end_time)) {
                    $time[$i]['start'] = $start;
                    $time[$i]['end'] = $end;
                }

                $i++;
            }
            return $time;
        }

        public function mybooker_get_range_end() {

            if($this->date_range != '') {

                $rangle     = 'P'.$this->date_range.'D'; // P1D means a period of 1 day
                $start_date = current_time('Y-m-d');
                $date       = new DateTime($start_date);

                $date->add(new DateInterval($rangle));
                return $date->format('Y-m-d');
            }else {
                return false;
            }

        }

        /**
         * check if user is allow
         * @return boolean
         */
        public function mybooker_is_allow() {

            $data = $this->form_data;

            if(!empty($data) && is_array($data['allow-user'])) {
                if(is_user_logged_in()
                  && in_array(2, $data['allow-user'])) {
                    return true;
                }elseif(!is_user_logged_in()
                  && in_array(1, $data['allow-user'])) {
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        /**
         * return schedule data
         * @return json
         */
        public function mybooker_schedule()
        {
            ob_clean();

            $output = '';

            if(isset($_POST['item_id']) && intval($_POST['item_id'])) {
              $id = intval($_POST['item_id']);
              $wpx = Mybooker_Form::get_instance( $id );

              if(isset($_POST['selected']) && $_POST['selected'] != '') {
                $selected = sanitize_text_field($_POST['selected']);
                $output = $wpx->mybooker_get_schedule($selected);
              }
            }
            echo wp_json_encode($output);
            die();
        }

        public function mybooker_get_schedule($selected)
        {
            $data = $this->mybooker_get_form_meta();

            if($this->id) {

            }

            $entries = $this->mybooker_get_form_entries();
            $time_arr = $this->mybooker_booked_slots($entries);

            $now    = new DateTime();
            $today  = $now->format('Y-m-d');

            $d        = date_i18n('Y-m-d', $selected);

            $start_end  = get_weekstartend($d);
            $days       = $this->mybooker_dates_from_range(date('Y-m-d', $start_end['start']), date('Y-m-d', $start_end['end']), 'Y-m-d');

            //return slot
            $slots    = $this->mybooker_get_schedule_slots();
            $output   = [];

            foreach ($days as $day) {
                $output['header'][] = $day;

                foreach ($slots as $key => $value) {

                    //get slot time range to time value
                    $slot_start = strtotime($value['start']);
                    $slot_end = strtotime($value['end']);

                    //assign rturn data
                    $arr = []; //array('data' => [], 'type' => '', 'name');

                    if(!empty($time_arr[$day])) {
                        $c = 0;

                        foreach ($time_arr[$day] as $idx => $val) {
                          //get booked time string
                          $start_time = strtotime($val[0]);
                          $end_time   = strtotime($val[1]);

                            //return slot data to array
                            if(!empty($val) && $slot_start <= $start_time && $slot_end >= $end_time ) { // && $c = $data['max-user']) {
                                //increase slot counter
                                $c ++;

                                //check maximum allowed
                                if($c >= $data['max-user']) {

                                    $arr['time'] = $time_arr[$day][$idx];
                                    $arr['bookable'] = 1;
                                    //break;
                                }else{
                                    $arr['time'] = [];
                                    $arr['bookable'] = 1;
                                }
                                //return booked data
                                if(!empty($val[2])) {
                                    $arr['data'][] = $val[2];
                                }
                            }
                        }
                    }else{
                        //return empty array if there is no booked value
                        $arr['time'] = [];
                    }

                    //check if date & time out of range "Booking range for members"
                    if($this->date_range != '') {
                        if($this->mybooker_get_range_end() && $day > $this->mybooker_get_range_end() || $this->mybooker_isInRange($day)) {
                            $arr['time'] = array($value['start'] , $value['end'] );
                            $arr['bookable'] = 2;
                        }
                    }

                    if(!empty($this->close_range)) {

                        foreach($this->close_range as $index => $range) {

                            $fr = new DateTime($range['from']);
                            $tr = new DateTime($range['to']);
                            //check if from time or to time is empty
                            $tf = $range['ftime'];
                            if($range['ftime'] == null) {
                                $tf = '00:00';
                            }
                            $tt = $range['ttime'];
                            if($range['ttime'] == '') {
                                $tt = '23:59';
                            }

                            if($fr->format('Y-m-d') != $tr->format('Y-m-d')) {

                                if($day > $fr->format('Y-m-d') && $day < $tr->format('Y-m-d')){

                                  $arr['time'] = array($value['start'] , $value['end'] );
                                  $arr['bookable'] = 2;
                                }

                                if($day == $fr->format('Y-m-d') && (strtotime($value['start']) >= strtotime($tf)
                                || strtotime($value['end']) > strtotime($tf) && strtotime($value['start']) < strtotime($tf))) {
                                    $arr['time'] = array($value['start'] , $value['end'] );
                                    $arr['bookable'] = 2;
                                }

                                if($day == $tr->format('Y-m-d') && (strtotime($value['end']) <= strtotime($tt)
                                || strtotime($value['start']) < strtotime($tt) && strtotime($value['end']) > strtotime($tt))) {
                                    $arr['time'] = array($value['start'] , $value['end'] );
                                    $arr['bookable'] = 2;
                                }
                            }else{
                                if($day == $tr->format('Y-m-d')
                                && strtotime($value['start']) >= strtotime($tf)
                                && strtotime($value['end']) <= strtotime($tt)
                                // from time not rounded and greater than start value
                                || $day == $tr->format('Y-m-d')
                                && strtotime($value['start']) < strtotime($tf)
                                && strtotime($value['end']) > strtotime($tf)
                                || $day == $tr->format('Y-m-d')
                                // to time not rounded and greater than start value
                                && (strtotime($value['end']) > strtotime($tt))
                                && strtotime($value['start']) < strtotime($tt)) {
                                    $arr['time'] = array($value['start'] , $value['end'] );
                                    $arr['bookable'] = 2;
                                }
                            }
                        }
                    }

                    //check if day in disabled week day
                    if(!empty($this->na_days) && in_array(date("N", strtotime($day)), $this->na_days)) {
                            $arr['time'] = array($value['start'] , $value['end'] );
                            $arr['bookable'] = 2;
                    }

                    //check slots outside break time
                    if(strtotime($this->break_start) >= strtotime($value['start'])
                    && strtotime($this->break_end) <= strtotime($value['end']))
                    {
                       $arr['time'] = array($value['start'] , $value['end'] );
                       $arr['bookable'] = 2;
                    }

                    if($day < $today) {
                        $arr['time'] = array($value['start'] , $value['end'] );
                        $arr['bookable'] = 2;
                    }

                    //check if current time is out of book
                    $cur_time = strtotime(date('H:i', current_time( 'timestamp' )));

                    //check if day is today
                    if($day == $today &&  $cur_time > strtotime($value['start'])
                    || $day == $today && $cur_time < strtotime($value['end']) && $cur_time > strtotime($value['start'])) {
                        $arr['time'] = array($value['start'] , $value['end'] );
                        $arr['bookable'] = 2;

                        //same day but not booked
                    }elseif ($day == $today &&  $cur_time < strtotime($value['start'])
                    || $day == $today && $cur_time > strtotime($value['end']) && $cur_time < strtotime($value['start'])) {
                        //@TODO : check booked data
                        if(empty($arr['data'])) {
                            $arr['time'] = [];
                            $arr['bookable'] = 1;
                        }
                    }

                    if(!empty($arr['time'])) {
                        $output['value'][$value['start'].'-'.$value['end']][$day] = array('time' => $arr['time'][0].'-'.$arr['time'][1], 'bookable' => @$arr['bookable'], 'data' => @$arr['data'] );
                    }else{
                        $output['value'][$value['start'].'-'.$value['end']][$day] = array('time' => '', 'bookable' => @$arr['bookable'], 'data' => @$arr['data']);
                    }
                }

            }

            return $output;
        }

        /**
         * render slot table on form initial
         * @return html [description]
         */
         public function mybooker_render_form_schedule()
         {
             //get selected datetime
             $selected = [];
             $settings = get_option('mybooker_settings');
             $na_text  = $settings['booking_na_text'];

             if(isset($_POST['booking_datetime']) && $_POST['booking_datetime'] != '') {
                 $arr = explode(',', sanitize_text_field($_POST['booking_datetime']));

                 foreach($arr as $k => $v) {
                     $d  = explode('|', $v);
                     $st = new DateTime($d[0]);
                     $et = new DateTime($d[1]);
                     if($st->format('Y-m-d') == $et->format('Y-m-d')) {
                         $selected[$st->format('Y-m-d')][] = array($st->format('H:i'), $et->format('H:i'));
                     }
                 }
             }

             //render schedule table
             $now    = new DateTime();
             $data = $this->mybooker_get_schedule($now->format('Y-m-d'));

             if(!empty($data)) {
                 $output  = '<table id="gschedule">';
                 //display header
                 $output .= '<tr><th></th>';

                 foreach($data['header'] as $i => $header) {
                   if(strtotime($header) == strtotime($now->format('Y-m-d'))) {
                       $cl = 'selected';
                   }else{
                       $cl = '';
                   }
                   $output .= '<th class="slot-time '.esc_attr($cl).'" data-date="'.esc_attr($header).'">';
                   $output .= '<span>'.esc_attr(date_i18n("D", strtotime($header))).'</span><br />';
                   $output .= '<span>'.esc_attr(date_i18n("d", strtotime($header))).'</span>';
                   $output .= '</th>';
                 }
                 $output .= '</tr>';

                 //display content
                 $index = 0;

                 foreach($data['value'] as $id => $row) {
                   $sl    = explode('-', $id);

                   $output .= '<tr><td>';
                   if($index == 0) {
                       $output .= '<span class="sstart">'.esc_attr(date_i18n(mybooker_opt_timeformat(), strtotime($sl[0]))).'</span>';
                   }
                   $output .= '<span class="send">'.esc_attr(date_i18n(mybooker_opt_timeformat(),strtotime($sl[1]))).'</span>';
                   $output .= '</td>';
                   foreach ($row as $key => $value) {

                       //check if slot is disabled
                       if(!empty($value['time'])) {
                           //add new class if booked
                           $class= '';

                           if(isset($value['bookable']) && $value['bookable'] == 2 && empty($value['data'])) {
                               $class = 'disabled';
                           }else{
                               $class = 'disabled booked';
                           }
                           $output .= '<td data-time="' .esc_attr($id). '" data-day="' .esc_attr($key). '" class="'.esc_attr($class).'">';
                       }else{

                           $class = '';

                           if(array_key_exists($key, $selected)) {
                               foreach ($selected[$key] as $arr) {
                                   if($sl[0] == $arr[0] && $sl[1] == $arr[1]) {
                                       $class = 'selected_date';
                                   }
                               }

                           }
                           $output .= '<td data-time="' .esc_attr($id). '" data-day="' .esc_attr($key). '" class="'.esc_attr($class).'">';

                       }

                       if(isset($value['bookable']) && $value['bookable'] == 2 && empty($value['data'])) {
                           if(!empty($na_text)) {
                               $output .= '<span class="hvr">';
                               if($na_text['short'] != '') {
                                   $output .= esc_attr($na_text['short']);
                               }
                               $output .= '</span>';
                               if($na_text['long'] != '') {
                                   $output .= '<div class="item-detail na">'.esc_attr($na_text['long']).'</div>';
                               }
                           }else{
                               $output .= '';
                           }
                       }

                       if(!empty($value['data'])) {
                           $output .= '<span class="hvr">'.esc_html__('Booked', 'mybooker').'</span>';
                           $output .= '<div class="item-detail">';
                           foreach($value['data'] as $v) {
                               $output .= esc_attr($v['type']).': '.esc_attr($v['name']).'<br />';
                           }
                           $output .= '</div>';
                       }
                       $output .= '</td>';
                   }
                   $output .= '</tr>';
                   $index++;
                 }
                 $output .= '</table>';
             }
             return $output;
         }


        public function mybooker_isInRange($day) {

            $result = false;

            if(!empty($this->close_range)) {

                foreach($this->close_range as $index => $range) {

                    $fr = new DateTime($range['from']);
                    $tr = new DateTime($range['to']);

                    if($day <= $tr->format('Y-m-d') && $day >= $fr->format('Y-m-d') ) {
                        $result = true;
                        break;
                    }
                }
            }
            return $result;
        }


        public static function mybooker_enqueue_script()
        {
            global $wp_locale;
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-validate-min', MY_BOOKER_PLUGIN_DIR_URL.'assets/js/jquery.validate.min.js', array('jquery'));

            wp_enqueue_script('mybooker-js', MY_BOOKER_PLUGIN_DIR_URL.'assets/js/mybooker.min.js', array('jquery'));
            wp_enqueue_script('wpx-moment', MY_BOOKER_PLUGIN_DIR_URL.'assets/js/moment.js', array('jquery'));

            wp_localize_script(
               'mybooker-js', // ajax script from wp_enqueue_script
               'ajax_object',
               array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
            );

            wp_localize_script( 'mybooker-js', 'wpx', array(
            	 'namsg'  => esc_html__( 'N/A', 'mybooker' ),
               'itemsl' => esc_html__( 'Please select', 'mybooker'),
               'booked' => esc_html__( 'Booked', 'mybooker'),
               'wmsg' => esc_html__('Please wait...', 'mybooker'),
               'pmfail' => esc_html__('There is an unexpected issue, please contact admin for more detail', 'mybooker'),
            ));

            //localize our js
            $ary_args = array(
                'closeText'         => esc_html__( 'Done', 'mybooker' ),
                'currentText'       => esc_html__( 'Today', 'mybooker' ),
                'monthNames'        => self::mybooker_strip_array_indices( $wp_locale->month ),
                'monthNamesShort'   => self::mybooker_strip_array_indices( $wp_locale->month_abbrev ),
                'monthStatus'       => esc_html__( 'Show a different month', 'mybooker' ),
                'dayNames'          => self::mybooker_strip_array_indices( $wp_locale->weekday ),
                'dayNamesShort'     => self::mybooker_strip_array_indices( $wp_locale->weekday_abbrev ),
                'dayNamesMin'       => self::mybooker_strip_array_indices( $wp_locale->weekday_initial ),
                'nextText'          => esc_html__( 'Next', 'mybooker' ),
                'prevText'          => esc_html__( 'Prev', 'mybooker' ),
                // set the date format to match the WP general date settings
                'dateFormat'        => mybooker_get_js_date_format(mybooker_opt_dateformat()),
                'timeFormat'        => mybooker_get_js_time_format(mybooker_opt_timeformat()),
                // get the start of week from WP general setting
                'firstDay'          => get_option( 'start_of_week' ),
                // is Right to left language? default is false
                'isRTL'             => $wp_locale->is_rtl(),
            );

            // Pass the localized array to the enqueued JS
            wp_localize_script( 'mybooker-js', 'dpl', $ary_args );
        }

        public static function mybooker_enqueue_css() {
              $settings = get_option('mybooker_settings');
              // add custom style if style is enabled
              if(isset($settings['booking_style_enable']) && $settings['booking_style_enable'] == 1) {
                  wp_enqueue_style('mybooker', MY_BOOKER_PLUGIN_DIR_URL.'assets/css/mybooker.min.css', '98', '');
              }
        }

        /**
         * Format array for the datepicker
         *
         * WordPress stores the locale information in an array with a alphanumeric index, and
         * the datepicker wants a numerical index. This function replaces the index with a number
         */
        public static function mybooker_strip_array_indices( $array_to_strip ) {
            foreach( $array_to_strip as $obj_array_item) {
                $new_arr[] =  $obj_array_item;
            }
            return( $new_arr );
        }
    }
}

add_action( "wp_ajax_mybooker_request_schedule", array("Mybooker_Form", "mybooker_schedule" )); // wpx_ajax + action
add_action( "wp_ajax_nopriv_mybooker_request_schedule", array("Mybooker_Form", "mybooker_schedule" ));
