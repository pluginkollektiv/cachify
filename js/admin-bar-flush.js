/* global cachify_admin_bar_flush_ajax_object */
( function() {
	function flush_icon_remove_classes( admin_bar_icon ) {
		var classes = [
			'animate-fade',
			'animate-pulse',
			'dashicons-trash',
			'dashicons-yes-alt',
			'dashicons-dismiss',
		];

		for ( var i = 0; i < classes.length; i++ ) {
			admin_bar_icon.classList.remove( classes[i] );
		}
	}

	function start_flush_icon_reset_timeout( admin_bar_icon ) {
		setTimeout( function() {
			flush_icon_remove_classes( admin_bar_icon );
			admin_bar_icon.classList.add( 'animate-fade' );
			admin_bar_icon.classList.add( 'dashicons-trash' );
		}, 2000 );
	}

	function flush( event ) {
		event.preventDefault();

		var admin_bar_icon = document.querySelector( '#wp-admin-bar-cachify .ab-icon' );
		if ( ! admin_bar_icon.classList.contains( 'dashicons-trash' ) || admin_bar_icon.classList.contains( 'animate-pulse' ) ) {
			return;
		}
		if ( admin_bar_icon !== null ) {
			flush_icon_remove_classes( admin_bar_icon );
			admin_bar_icon.classList.add( 'animate-pulse' );
			admin_bar_icon.classList.add( 'dashicons-trash' );
		}

		var request = new XMLHttpRequest();
		request.addEventListener( 'load', function() {
			start_flush_icon_reset_timeout( admin_bar_icon );
			flush_icon_remove_classes( admin_bar_icon );
			admin_bar_icon.classList.add( 'animate-fade' );
			if ( this.status === 200 ) {
				admin_bar_icon.classList.add( 'dashicons-yes-alt' );
				return;
			}

			admin_bar_icon.classList.add( 'dashicons-dismiss' );
		} );

		request.addEventListener( 'error', function() {
			start_flush_icon_reset_timeout( admin_bar_icon );
			flush_icon_remove_classes( admin_bar_icon );
			admin_bar_icon.classList.add( 'animate-fade' );
			admin_bar_icon.classList.add( 'dashicons-dismiss' );
		} );

		request.open( 'DELETE', cachify_admin_bar_flush_ajax_object.url );
		request.setRequestHeader( 'X-WP-Nonce', cachify_admin_bar_flush_ajax_object.nonce );
		request.send();
	}

	document.addEventListener( 'DOMContentLoaded', function() {
		var ab_item = document.querySelector( '#wp-admin-bar-cachify .ab-item' );
		ab_item.addEventListener( 'click', flush );

		var admin_bar_icon = document.querySelector( '#wp-admin-bar-cachify .ab-icon' );
		admin_bar_icon.addEventListener( 'animationend', function() {
			admin_bar_icon.classList.remove( 'animate-fade' );
		} );
	} );
}() );
