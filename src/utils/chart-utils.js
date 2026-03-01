/**
 * Chart Utilities for Affiliates (Chart.js v4 Compatible)
 *
 * @package Affiliates for WooCommerce
 * @version 1.1.0
 */

/**
 * Chart utility functions
 */
export class ChartUtils {
	/**
	 * Format date for chart display
	 *
	 * @param {Date} date
	 * @param {string} format
	 * @returns {string}
	 */
	static formatDate(date, format) {
		if (!date || !format) return date;
		
		if (format === 'MMM YYYY') {
			return new Intl.DateTimeFormat('en-US', {
				month: 'short',
				year: 'numeric'
			}).format(date);
		}
		
		if (format === 'QQQ YYYY') {
			const quarter = Math.ceil((date.getMonth() + 1) / 3);
			const year = date.getFullYear();
			return `Q${quarter} ${year}`;
		}
		
		// Default format: M j (e.g., "Jan 15")
		return new Intl.DateTimeFormat('en-US', {
			month: 'short',
			day: 'numeric'
		}).format(date);
	}

	/**
	 * Determine appropriate date format based on date range
	 *
	 * @param {Date} fromDate
	 * @param {Date} toDate
	 * @returns {string}
	 */
	static getDateFormat(fromDate, toDate) {
		const daysDiff = Math.ceil((toDate - fromDate) / (1000 * 60 * 60 * 24));
		
		if (daysDiff > 365) {
			return 'QQQ YYYY';
		} else if (daysDiff > 60) {
			return 'MMM YYYY';
		}

		return 'M j';
	}

	/**
	 * Get empty state HTML with SVG illustration
	 *
	 * @param {string} type
	 * @param {string} title
	 * @param {string} description
	 * @returns {string}
	 */
	static getEmptyStateHTML(type, title, description) {
		const svg = type === 'chart' ? this.getChartEmptySVG() : this.getDonutEmptySVG();
		
		return `
			<div class="ddwcaf-empty-state">
				<div class="ddwcaf-empty-illustration">
					${svg}
				</div>
				<div class="ddwcaf-empty-content">
					<h4 class="ddwcaf-empty-title">${title}</h4>
					<p class="ddwcaf-empty-description">${description}</p>
				</div>
			</div>
		`;
	}

	/**
	 * Get chart empty state SVG
	 *
	 * @returns {string}
	 */
	static getChartEmptySVG() {
		return `
			<svg width="120" height="80" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="chartGradient" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" style="stop-color:#e3f2fd;stop-opacity:1" />
						<stop offset="100%" style="stop-color:#f3e5f5;stop-opacity:1" />
					</linearGradient>
				</defs>
				<rect width="120" height="80" fill="url(#chartGradient)" rx="8"/>
				<g opacity="0.6">
					<path d="M20 60 L30 50 L40 45 L50 40 L60 35 L70 30 L80 25 L90 20 L100 15" stroke="#0256ff" stroke-width="2" fill="none" stroke-dasharray="4,4"/>
					<circle cx="20" cy="60" r="3" fill="#0256ff"/>
					<circle cx="30" cy="50" r="3" fill="#0256ff"/>
					<circle cx="40" cy="45" r="3" fill="#0256ff"/>
					<circle cx="50" cy="40" r="3" fill="#0256ff"/>
					<circle cx="60" cy="35" r="3" fill="#0256ff"/>
					<circle cx="70" cy="30" r="3" fill="#0256ff"/>
					<circle cx="80" cy="25" r="3" fill="#0256ff"/>
					<circle cx="90" cy="20" r="3" fill="#0256ff"/>
					<circle cx="100" cy="15" r="3" fill="#0256ff"/>
				</g>
				<g opacity="0.4">
					<path d="M20 65 L30 55 L40 50 L50 45 L60 40 L70 35 L80 30 L90 25 L100 20" stroke="#d1d5db" stroke-width="2" fill="none" stroke-dasharray="4,4"/>
				</g>
			</svg>
		`;
	}

	/**
	 * Get donut chart empty state SVG
	 *
	 * @returns {string}
	 */
	static getDonutEmptySVG() {
		return `
			<svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
				<defs>
					<linearGradient id="donutGradient" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" style="stop-color:#f8fafc;stop-opacity:1" />
						<stop offset="100%" style="stop-color:#e2e8f0;stop-opacity:1" />
					</linearGradient>
				</defs>
				<circle cx="60" cy="60" r="50" fill="url(#donutGradient)" stroke="#e2e8f0" stroke-width="2"/>
				<circle cx="60" cy="60" r="30" fill="white" stroke="#e2e8f0" stroke-width="2"/>
				<g opacity="0.6">
					<circle cx="60" cy="60" r="40" stroke="#0256ff" stroke-width="3" fill="none" stroke-dasharray="8,8"/>
					<circle cx="60" cy="60" r="40" stroke="#d1d5db" stroke-width="2" fill="none" stroke-dasharray="4,4" stroke-dashoffset="4"/>
				</g>
				<g opacity="0.4">
					<path d="M45 45 L55 55 M65 45 L75 55 M45 75 L55 65 M65 75 L75 65" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/>
				</g>
			</svg>
		`;
	}

	/**
	 * Generate chart colors using brand color shades only (Matched with LoyaltyX)
	 *
	 * @param {number} count
	 * @returns {object}
	 */
	static generateColors(count) {
		// Brand color shades - different shades of blue (Matched with LoyaltyX)
		const brandShades = [
			'#0256ff', // Primary blue
			'#3b82f6', // Lighter blue
			'#60a5fa', // Even lighter blue
			'#93c5fd', // Light blue
			'#dbeafe', // Very light blue
			'#1e40af', // Darker blue
			'#1d4ed8', // Dark blue
			'#2563eb', // Medium blue
			'#1e3a8a', // Very dark blue
			'#312e81'  // Darkest blue
		];

		const backgrounds = [];
		const borders = [];

		for (let i = 0; i < count; i++) {
			const color = brandShades[i % brandShades.length];
			backgrounds.push(color);
			borders.push('#f2f2f2'); // Consistent light border
		}

		return { backgrounds, borders };
	}

	/**
	 * Get common chart options for line charts (v4 syntax)
	 *
	 * @param {object} i18n
	 * @returns {object}
	 */
	static getLineChartOptions(i18n = {}) {
		return {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					position: 'top',
					labels: {
						usePointStyle: true,
						pointStyle: 'circle',
						boxWidth: 6,
						boxHeight: 6,
						padding: 20
					}
				},
				tooltip: {
					mode: 'index',
					intersect: false,
					backgroundColor: '#ffffff',
					titleColor: '#111827',
					bodyColor: '#374151',
					borderColor: '#e5e7eb',
					borderWidth: 1,
					cornerRadius: 15,
					displayColors: true,
					usePointStyle: true,
					boxWidth: 8,
					boxHeight: 8,
					boxPadding: 4,
					padding: 15,
					titleFont: {
						size: 15,
						weight: '600'
					},
					bodyFont: {
						size: 14,
						weight: '400'
					},
					callbacks: {
						label: function(context) {
							let label = context.dataset.label || '';
							if (label) {
								label += ': ';
							}
							if (context.parsed.y !== null) {
								label += context.parsed.y.toLocaleString();
							}
							return label;
						}
					}
				}
			},
			scales: {
				x: {
					display: true,
					grid: {
						display: false
					}
				},
				y: {
					display: true,
					beginAtZero: true,
					border: {
						display: true,
						dash: [4, 2]
					},
					grid: {
						color: '#f3f4f6'
					},
					ticks: {
						callback: function(value) {
							return value.toLocaleString();
						}
					}
				}
			},
			interaction: {
				mode: 'nearest',
				axis: 'x',
				intersect: false
			}
		};
	}

	/**
	 * Get common chart options for donut charts (v4 syntax)
	 *
	 * @param {object} i18n
	 * @returns {object}
	 */
	static getDonutChartOptions(i18n = {}) {
		return {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					position: 'right',
					labels: {
						usePointStyle: true,
						pointStyle: 'circle',
						boxWidth: 6,
						boxHeight: 6,
						padding: 20
					}
				},
				tooltip: {
					backgroundColor: '#ffffff',
					titleColor: '#111827',
					bodyColor: '#374151',
					borderColor: '#e5e7eb',
					borderWidth: 1,
					cornerRadius: 15,
					displayColors: true,
					usePointStyle: true,
					boxWidth: 8,
					boxHeight: 8,
					boxPadding: 4,
					padding: 15,
					titleFont: {
						size: 15,
						weight: '600'
					},
					bodyFont: {
						size: 14,
						weight: '400'
					},
					callbacks: {
						label: function(context) {
							const dataset = context.dataset;
							const total = dataset.data.reduce((a, b) => a + b, 0);
							const currentValue = dataset.data[context.dataIndex];
							const percentage = ((currentValue / total) * 100).toFixed(1);
							const label = context.label || '';
							return `${label}: ${currentValue.toLocaleString()} (${percentage}%)`;
						}
					}
				}
			},
			cutout: '60%'
		};
	}
}
