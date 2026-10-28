"use strict";

import './manage-affiliate.less';
import { ChartUtils } from '../utils/chart-utils';
import { DateRangeUtils } from '../utils/date-range-utils';
import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Date Filter
    // DateRangeUtils.init();

    DateRangeUtils.initDateRangeToggle(
            'ddwcaf-date-range-picker',
            'ddwcaf-date-range-dropdown',
            'ddwcaf-selected-range'
        );

    // Initialize Performance Chart
    initPerformanceChart();
});

/**
 * Format an amount the way the store does ( symbol, position, decimals, separators ).
 *
 * @param {number} value    Amount.
 * @param {number} decimals Decimals to show; the store's own when not given.
 * @return {string} Formatted amount.
 */
function formatMoney(value, decimals) {
    const currency = window.ddwcafDashboardData.currency;
    const [whole, fraction] = Math.abs(value).toFixed(undefined === decimals ? currency.decimals : decimals).split('.');
    const amount = (value < 0 ? '-' : '') + whole.replace(/\B(?=(\d{3})+(?!\d))/g, currency.thousandSeparator) + (fraction ? currency.decimalSeparator + fraction : '');

    return {
        right: amount + currency.symbol,
        left_space: currency.symbol + '\u00a0' + amount,
        right_space: amount + '\u00a0' + currency.symbol,
    }[currency.position] || currency.symbol + amount;
}

/**
 * Performance Overview Chart
 */
function initPerformanceChart() {
    const canvas = document.getElementById('ddwcaf-performance-chart');
    if (!canvas || !window.ddwcafDashboardData) return;

    const chartData = window.ddwcafDashboardData.charts.performance;
    const i18n = window.ddwcafDashboardData.i18n;

    if (!chartData || (!chartData.earnings.length && !chartData.visits.length)) {
        canvas.parentElement.innerHTML = ChartUtils.getEmptyStateHTML(
            'chart',
            i18n.noPerformanceData,
            i18n.noPerformanceDataDesc || ''
        );
        return;
    }

    // Combine labels
    const labelsSet = new Set();
    chartData.earnings.forEach(item => labelsSet.add(item.period));
    chartData.visits.forEach(item => labelsSet.add(item.period));
    const sortedLabels = Array.from(labelsSet).sort();
    
    const earningsValues = sortedLabels.map(label => {
        const match = chartData.earnings.find(item => item.period === label);
        return match ? parseFloat(match.value) : 0;
    });

    const visitsValues = sortedLabels.map(label => {
        const match = chartData.visits.find(item => item.period === label);
        return match ? parseInt(match.value) : 0;
    });

    const startLabel = sortedLabels[0];
    const endLabel = sortedLabels[sortedLabels.length - 1];
    const displayFormat = ChartUtils.getDateFormat(new Date(startLabel), new Date(endLabel));
    const displayLabels = sortedLabels.map(label => ChartUtils.formatDate(new Date(label), displayFormat));

    const ctx = canvas.getContext('2d');
    const options = ChartUtils.getLineChartOptions(i18n);

    // v4 axis configuration
    options.scales = options.scales || {};
    options.scales.y = {
        type: 'linear',
        display: true,
        position: 'left',
        beginAtZero: true,
        ticks: {
            callback: (value) => formatMoney(value, Number.isInteger(value) ? 0 : undefined)
        }
    };

    options.scales.y1 = {
        type: 'linear',
        display: true,
        position: 'right',
        beginAtZero: true,
        grid: {
            drawOnChartArea: false
        }
    };

    options.plugins = options.plugins || {};
    options.plugins.tooltip = options.plugins.tooltip || {};
    options.plugins.tooltip.callbacks = options.plugins.tooltip.callbacks || {};
    options.plugins.tooltip.callbacks.label = (context) => {
        let label = context.dataset.label || '';
        if (label) {
            label += ': ';
        }
        if (context.parsed.y !== null) {
            if (context.dataset.yAxisID === 'y') {
                label += formatMoney(context.parsed.y);
            } else {
                label += context.parsed.y.toLocaleString();
            }
        }
        return label;
    };

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: displayLabels,
            datasets: [
                {
                    label: i18n.earnings,
                    data: earningsValues,
                    borderColor: '#0256ff',
                    backgroundColor: 'rgba(2, 86, 255, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointBackgroundColor: '#0256ff',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: i18n.visits,
                    data: visitsValues,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'y1'
                }
            ]
        },
        options: options
    });
}
