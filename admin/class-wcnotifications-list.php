<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WCNotifications_List' ) ) {

	class WCNotifications_List extends WP_List_Table {

		function __construct() {
			global $status, $page;

			//Set parent defaults
			parent::__construct( array(
				'singular' => 'WCNotification',     //singular name of the listed records
				'plural'   => 'WCNotifications',    //plural name of the listed records
				'ajax'     => false,        //does this table support ajax?
			) );

		}

		function get_columns() {
			$columns = array(
				'wcnotifications_title'        => __( 'Title', 'woo-instant-notifications' ),
				'wcnotifications_description'  => __( 'Description', 'woo-instant-notifications' ),
				'wcnotifications_subject'      => __( 'Subject', 'woo-instant-notifications' ),
				'wcnotifications_heading'      => __( 'Heading', 'woo-instant-notifications' ),
				'wcnotifications_order_action' => __( 'Order Action', 'woo-instant-notifications' ),
				'wcnotifications_enable'       => __( 'Enable', 'woo-instant-notifications' ),
			);
			return $columns;
		}

		function column_wcnotifications_title($item){
			ob_start() ?>
			<strong><a class="row-title"
				href="<?php echo add_query_arg( array( 'type' => 'add-email', 'wcnotifications_edit' => $item['ID'] ), admin_url( 'admin.php?page=wcnotifications-settings' ) ); ?>"
				title="Edit “<?php echo $item['title'] ?>”"><?php echo $item['title'] ?></a>
			</strong>
			<div class="row-actions">
				<span class="edit">
					<a href="<?php echo add_query_arg( array( 'type' => 'add-email', 'wcnotifications_edit' => $item['ID'] ), admin_url( 'admin.php?page=wcnotifications-settings' ) ); ?>"
						data-key="<?php echo $item['ID']; ?>"
						title="Edit this item"><?php
						_e( 'Edit', 'woo-instant-notifications' ); ?>
					</a> |
				</span>
				<span class="delete">
					<a href="<?php echo add_query_arg( array( 'type' => 'view-email', 'wcnotifications_delete' => $item['ID'] ), admin_url( 'admin.php?page=wcnotifications-settings' ) ); ?>"
						class="wcnotifications_delete"
						data-key="<?php echo $item['ID']; ?>"
						title="Edit this item"><?php
						_e( 'Delete', 'woo-instant-notifications' ); ?>
					</a> |
				</span>
			</div><?php
			return ob_get_clean();
		}

		function column_wcnotifications_description($item){
			return isset( $item['description'] ) ? $item['description'] : '';
		}

		function column_wcnotifications_subject($item){
			return isset( $item['description'] ) ? $item['description'] : '';;
		}

		function column_wcnotifications_heading($item){
			return $item['heading'];
		}

		function column_wcnotifications_order_action($item){
			return 'on' == $item['order_action'] ? 'Yes' : 'No'; ;
		}

		function column_wcnotifications_enable($item){
			return 'on' == $item['enable'] ? 'Yes' : 'No';;
		}

		function get_sortable_columns() {
			$sortable_columns = array();
			return $sortable_columns;
		}

		function get_bulk_actions() {
			$actions = array();
			return $actions;
		}

		function process_bulk_action() {
			//Detect when a bulk action is being triggered...
			/*if( 'delete'===$this->current_action() ) {
			}*/

		}

		function prepare_items() {
			global $wpdb; //This is used only if making any database queries

			$per_page = 10;

			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$data = get_option( 'wcnotifications_email_details', array() );

			foreach ( $data as $key => $data_item ) {
				$data[ $key ]['ID'] = $key;
			}

			$current_page = $this->get_pagenum();

			$total_items = count( $data );

			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

			$this->items = $data;

			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
			) );
		}

	}
}
