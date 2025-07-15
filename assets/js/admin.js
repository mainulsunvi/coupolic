/**
 * Coupolic Admin JavaScript
 * Version: 1.0.0
 */

(function ($) {
	'use strict';

	$(document).ready(function () {
		var $form = $('#coupolic-generator-form');
		var $generateBtn = $('#generate-coupons');
		var $message = $('#coupolic-message');
		var $results = $('#coupolic-results');
		var $couponList = $('#coupolic-coupon-list');
		var generatedCoupons = [];

		// Handle form submission
		$form.on('submit', function (e) {
			e.preventDefault();
			generateCoupons();
		});

		// Generate coupons
		function generateCoupons() {
			var formData = $form.serialize();
			formData += '&action=coupolic_generate_coupons';
			formData += '&nonce=' + coupolic_ajax.nonce;

			// Disable button and show loading
			$generateBtn.prop('disabled', true).html(
				coupolic_ajax.messages.generating + ' <span class="coupolic-spinner"></span>'
			);

			// Hide previous messages
			$message.hide().removeClass('notice-success notice-error notice-info');
			$results.hide();

			$.ajax({
				url: coupolic_ajax.ajax_url,
				type: 'POST',
				data: formData,
				success: function (response) {
					if (response.success) {
						showMessage(coupolic_ajax.messages.success, 'success');
						displayResults(response.data);
						generatedCoupons = response.data.coupons;
					} else {
						showMessage(response.data || coupolic_ajax.messages.error, 'error');
					}
				},
				error: function () {
					showMessage(coupolic_ajax.messages.error, 'error');
				},
				complete: function () {
					$generateBtn.prop('disabled', false).html('Generate Coupons');
				}
			});
		}

		// Display results
		function displayResults(data) {
			var html = '';

			if (data.coupons && data.coupons.length > 0) {
				data.coupons.forEach(function (coupon) {
					html += '<div class="coupolic-coupon-item">';
					html += '<span class="coupolic-coupon-code">' + escapeHtml(coupon.code) + '</span>';
					html += '<span class="coupolic-coupon-actions">';
					html += '<a href="' + coupon.link + '" target="_blank">Edit</a>';
					html += '</span>';
					html += '</div>';
				});

				$couponList.html(html);
				$results.slideDown();
			}
		}

		// Show message
		function showMessage(text, type) {
			$message.html(escapeHtml(text))
				.addClass('notice-' + type)
				.slideDown();
		}

		// Export to CSV
		$('#export-coupons').on('click', function () {
			if (generatedCoupons.length === 0) {
				return;
			}

			var csv = 'Coupon Code,Edit Link\n';
			generatedCoupons.forEach(function (coupon) {
				csv += '"' + coupon.code + '","' + coupon.link + '"\n';
			});

			// Create download link
			var blob = new Blob([csv], { type: 'text/csv' });
			var url = URL.createObjectURL(blob);
			var a = document.createElement('a');
			a.href = url;
			a.download = 'coupons-' + new Date().getTime() + '.csv';
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			URL.revokeObjectURL(url);
		});

		// Escape HTML
		function escapeHtml(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function (m) { return map[m]; });
		}
	});

})(jQuery);