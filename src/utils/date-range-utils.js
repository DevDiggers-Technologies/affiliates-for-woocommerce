/**
 * Date Range Utilities for Affiliates
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

/**
 * Date range utility functions
 */
export class DateRangeUtils {
	/**
	 * Initialize date range toggle functionality
	 *
	 * @param {string} pickerId
	 * @param {string} dropdownId
	 */
	static initDateRangeToggle(pickerId = 'ddwcaf-date-range-picker', dropdownId = 'ddwcaf-date-range-dropdown', selectedRangeId = 'ddwcaf-selected-range') {
		const dateRangePicker = document.getElementById(pickerId);
		const dateRangeDropdown = document.getElementById(dropdownId);
		const datePresets = document.querySelectorAll('.ddwcaf-date-preset');
		const applyCustomRange = document.querySelector('.ddwcaf-apply-custom-range');
		const selectedRangeInput = document.getElementById(selectedRangeId);
 
		if (!dateRangePicker || !dateRangeDropdown) return;

		// Toggle dropdown
		dateRangePicker.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			dateRangeDropdown.classList.toggle('show');
		});

		// Close dropdown when clicking outside
		document.addEventListener('click', (e) => {
			if (!dateRangePicker.contains(e.target) && !dateRangeDropdown.contains(e.target)) {
				dateRangeDropdown.classList.remove('show');
			}
		});

		// Handle preset selection
		datePresets.forEach(preset => {
			preset.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const range = preset.getAttribute('data-range');
				selectedRangeInput.value = range;
				dateRangePicker.value = preset.textContent;
				dateRangeDropdown.classList.remove('show');
				dateRangePicker.closest('form').submit();
			});
		});

		// Handle custom range application
		if (applyCustomRange) {
			applyCustomRange.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				selectedRangeInput.value = 'custom';
				dateRangeDropdown.classList.remove('show');
				dateRangePicker.closest('form').submit();
			});
		}
	}
}
