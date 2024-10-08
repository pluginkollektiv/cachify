/* global cachifyAdminBarFlushAjaxObject */
document.addEventListener('DOMContentLoaded', () => {
	let isFlushing = false;
	const adminBarItem = document.getElementById('wp-admin-bar-cachify');

	if (!adminBarItem) {
		// Admin bar item is not present in DOM, exit here.
		return;
	}

	const flushLink = adminBarItem.querySelector('a.ab-item');
	const fallbackUrl = flushLink.getAttribute('href');
	const ariaLiveArea = document.querySelector('.ab-aria-live-area');

	// Replacing flush link with button because with AJAX action, it is semantically not a link anymore.
	const button = document.createElement('button');
	button.classList.add('ab-item');
	button.innerHTML = flushLink.innerHTML;
	flushLink.parentNode.replaceChild(button, flushLink);

	button.addEventListener('click', flush);

	const adminBarIcon = adminBarItem.querySelector(
		'#wp-admin-bar-cachify .ab-icon'
	);
	adminBarIcon.addEventListener('animationend', () => {
		adminBarIcon.classList.remove('animate-fade');
	});

	/**
	 * Remove classes used for animation from flush icon.
	 */
	function flushIconRemoveClasses() {
		const classes = [
			'animate-fade',
			'animate-pulse',
			'dashicons-trash',
			'dashicons-yes',
			'dashicons-yes-alt',
			'dashicons-dismiss',
		];

		for (const element of classes) {
			adminBarIcon.classList.remove(element);
		}
	}

	/**
	 * Add animation classes to flush icon.
	 */
	function startFlushIconResetTimeout() {
		setTimeout(() => {
			flushIconRemoveClasses();
			adminBarIcon.classList.add('animate-fade', 'dashicons-trash');
			isFlushing = false;
			ariaLiveArea.textContent = '';
		}, 2000);
	}

	/**
	 * Event listener to flush the cache.
	 *
	 * @param {MouseEvent} event Click event
	 */
	function flush(event) {
		event.preventDefault();

		if (isFlushing) {
			return;
		}
		isFlushing = true;
		ariaLiveArea.textContent = cachifyAdminBarFlushAjaxObject.flushing;

		if (adminBarIcon !== null) {
			flushIconRemoveClasses();
			adminBarIcon.classList.add('animate-pulse');
			adminBarIcon.classList.add('dashicons-trash');
		}

		fetch(cachifyAdminBarFlushAjaxObject.url, {
			method: 'DELETE',
			headers: { 'X-WP-Nonce': cachifyAdminBarFlushAjaxObject.nonce },
		})
			.then((response) => {
				if (!response.ok) {
					throw new Error(
						`AJAX request failed with status ${response.status}`
					);
				}
				startFlushIconResetTimeout();
				flushIconRemoveClasses();
				adminBarIcon.classList.add('animate-fade');
				adminBarIcon.classList.add(
					cachifyAdminBarFlushAjaxObject.dashiconSuccess
				);
				ariaLiveArea.textContent =
					cachifyAdminBarFlushAjaxObject.flushed;
			})
			.catch(() => {
				window.location = fallbackUrl;
			});
	}
});
