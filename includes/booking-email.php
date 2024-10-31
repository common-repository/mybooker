<?php
if (!class_exists('Mybooker_Email')) {

    class Mybooker_Email
    {
        private $id;
        private static $instance = null;

        protected $tags;
        protected $from_email;
        protected $from_name;
        protected $message;

        /**
         * Gets an instance of our plugin.
         *
         * @return Mybooker_Email
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function __construct($id)
        {
            $settings = get_option('mybooker_settings');
            $this->id = $id;
            $this->from_email = $settings['booking_email_from'];
            $this->from_name  = $settings['booking_email_name'];
            $this->subject    = $settings['booking_email_subject'];
            $this->message    = $settings['booking_email_message'];
            $this->cc_email   = $settings['booking_email_cc'];

        }

        public function mybooker_get_tags()
        {
            $result = array();
            if ($this->message != '') {
              preg_match_all( '%\[.*?]%', $this->message, $matches, PREG_SET_ORDER );
              if ( !empty( $matches ) ) {
                  foreach($matches as $k => $v) {
                    $name = str_replace(']', '', str_replace('[', '', $v[0]));
                    $result[$name] = '';
                  }
              }
            }
            return $result;
        }

        /**
         * pass email content base on tags
         * @param  [type] $entry [description]
         * @return [type]        [description]
         */
        public function mybooker_get_content($data)
        {

            $tags   = $this->mybooker_get_tags();
            $item   = shortcode_atts($tags, $data);
            $content = $this->message;

            foreach ($item as $k => $v) {
              preg_match_all( '%\['.$k.']%', $content, $matches, PREG_SET_ORDER );
              if ( !empty( $matches ) ) {
                  $content = preg_replace( '%\['.$k.']%', $v, $content );
              }
            }

            return $content;
        }

        public function mybooker_send_message()
        {
            if($this->id) {
                $obj    = new Mybooker_Entry();
                $data   = $obj->mybooker_get_entry_data($this->id);
                $notice = '';

                $to = $data['booking_email'];
                if($to != '') {
                    $message  = $this->mybooker_get_content($data);

                    $from     = '"'. esc_attr($this->from_name) .'" <'.esc_attr($this->from_email).'>';
                    $subject  = $this->subject;

                    //'From: Me Myself <me@example.net>'
                    $headers[] = 'From: '.esc_attr($from);
                    $headers[] = 'Content-Type:text/plain; charset=UTF-8';

                    if(get_bloginfo('admin_email')) {
                        $headers[] = 'Bcc: '.get_bloginfo('admin_email');
                    }
                    if($this->cc_email != '') {
                        $headers[] = 'Cc: '.esc_attr($this->cc_email);
                    }

                    try {
                        wp_mail( $to, $subject, $message, $headers);
                    } catch (\Exception $e) {
                        $notice = $e->message;
                    }
                }else{
                   $notice = esc_html__('Could not send email, please check plugin settings', 'mybooker');
                }
            }else{
                $notice = esc_html__('Could find entry', 'mybooker');
            }

            return $notice;
        }
    }
}
