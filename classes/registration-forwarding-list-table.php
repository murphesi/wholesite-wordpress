<?php
// Load list table class if not already
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Third party user registration forwarding class
 */
class Wholesite_Forms_List_Table extends WP_List_Table {
	public $message = NULL;
	
	function __construct() {
		$this->message = '';

		parent::__construct( array(
			'singular' => 'form',
			'plural' => 'forms',
			'ajax' => false
		) );
	}
	
	function column_default( $item, $column_name ) {
		if ( isset( $item[$column_name] ) ) {
			return htmlspecialchars( $item[$column_name], ENT_QUOTES );
		} else {
			return '';
		}
	}
	
	function column_cb( $item ) {
		return '<input type="checkbox" name="' . esc_attr( $this->_args['singular'] ) . '[]" value="' . esc_attr( $item['id'] ) . '" />';
	}
	
	function column_key( $item ) {
		if ( $item['deleted'] ) {
			if ( isset( $item['key'] ) ) {
				$item_name = $item['key'];
			} else {
				$item_name = $item['id'];
			}
		
			$actions = array(
				'edit' => '<a href="admin.php?page=wholesite_registration&amp;fid=' . esc_attr( $item['id'] ) . '&amp;restore=1">Restore</a>'
			);
			
			return '<strong>' . $item_name . '</strong>' . $this->row_actions( $actions );
	  	} else {
			$actions = array(
				//'edit' => '<a href="admin.php?page=wholesite_registration&amp;fid=' . esc_attr( $item['id'] ) . '">Edit</a> &middot; ' . 
					'<a href="admin.php?page=wholesite_registration&amp;fid_del=' . esc_attr( $item['id'] ) . '">Delete</a>'
			);

			return '<strong><a class="row-title" href="admin.php?page=wholesite_registration&amp;fid=' . esc_attr( $item['id'] ) . '">' . esc_html( $item['key'] ) . '</a></strong>' . $this->row_actions( $actions );
		}
	}

	function get_columns(){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'key' => 'Field Key Value',
			'url' => 'Forwarding URL'
		);

		return $columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);
		
		return $actions;
	}

	function set_message( $single_message ) {
		$this->message = $single_message;
	}

	function get_message() {
		if ( $this->message != null) {
			return $this->message;
		}

		return null;
	}

	function process_bulk_action() {
		$forward_ids = $_GET['form'];

		if ( 'delete' == $this->current_action() ) {
			$items_removed = false;
			$options = get_option( 'wholesite_registration_forwarding', array() ); 
		
			foreach ( $forward_ids as $fid ) {
				for ( $i = 0; $i < count( $options['forwards'] ); $i++ ) {
					if ( intval( $fid ) == $options['forwards'][$i]['id'] ) {
						unset( $options['forwards'][$i] );
						
						$options['forwards'] = array_values( $options['forwards'] );

						$items_removed = true;

						?>
						<script type="text/javascript">
							jQuery(function($){
								jQuery("#the-list tr:has(th.check-column input[type='checkbox'][value='<?php echo $fid; ?>'])").fadeOut();
							});
						</script>
						<?php

						break;
					}
				}
			}

			if ($items_removed) {
				update_option( 'wholesite_registration_forwarding', $options );

			 	$this->set_message('Forwards Deleted');
			}
		}
	}
	
	function prepare_items() {
		$per_page = 50;
		$current_page = ( isset( $_GET['paged'] ) ) ? intval( $_GET['paged'] ) : 1;

		$columns = $this->get_columns();
		$hidden = get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		$limit = ( $current_page - 1 ) * $per_page;

		// get data
		$data = get_option( 'wholesite_registration_forwarding' );

		// sort data
		$sort = array();
		foreach( $data['forwards'] as $key => $row ) {
			$sort[$key] = $row['key'];
		}

		array_multisort( $sort, SORT_ASC, $data['forwards'] );
		
		// only display items for this page
		$this->items = array_slice( $data['forwards'], $limit, $per_page );
		
		$total_items = count( $data['forwards'] );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}
}

function wholesite_registration_forwarding_list_page_table() {
	$listTable = new Wholesite_Forms_List_Table();
	
	// Remove items marked for deletion
	if ( isset( $_GET['fid_del'] ) ) {
		$fid = intval( $_GET['fid_del'] );

		$options = get_option( 'wholesite_registration_forwarding', array() );

		for ( $i = 0; $i < count( $options['forwards'] ); $i++ ) {
			if ( $fid == $options['forwards'][$i]['id'] ) {
				unset( $options['forwards'][$i] );
			}
		}

		$options['forwards'] = array_values( $options['forwards'] );

		update_option( 'wholesite_registration_forwarding', $options );

		$listTable->set_message('Forward Deleted');
	}

	$listTable->process_bulk_action();
	$listTable->prepare_items();

	if ( $listTable->get_message() != null ) { 
		echo '<div class="updated"><p>' . esc_html( $listTable->get_message() ) . '</p></div>';
	} 

	echo '<form id="ws-forward-list-filter" method="get">';
		echo '<input type="hidden" name="page" value="' . htmlspecialchars( stripslashes( $_REQUEST['page'] ) ) . '" />';
		$listTable->display();
	echo '</form>';
}
