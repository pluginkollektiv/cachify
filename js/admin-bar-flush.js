/* global cachify_admin_bar_flush_ajax_object */
( function() {
	var is_flushing = false;

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
			is_flushing = false;
		}, 2000 );
	}

	function flush( event ) {
		event.preventDefault();

		var fallback_url = this.getAttribute( 'href' );

		var admin_bar_icon = document.querySelector( '#wp-admin-bar-cachify .ab-icon' );

		if ( is_flushing ) {
			return;
		}
		is_flushing = true;

		if ( admin_bar_icon !== null ) {
			flush_icon_remove_classes( admin_bar_icon );
			admin_bar_icon.classList.add( 'animate-pulse' );
			admin_bar_icon.classList.add( 'dashicons-trash' );
		}

		var request = new XMLHttpRequest();
		request.addEventListener( 'load', function() {
			if ( this.status === 200 ) {
				start_flush_icon_reset_timeout( admin_bar_icon );
				flush_icon_remove_classes( admin_bar_icon );
				admin_bar_icon.classList.add( 'animate-fade' );
				admin_bar_icon.classList.add( 'dashicons-yes-alt' );
				return;
			}

			window.location = fallback_url;
		} );

		request.addEventListener( 'error', function() {
			window.location = fallback_url;
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
