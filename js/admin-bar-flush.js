/* global cachify_admin_bar_flush_ajax_object */
( function() {
	var is_flushing = false,
		admin_bar_cachify_list_item = document.getElementById( 'wp-admin-bar-cachify' ),
		flush_link = admin_bar_cachify_list_item.querySelector( 'a.ab-item' ),
		fallback_url = flush_link.getAttribute( 'href' ),
		aria_live_area = document.querySelector( '.ab-aria-live-area' );

	// Replacing flush link with button because with AJAX action, it is semantically not a link anymore.
	var button = document.createRange().createContextualFragment( '<button class="ab-item">' + flush_link.innerHTML + '</button>' );
	flush_link.parentNode.replaceChild( button, flush_link );

	var admin_bar_icon = admin_bar_cachify_list_item.querySelector( '#wp-admin-bar-cachify .ab-icon' );

	document.querySelector( '#wp-admin-bar-cachify .ab-item' ).addEventListener( 'click', flush );

	admin_bar_icon.addEventListener( 'animationend', function() {
		admin_bar_icon.classList.remove( 'animate-fade' );
	} );

	function flush_icon_remove_classes() {
		var classes = [
			'animate-fade',
			'animate-pulse',
			'dashicons-trash',
			'dashicons-yes',
			'dashicons-yes-alt',
			'dashicons-dismiss',
		];

		for ( var i = 0; i < classes.length; i++ ) {
			admin_bar_icon.classList.remove( classes[i] );
		}
	}

	function start_flush_icon_reset_timeout() {
		setTimeout( function() {
			flush_icon_remove_classes();
			admin_bar_icon.classList.add( 'animate-fade' );
			admin_bar_icon.classList.add( 'dashicons-trash' );
			is_flushing = false;
			aria_live_area.textContent = '';
		}, 2000 );
	}

	function flush( event ) {
		event.preventDefault();

		if ( is_flushing ) {
			return;
		}
		is_flushing = true;
		aria_live_area.textContent = cachify_admin_bar_flush_ajax_object.flushing;

		if ( admin_bar_icon !== null ) {
			flush_icon_remove_classes();
			admin_bar_icon.classList.add( 'animate-pulse' );
			admin_bar_icon.classList.add( 'dashicons-trash' );
		}

		var request = new XMLHttpRequest();
		request.addEventListener( 'load', function() {
			if ( this.status === 200 ) {
				start_flush_icon_reset_timeout();
				flush_icon_remove_classes();
				admin_bar_icon.classList.add( 'animate-fade' );
				admin_bar_icon.classList.add( cachify_admin_bar_flush_ajax_object.dashicon_success );
				aria_live_area.textContent = cachify_admin_bar_flush_ajax_object.flushed;
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
}() );
