<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Mybooker_Entries_List_Table extends WP_List_Table {

	private $table_name;
	private $order;
	private $orderby;
	private $posts_per_page = 10;
	private $status;

	/** Class constructor */
	public function __construct() {
		global $page, $wpdb;

		$this->status     = array(
			'0' => 'Trash',
			'1' => 'Pending',
			'2' => 'Payment Failed',
			'3' => 'Success',
			'4' => 'Cancelled',
			'5' => 'Paid',
		);
		$this->table_name = $wpdb->prefix . 'booking_entries';
		$this->orderby    = 'id';

		parent::__construct(
			array(
				'singular' => 'booking',
				'plural'   => 'bookings',
			)
		);
	}

	protected function get_views() {
		global $wpdb;

		$status_links = array();
		$link         = get_admin_url( get_current_blog_id(), 'admin.php?page=mybooker' );
		$status_links[ esc_html__( 'All', 'mybooker' ) ] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'All', 'mybooker' ) . '</a>';

		$total_by_status = $wpdb->get_results( "SELECT status, COUNT(id) as total FROM $this->table_name GROUP BY status", ARRAY_A );

		if ( $this->status ) {
			foreach ( $this->status as $k => $v ) {
				foreach ( $total_by_status as $key => $val ) {
					if ( $k == $val['status'] && $val['total'] > 0 && $k == 0 ) {
						$class = ( isset( $_REQUEST['status'] ) &&
								  array_key_exists( intval($_REQUEST['status']), $this->status ) &&
								  $_REQUEST['status'] == $k ) ? 'current' : '';

						$status_links[ $v ] = "<a href='" . esc_url( $link ) . '&status=' . esc_attr( $k ) . "' class='" . esc_attr( $class ) . "'>" . esc_attr( $v ) . " <span class='count'>(" . esc_attr( $val['total'] ) . ')</span></a>';
					}
				} // total status loop
			}
		}

		return $status_links;
	}

	// custom row class
	public function single_row( $item ) {
		$class = '';

		if ( $item['et_status'] == 'unread' ) {
			$class = 'entry_unread';
		}

		echo '<tr class="' . esc_attr( $class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}
	/**
	 * [REQUIRED] this is a default column renderer
	 *
	 * @param $item - row (key, value array)
	 * @param $column_name - string (key)
	 * @return HTML
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	public function extra_tablenav( $which ) {
		global $wpdb;
		global $wp;

		if ( empty( $_SERVER['QUERY_STRING'] ) ) {
			return;
		}

		$cur = add_query_arg( $_SERVER['QUERY_STRING'], '', esc_url( get_admin_url( $wp->request ) ) );

		$move_on_url = $cur . '&booking_type=';

		if ( $which == 'top' ) {
			$top = '<div class="alignleft actions bulkactions">';

			$type_arr = array(
				'1' => esc_html__( 'Guest', 'mybooker' ),
				'2' => esc_html__( 'Member', 'mybooker' ),
			);
			if ( $type_arr ) {
				$top .= '<select name="type-filter" class="wpx-filter-type">';
				$top .= '<option value="">' . esc_html__( 'Booking Type', 'mybooker' ) . '</option>';

				foreach ( $type_arr as $k => $v ) {
					$selected = '';
					if ( isset( $_GET['booking_type'] ) && $_GET['booking_type'] == $k ) {
						$selected = ' selected = "selected"';
					}

					$has_data = $wpdb->get_var( "SELECT COUNT(id) FROM $this->table_name WHERE booking_type =" . esc_attr( $k ) );
					if ( $has_data > 0 ) {
						$top .= '<option value="' . esc_attr( $move_on_url ) . esc_attr( $k ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $v ) . '</option>';
					}
				}
				$top .= '</select>';
			}
			$top .= '</div>';
			echo wp_kses_post($top);
		}
		if ( $which == 'bottom' ) {
			// The code that goes after the table is there
			$bottom  = '<div class="alignleft actions">';
			$bottom .= '<button type="submit" name="action" id="csv-export" class="button action" value="export">' . esc_html__( 'Export All', 'mybooker' ) . '</button>';
			$bottom .= '</div>';
			echo wp_kses_post($bottom);
		}
	}



	/**
	 * [OPTIONAL] this is example, how to render specific column
	 *
	 * method name must be like this: "column_[column_name]"
	 *
	 * @param $item - row (key, value array)
	 * @return HTML
	 */
	public function column_time( $item ) {
		return date_i18n( 'Y-m-d H:i', strtotime( $item['time'] ) );
	}

	public function column_booking_name( $item ) {
		return $item['booking_name'];
	}

	public function column_booking_type( $item ) {
		return mybooker_get_booking_type( $item['booking_type'] );
	}

	public function column_pm_method( $item ) {
		return $item['pm_method'];
	}

	public function column_item( $item ) {
		$post = get_post( $item['item_id'] );
		return $post->post_title;
	}

	public function column_pm_transaction( $item ) {
		return $item['pm_transaction'];
	}

	public function no_items() {
		echo esc_html__( 'No booked entry yet', 'mybooker' );
	}

	public function column_booking_datetime( $item ) {
		$slots = array();

		if ( $item['booking_datetime'] != '' && strstr( $item['booking_datetime'], ',' ) ) {
			foreach ( explode( ',', $item['booking_datetime'] ) as $k => $dt ) {
				$slots[] = explode( '|', $dt );
			}
		} else {
			$slots[] = explode( '|', $item['booking_datetime'] );
		}
		return count( $slots );
	}

	public function column_status( $item ) {
		$values = $this->status;
		return $values[ $item['status'] ];
	}

	/**
	 * [OPTIONAL] this is example, how to render column with actions,
	 * when you hover row "Edit | Delete" links showed
	 *
	 * @param $item - row (key, value array)
	 * @return HTML
	 */
	public function column_id( $item ) {
		if ( ! empty( $item ) ) {
			$page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
			$actions = array(
				'view'  => sprintf( '<a href="?page=mybooker_entry_form&id=%s">%s</a>', esc_attr( $item['id'] ), esc_html__( 'View', 'mybooker' ) ),
				'trash' => sprintf( '<a href="?page=%s&action=trash&id=%s">%s</a>', esc_attr( $page), esc_attr( $item['id'] ), esc_html__( 'Trash', 'mybooker' ) ),
			);
			return sprintf(
				'%s %s',
				sprintf( '<a href="?page=mybooker_entry_form&id=%s">%s</a>', esc_attr( $item['id'] ), '<strong>' . esc_attr( $item['id'] ) . '</strong>' ),
				$this->row_actions( $actions )
			);
		} else {
			return;
		}
	}

	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_sql_orderby($_REQUEST['orderby']) ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field($_REQUEST['order'] )) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( sanitize_text_field($_REQUEST['post_mime_type'] )) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( sanitize_text_field($_REQUEST['detached'] )) . '" />';
		}
		?>
		<p class="search-box">
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
		<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button( esc_attr( $text ), 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * [REQUIRED] this is how checkbox column renders
	 *
	 * @param $item - row (key, value array)
	 * @return HTML
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			esc_attr( $item['id'] )
		);
	}
	/**
	 * [REQUIRED] This method return columns to display in table
	 * you can skip columns that you do not want to show
	 * like content, or description
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />', // Render a checkbox instead of text
			'id'               => esc_html__( '#', 'mybooker' ),
			'booking_name'     => esc_html__( 'Name', 'mybooker' ),
			'booking_email'    => esc_html__( 'E-Mail', 'mybooker' ),
			'item'             => esc_html__( 'Item', 'mybooker' ),
			'booking_type'     => esc_html__( 'Type', 'mybooker' ),
			'booking_datetime' => esc_html__( 'Slot', 'mybooker' ),
			'status'           => esc_html__( 'Status', 'mybooker' ),
			'time'             => esc_html__( 'Date', 'mybooker' ),

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
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'     => array( 'id', true ),
			'status' => array( 'status', false ),
			'time'   => array( 'time', false ),
		);
		return $sortable_columns;
	}
	/**
	 * [OPTIONAL] Return array of bult actions if has any
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'trash' => 'Move to Trash',
		);

		if ( current_user_can( 'manage_options' ) ) {
			$actions['delete'] = 'Delete Permanently';
		}

		return $actions;
	}
	/**
	 * [OPTIONAL] This method processes bulk actions
	 * it can be outside of class
	 * it can not use wp_redirect coz there is output already
	 * in this example we are processing delete action
	 * message about successful deletion will be shown on page in next part
	 */
	public function process_bulk_action() {
		global $wpdb;

		if ( 'trash' === $this->current_action() ) {
			$ids = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();

			if ( ! is_array( $ids ) && $ids != '' ) {
				$ids = explode( ',', $ids );
			}

			if ( ! empty( $ids ) ) {
				foreach ( $ids as $id ) {
					$result = $wpdb->update( $this->table_name, array( 'status' => 0 ), array( 'id' => $id ) );
				}
			}
		}

		if ( 'delete' === $this->current_action() ) {
			$ids = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();

			if ( is_array( $ids ) ) {
				$ids = implode( ',', $ids );
			}
			if ( ! empty( $ids ) && current_user_can( 'manage_options' ) ) {
				$wpdb->query("DELETE FROM $this->table_name WHERE id IN($ids)");
			}
		}

		if ( 'export' === $this->current_action() ) {
			$this->export();
		}
	}

	/**
	 * Export all item to csv
	 *
	 * @return csv files
	 */
	public function export() {
		global $wpdb;
		$rows     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name ORDER BY id DESC", 1 ), ARRAY_A );
		$fields   = array(
			'id'               => esc_html__( '#', 'mybooker' ),
			'booking_name'     => esc_html__( 'Name', 'mybooker' ),
			'booking_email'    => esc_html__( 'E-Mail', 'mybooker' ),
			'item'             => esc_html__( 'Item', 'mybooker' ),
			'booking_type'     => esc_html__( 'Type', 'mybooker' ),
			'booking_datetime' => esc_html__( 'Slot', 'mybooker' ),
			'status'           => esc_html__( 'Status', 'mybooker' ),
			'time'             => esc_html__( 'Date', 'mybooker' ),
		);
		$filename = 'Booking_Export_All_' . date( 'Y-m-d' );

		if ( ! empty( $rows ) ) {
			 header( 'Content-Type: text/csv' );
			 header( 'Content-Disposition: attachment; filename="' . esc_attr( $filename ) . '.csv"' );
			 // clean output buffer
			 ob_end_clean();

			 $fp      = fopen( 'php://output', 'w' );
			 $headers = array_values( $fields );

			 fputcsv( $fp, array_values( $fields ) );

			foreach ( $rows as $row ) {
				 $output = array();
				foreach ( $fields as $k => $v ) {
					if ( $k == 'item' ) {
						$post           = get_post( $row['item_id'] );
						$output['item'] = $post->post_title;
					}

					if ( $k == 'booking_type' ) {
						$output['booking_type'] = mybooker_get_booking_type( $row['booking_type'] );
					}

					if ( $k == 'booking_datetime' ) {
						$slots = array();
						if ( $row['booking_datetime'] != '' && strstr( $row['booking_datetime'], ',' ) ) {
							foreach ( explode( ',', $row['booking_datetime'] ) as $k => $dt ) {
								$slots[] = explode( '|', $dt );
							}
						} else {
							$slots[] = explode( '|', $row['booking_datetime'] );
						}

						$output['booking_datetime'] = count( $slots );
					}

					if ( $k == 'status' ) {
						$values           = $this->status;
						$output['status'] = $values[ $row['status'] ];
					}
					if ( $k == 'booking_name' ) {
						$output['booking_name'] = $row['booking_name'];
					}

					if ( $k == 'booking_email' ) {
						$output['booking_email'] = $row['booking_email'];
					}

					if ( $k == 'id' ) {
						$output['id'] = $row['id'];
					}

					if ( $k == 'time' ) {
						$output['time'] = $row['time'];
					}
				}

				 fputcsv( $fp, $output );
			}

			 fclose( $fp );
			 exit;
		}
	}
	/**
	 * [REQUIRED] This is the most important method
	 *
	 * It will get rows from database and prepare them to be showed in table
	 */
	public function prepare_items() {
		global $wpdb;
		// do not forget about tables prefix

		$per_page = 10; // constant, how much records will be shown per page
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// here we configure table headers, defined in our methods
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// [OPTIONAL] process bulk action if any
		$this->process_bulk_action();

		// prepare query params, as usual current page, order by and order direction
		$paged   = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] - 1 ) * $per_page ) : 0;
		$orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? sanitize_sql_orderby($_REQUEST['orderby']) : 'id';
		$order   = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array( 'asc', 'desc' ) ) ) ? sanitize_text_field($_REQUEST['order']) : 'desc';

		$status = ( isset( $_REQUEST['status'] ) && array_key_exists( intval($_REQUEST['status']), $this->status ) ) ? intval($_REQUEST['status']) : '';

		$sq = '';

		if ( $status != '' ) {
			$sq .= "AND status = '" . esc_sql( $status ) . "'";
		}

		$type = ( isset( $_REQUEST['booking_type'] ) && mybooker_get_booking_type( sanitize_text_field($_REQUEST['booking_type'] )) != '' ) ? sanitize_text_field($_REQUEST['booking_type']) : '';

		if ( $type != '' ) {
			$sq .= "AND booking_type = '" . esc_sql( $type ) . "'";
		}
		// search query
		$search = '';

		if ( ! empty( $_REQUEST['s'] ) ) {
			$search = "AND booking_name LIKE '%" . esc_sql( $wpdb->esc_like( sanitize_text_field($_REQUEST['s'] )) ) . "%'";
		}

		// will be used in pagination settings
		$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $this->table_name WHERE 1 = 1 $search $sq" );

		$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE 1 = 1 $search $sq ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged ), ARRAY_A );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // total items defined above
				'per_page'    => $per_page, // per page constant defined at top of method
				'total_pages' => ceil( $total_items / $per_page ), // calculate pages count
			)
		);
		return $this->items;
	}
}
