<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCNotifications_Admin' ) ) {
define('temp_file', ABSPATH.'/_temp_out.txt' );
	/**
	 * Admin WooCommerce Instant Notifications Class
	 *
	 * @class WCE_Admin
	 * @version	0.1
	 */
	class wcnotifications_Admin {

		/**
		 * @var wcnotifications_Admin The single instance of the class
		 * @since 0.1
		 */
		protected static $_instance = null;

		/**
		 * Main wcnotifications_Admin Instance
		 *
		 * Ensures only one instance of wcnotifications_Admin is loaded or can be loaded.
		 *
		 * @since 0.1
		 * @static
		 * @return wcnotifications_Admin - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'activated_plugin', $this->plugin_install());
			add_action( 'admin_menu', array( $this, 'wcnotifications_settings_menu' ), 100 );

			add_action( 'woocommerce_new_order', 'do_email_actions',  10, 2  );

			add_action( 'admin_init', array( $this, 'wcnotifications_email_actions_details' ) );
			add_action( 'save_post', array( $this, 'do_email_actions' ), 10, 2 );

			add_filter( 'woocommerce_email_classes', array( $this, 'wcnotifications_instant_woocommerce_emails' ) );

			add_filter( 'woocommerce_order_actions', array( $this, 'wcnotifications_change_action_emails' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'wcnotifications_enqueue_scripts' ) );

			add_filter( 'woocommerce_email_actions', array( $this, 'wcnotifications_filter_actions' ) );

		}

		function plugin_install() {

                include_once('class-wcnotifications-install.php');
                $install = new wc_notifications_install();

                $install->install();
                }


		function wcnotifications_enqueue_scripts() {
			wp_register_script( 'jquery-cloneya', WCNotifications_PLUGIN_URL . 'js/jquery-cloneya.min.js', array( 'jquery' ) );
			wp_register_script( 'wcnotifications-custom-scripts', WCNotifications_PLUGIN_URL . 'js/wcnotifications-custom-scripts.js', array( 'jquery' ), WCNotifications_VERSION );
		}






		function wcnotifications_settings_menu() {

			add_submenu_page( 'woocommerce', __( 'Woo Instant Notifications', 'woo-instant-notifications' ), 'Instant Notifications', 'manage_options', 'wcnotifications-settings', array( $this, 'wcnotifications_settings_callback' ) );

		}

		function wcnotifications_settings_callback() {

			$this->wcnotifications_woocommerce_check();

			?>
 			<div class="wrap">
				<h2><?php _e( 'Woo Instant Notifications Settings', 'woo-instant-notifications' ); ?></h2>
			
	<br><img src="<?php echo plugins_url( 'images/logo.jpg', __FILE__ ); ?>" border="0" /> </br>
				<?php
				if ( ! isset( $_REQUEST['type'] ) ) {
					$type = 'today';
				} else {
					$type = $_REQUEST['type'];
				}
				$all_types = array( 'add-email', 'view-email' );
				if ( ! in_array( $type, $all_types ) ) {
					$type = 'add-email';
				}
				?>
				<ul class="subsubsub">
					<li class="today"><a class ="<?php echo ( 'add-email' == $type ) ? 'current' : ''; ?>" href="<?php echo add_query_arg( array( 'type' => 'add-email' ), admin_url( 'admin.php?page=wcnotifications-settings' ) ); ?>"><?php _e( 'Add Instant Notifications', 'woo-instant-notifications' ); ?></a> |</li>
					<li class="today"><a class ="<?php echo ( 'view-email' == $type ) ? 'current' : ''; ?>" href="<?php echo add_query_arg( array( 'type' => 'view-email' ), admin_url( 'admin.php?page=wcnotifications-settings' ) ); ?>"><?php _e( 'View Notifications', 'woo-instant-notifications' ); ?></a></li>
				</ul>
				<?php $this->wcnotifications_render_sections( $type ); ?>
			</div>
			<?php

		}

		function wcnotifications_render_sections( $type ) {

			if ( 'add-email' == $type ) {
				$this->wcnotifications_render_add_email_section();
			} else if ( 'view-email' == $type ) {
				$this->wcnotifications_render_view_email_section();
			} else {
				$this->wcnotifications_render_add_email_section();
			}

		}

		function wcnotifications_render_add_email_section() {

			$wcnotifications_detail = array();
			if ( isset( $_REQUEST['wcnotifications_edit'] ) ) {
				$wcnotifications_email_details = get_option( 'wcnotifications_email_details', array() );
				if ( ! empty( $wcnotifications_email_details ) ) {
					foreach ( $wcnotifications_email_details as $key => $details ) {
						if ( $_REQUEST['wcnotifications_edit'] == $key ) {
							$wcnotifications_detail = $details;
							$wcnotifications_detail['template'] = stripslashes( $wcnotifications_detail['template'] );
						}
					}
				}
			}

			$wc_statuses = wc_get_order_statuses();
			if ( ! empty( $wc_statuses ) ) {
				foreach ( $wc_statuses as $k => $status ) {
					$key = ( 'wc-' === substr( $k, 0, 3 ) ) ? substr( $k, 3 ) : $k;
					$wc_statuses[ $key ] = $status;
					unset( $wc_statuses[ $k ] );
				}
			}

			wp_enqueue_script( 'jquery-cloneya' );
			wp_enqueue_script( 'wcnotifications-custom-scripts' );

			?>
			<form method="post" action="">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Title', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Title of the Notification. )' ); ?>
								</span>
						</th>
						<td>
							<input name="wcnotifications_title" id="wcnotifications_title" type="text" required value="<?php echo isset( $wcnotifications_detail['title'] ) ? $wcnotifications_detail['title'] : ''; ?>" placeholder="<?php _e( 'Title', 'woo-instant-notifications' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Description', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Email Description to display at Woocommerce Email Setting. )' ); ?>
								</span>
						</th>
						<td>
							<textarea name="wcnotifications_description" id="wcnotifications_description" required placeholder="<?php _e( 'Description', 'woo-instant-notifications' ); ?>" ><?php echo isset( $wcnotifications_detail['description'] ) ? $wcnotifications_detail['description'] : ''; ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Subject', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Email Subject <br/>[Try this placeholders : <i>{site_title}, {order_number}, {order_date}</i>] )' ); ?>
								</span>
						</th>
						<td>
							<input name="wcnotifications_subject" id="wcnotifications_subject" type="text" required value="<?php echo isset( $wcnotifications_detail['subject'] ) ? $wcnotifications_detail['subject'] : ''; ?>" placeholder="<?php _e( 'Subject', 'woo-instant-notifications' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Recipients', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( 'Recipients email addresses separated with comma', 'woo-instant-notifications' ); ?>
								</span>
						</th>
						<td>
							<input name="wcnotifications_recipients" id="wcnotifications_recipients" type="text" value="<?php echo isset( $wcnotifications_detail['recipients'] ) ? $wcnotifications_detail['recipients'] : ''; ?>" placeholder="<?php _e( 'Recipients', 'woo-instant-notifications' ); ?>" />
						</td>
					</tr>
					
					
					<tr>
						<th scope="row">
							<?php _e( 'Choose Order Status', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Choose order statuses when changed this notification should fire. )', 'woo-instant-notifications' ); ?>
								</span>
						</th>
						<td>
							<div class="status-clone-wrapper">
								<?php
								if ( ! empty( $wc_statuses ) ) {
									if ( ! empty( $wcnotifications_detail['from_status'] ) ) {
										foreach ( $wcnotifications_detail['from_status'] as $key => $status ) {
											?>
											<div class="toclone">
												<select name="wcnotifications_from_status[]" required>
													<option value=""><?php _e( 'Select From Status', 'woo-instant-notifications' ); ?></option>
													<?php
													$status_options = '';
													foreach ( $wc_statuses as $k => $wc_status ) {
														$selected = '';
														if ( $k == $status ) {
															$selected = 'selected="selected"';
														}
														$status_options .= '<option value="' . $k . '" ' . $selected . '>' . $wc_status . '</option>';
													}
													echo $status_options;
													?>
												</select>
												<select name="wcnotifications_to_status[]" required>
													<option value=""><?php _e( 'Select To Status', 'woo-instant-notifications' ); ?></option>
													<?php
													$status_options = '';
													foreach ( $wc_statuses as $k => $wc_status ) {
														$selected = '';
														if ( $k == $wcnotifications_detail['to_status'][ $key ] ) {
															$selected = 'selected="selected"';
														}
														$status_options .= '<option value="' . $k . '" ' . $selected . '>' . $wc_status . '</option>';
													}
													echo $status_options;
													?>
												</select>
												<a href="#" class="clone" title="<?php _e( 'Add Another', 'woo-instant-notifications' ) ?>">+</a>
												<a href="#" class="delete" title="<?php _e( 'Delete', 'woo-instant-notifications' ) ?>">-</a>
											</div>
											<?php
										}
									} else {
										$status_options = '';
										foreach ( $wc_statuses as $k => $wc_status ) {
											$status_options .= '<option value="' . $k . '">' . $wc_status . '</option>';
										}
										?>
										<div class="toclone">
											<select name="wcnotifications_from_status[]" required>
												<option value=""><?php _e( 'Select From Status', 'woo-instant-notifications' ); ?></option>
												<?php echo $status_options; ?>
											</select>
											<select name="wcnotifications_to_status[]" required>
												<option value=""><?php _e( 'Select To Status', 'woo-instant-notifications' ); ?></option>
												<?php echo $status_options; ?>
											</select>
											<a href="#" class="clone" title="<?php _e( 'Add Another', 'woo-instant-notifications' ) ?>">+</a>
											<a href="#" class="delete" title="<?php _e( 'Delete', 'woo-instant-notifications' ) ?>">-</a>
										</div>
										<?php
									}
								}
								?>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Template', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
                                <?php _e( '( Use these tags to to print them in the notification. - ', 'woo-instant-notifications' ) ?><br/>
                                <i>{order_date},
										{order_number},
										{woocommerce_email_order_meta},
										{order_billing_name},
										{email_order_items_table},
										{email_order_total_footer},
										{order_billing_email},
										{order_billing_phone},
										{email_addresses}</i> )
								</span>
						</th>
						<td>
							<?php
							$settings = array(
								'textarea_name' => 'wcnotifications_template',
							);
							$con = html_entity_decode( isset( $wcnotifications_detail['template'] ) ? $wcnotifications_detail['template'] : '' );
							if(empty($con)){
							$con = "<strong>Order From:</strong>
{order_billing_name}
**************************

<strong>Details:</strong>
**************************
{email_order_items_table}

**************************

{email_order_total_footer}

<strong>Address</strong>
**************************

{order_billing_name}
{order_shipping_address_1}

{order_shipping_city}

{order_shipping_state}

{order_shipping_postcode}

{order_shipping_country}

<i>tel: {order_billing_phone}</i>

**************************

Hardware Group LTD ";
							}
							wp_editor( $con   , 'ezway_custom_email_new_order', $settings );
							?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Put It In Order Actions?', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Order Edit screen at backend will have this notification as order action. )', 'woo-instant-notifications' ); ?>
								</span>
						</th>
						<td>
							<input name="wcnotifications_order_action" id="wcnotifications_order_action" type="checkbox" <?php echo ( isset( $wcnotifications_detail['order_action'] ) && 'on' == $wcnotifications_detail['order_action'] ) ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Enable?', 'woo-instant-notifications' ); ?>
							<span style="display: block; font-size: 12px; font-weight: 300;">
							<?php _e( '( Enable this notification here. )', 'woo-instant-notifications' ); ?>
								</span>
						</th>
						<td>
							<input name="wcnotifications_enable" id="wcnotifications_enable" type="checkbox" <?php echo ( isset( $wcnotifications_detail['enable'] ) && 'on' == $wcnotifications_detail['enable'] ) ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" name="wcnotifications_submit" id="wcnotifications_submit" class="button button-primary" value="<?php _e( 'Save Changes', 'woo-instant-notifications' ); ?>">
				</p>
				<?php
				if ( isset( $_REQUEST['wcnotifications_edit'] ) ) {
					?>
					<input type="hidden" name="wcnotifications_update" id="wcnotifications_update" value="<?php echo $_REQUEST['wcnotifications_edit']; ?>" />
					<?php
				}
				?>
			</form>
			<?php

		}

		function wcnotifications_render_view_email_section() {
			include_once( 'class-wcnotifications-list.php' );
			$wcnotifications_list = new wcnotifications_List();
			$wcnotifications_list->prepare_items();
			$wcnotifications_list->display();
		}

		/**
		 * Save notification options
		 */
		function wcnotifications_email_actions_details() {

			if ( isset( $_POST['wcnotifications_submit'] ) ) {

				$title         = filter_input( INPUT_POST, 'wcnotifications_title', FILTER_SANITIZE_STRING );
				$description   = filter_input( INPUT_POST, 'wcnotifications_description', FILTER_SANITIZE_STRING );
				$subject       = filter_input( INPUT_POST, 'wcnotifications_subject', FILTER_SANITIZE_STRING );
				$recipients    = filter_input( INPUT_POST, 'wcnotifications_recipients', FILTER_SANITIZE_STRING );
				$heading       = filter_input( INPUT_POST, 'wcnotifications_heading', FILTER_SANITIZE_STRING );
				$from_status   = isset( $_POST['wcnotifications_from_status'] ) ? $_POST['wcnotifications_from_status'] : '';
				$to_status     = isset( $_POST['wcnotifications_to_status'] ) ? $_POST['wcnotifications_to_status'] : '';
				$template      = isset( $_POST['wcnotifications_template'] ) ? $_POST['wcnotifications_template'] : '';
				$order_action  = filter_input( INPUT_POST, 'wcnotifications_order_action', FILTER_SANITIZE_STRING );
				$order_action  = empty( $order_action ) ? 'off' : $order_action;
				$enable        = filter_input( INPUT_POST, 'wcnotifications_enable', FILTER_SANITIZE_STRING );
				$enable        = empty( $enable ) ? 'off' : $enable;
				$send_customer = filter_input( INPUT_POST, 'wcnotifications_send_customer', FILTER_SANITIZE_STRING );
				$send_customer = empty( $send_customer ) ? 'off' : $send_customer;

				$wcnotifications_email_details = get_option( 'wcnotifications_email_details', array() );

				$data = array(
					'title'         => $title,
					'description'   => $description,
					'subject'       => $subject,
					'recipients'    => $recipients,
					'heading'       => $heading,
					'from_status'   => $from_status,
					'to_status'     => $to_status,
					'template'      => $template,
					'order_action'  => $order_action,
					'enable'        => $enable,
					'send_customer' => $send_customer,
				);

				if ( isset( $_POST['wcnotifications_update'] ) ) {
					if ( ! empty( $wcnotifications_email_details ) ) {
						foreach ( $wcnotifications_email_details as $key => $details ) {
							if ( $key == $_POST['wcnotifications_update'] ) {
								$data['id'] = $details['id'];
								$wcnotifications_email_details[ $key ] = $data;
							}
						}
					}
				} else {
					$id = uniqid( 'wnotifications' );
					$data['id'] = $id;
					array_push( $wcnotifications_email_details, $data );
				}

				update_option( 'wcnotifications_email_details', $wcnotifications_email_details );

				add_settings_error( 'wcnotifications-settings', 'error_code', $title.' is saved and if you have enabled it then you can see it in Woocommerce Email Settings Now', 'success' );

			} else if ( isset( $_REQUEST['wcnotifications_delete'] ) ) {

				$wcnotifications_email_details = get_option( 'wcnotifications_email_details', array() );

				$delete_key = $_REQUEST['wcnotifications_delete'];

				if ( ! empty( $wcnotifications_email_details ) ) {
					foreach ( $wcnotifications_email_details as $key => $details ) {
						if ( $key == $delete_key ) {
							unset( $wcnotifications_email_details[ $key ] );
						}
					}
				}

				update_option( 'wcnotifications_email_details', $wcnotifications_email_details );

				add_settings_error( 'wcnotifications-settings', 'error_code', 'Notification settings deleted!', 'success' );

			}

		}

		/**
		 * custom order action email classes instantiation
		 *
		 * @param $email_classes
		 *
		 * @return mixed
		 */
		function wcnotifications_instant_woocommerce_emails( $email_classes ) {

			include_once( 'class-wcnotifications-instance.php' );

			$wcnotifications_email_details = get_option( 'wcnotifications_email_details', array() );

			if ( ! empty( $wcnotifications_email_details ) ) {

				foreach ( $wcnotifications_email_details as $key => $details ) {

					$enable = $details['enable'];

					if ( 'on' == $enable ) {

						$title         = isset( $details['title'] ) ? $details['title'] : '';
						$id            = isset( $details['id'] ) ? $details['id'] : '';
						$description   = isset( $details['description'] ) ? $details['description'] : '';
						$subject       = isset( $details['subject'] ) ? $details['subject'] : '';
						$recipients    = isset( $details['recipients'] ) ? $details['recipients'] : '';
						$heading       = isset( $details['heading'] ) ? $details['heading'] : '';
						$from_status   = isset( $details['from_status'] ) ? $details['from_status'] : array();
						$to_status     = isset( $details['to_status'] ) ? $details['to_status'] : array();
						$send_customer = isset( $details['send_customer'] ) ? $details['send_customer'] : array();
						$template      = stripslashes( html_entity_decode( isset( $details['template'] ) ? $details['template'] : '' ) );

						$wcnotifications_instance = new wcnotifications_Instance( $id, $title, $description, $subject, $recipients, $heading, $from_status, $to_status, $send_customer, $template );

						$email_classes[ 'WCInstant_Notifications_'.$id.'_Email' ] = $wcnotifications_instance;

					}
				}
			}

			return $email_classes;

		}

		/**
		 * woocommerce order action change
		 *
		 * @param $emails
		 *
		 * @return mixed
		 */
		function wcnotifications_change_action_emails( $emails ) {

			$wcnotifications_email_details = get_option( 'wcnotifications_email_details', array() );

			if ( ! empty( $wcnotifications_email_details ) ) {

				foreach ( $wcnotifications_email_details as $key => $details ) {

					$enable = $details['enable'];
					$order_action = $details['order_action'];

					if ( 'on' == $enable && 'on' == $order_action ) {

						$id             = $details['id'];
						$title         = isset( $details['title'] ) ? $details['title'] : '';

                        $emails[$id] = __( 'Resend ' . $title, 'woo-instant-notifications' );

					}
				}
			}

			return $emails;

		}

		/**
		 * woocommerce active check
		 */
		function wcnotifications_woocommerce_check() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				?><h2><?php _e( 'WooCommerce is not activated!', 'woo-instant-notifications' );?></h2><?php
				die();
			}
		}

		/**
		 * filter the email actions for order notifications
		 *
		 * @param $actions
		 *
		 * @return array
		 */
		function wcnotifications_filter_actions( $actions ) {

			$wcnotifications_email_details = get_option( 'wcnotifications_email_details', array() );

			if ( ! empty( $wcnotifications_email_details ) ) {

				foreach ( $wcnotifications_email_details as $key => $details ) {

					$enable = $details['enable'];

					if ( 'on' == $enable ) {

						$from_status   = isset( $details['from_status'] ) ? $details['from_status'] : array();
						$to_status     = isset( $details['to_status'] ) ? $details['to_status'] : array();

						if ( ! empty( $from_status ) && ! empty( $to_status ) ) {
							foreach ( $from_status as $k => $status ) {
								$hook = 'woocommerce_order_status_' . $status . '_to_' . $to_status[ $k ];
								if ( ! in_array( $hook, $actions ) ) {
									$actions[] = 'woocommerce_order_status_' . $status . '_to_' . $to_status[ $k ];
								}
							}
						}

					}
				}
			}
			return $actions;
		}

		function do_email_actions( $post_id, $post ) {

			if ( ! empty( $_POST['wc_order_action'] ) ) {

				// Order data saved, now get it so we can manipulate status.
				$order = wc_get_order( $post_id );

				$action = wc_clean( $_POST['wc_order_action'] );

				$wcnotifications_email_details = get_option( 'wcnotifications_email_details', array() );
				if ( ! empty( $wcnotifications_email_details ) ) {
					foreach ( $wcnotifications_email_details as $key => $details ) {
						$enable = $details['enable'];
						$order_action = $details['order_action'];
						if ( 'on' == $enable && 'on' == $order_action ) {
							$id             = $details['id'];
							if ( $id == $action ) {
								WC()->payment_gateways();
								WC()->shipping();
								WC()->mailer()->emails['WCInstant_Notifications_'.$id.'_Email']->trigger( $order->get_id(), $order );
                            }
						}
					}
				}
            }

        }

	}

}

/**
 * Returns the main instance of wcnotifications_Admin to prevent the need to use globals.
 *
 * @since  0.1
 * @return wcnotifications_Admin
 */
function woo_instant_notifications_admin() {
	return wcnotifications_Admin::instance();
}
woo_instant_notifications_admin();
