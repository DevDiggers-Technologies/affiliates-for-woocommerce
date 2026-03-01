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
            callback: (value) => ddwcafDashboardData.currencySymbol + value.toLocaleString()
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
                label += ddwcafDashboardData.currencySymbol + context.parsed.y.toLocaleString();
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
