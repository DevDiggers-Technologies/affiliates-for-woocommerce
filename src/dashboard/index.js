/* global ddwcafDashboardData */
"use strict";

import './dashboard.less';
import Chart from 'chart.js/auto';
import { ChartUtils } from '../utils/chart-utils';
import { DateRangeUtils } from '../utils/date-range-utils';

document.addEventListener('DOMContentLoaded', function() {
    new DDWCAF_Dashboard();
});

/**
 * Dashboard Class
 */
class DDWCAF_Dashboard {
    /**
     * Constructor
     */
    constructor() {
        this.init();
    }

    /**
     * Initialize
     */
    init() {
        // Initialize date range toggle
        DateRangeUtils.initDateRangeToggle(
            'ddwcaf-date-range-picker',
            'ddwcaf-date-range-dropdown',
            'ddwcaf-selected-range'
        );

        this.initCharts();
    }

    /**
     * Initialize charts
     */
    initCharts() {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js is not loaded.');
            return;
        }

        this.initPerformanceChart();
        this.initRevenueImpactChart();
        this.initConversionSourcesChart();
    }

    /**
     * Performance Overview Chart
     */
    initPerformanceChart() {
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

    /**
     * Revenue Impact Chart
     */
    initRevenueImpactChart() {
        const canvas = document.getElementById('ddwcaf-revenue-impact-chart');
        if (!canvas || !window.ddwcafDashboardData) return;

        const chartData = window.ddwcafDashboardData.charts.revenue_impact;
        const i18n = window.ddwcafDashboardData.i18n;

        if (!chartData || (!chartData.affiliate.length && !chartData.total.length)) {
            canvas.parentElement.innerHTML = ChartUtils.getEmptyStateHTML(
                'chart',
                i18n.noRevenueImpactData || i18n.noPerformanceData,
                ''
            );
            return;
        }

        const labelsSet = new Set();
        chartData.affiliate.forEach(item => labelsSet.add(item.period));
        chartData.total.forEach(item => labelsSet.add(item.period));
        const sortedLabels = Array.from(labelsSet).sort();

        const affiliateValues = sortedLabels.map(label => {
            const match = chartData.affiliate.find(item => item.period === label);
            return match ? parseFloat(match.value) : 0;
        });

        const totalValues = sortedLabels.map(label => {
            const match = chartData.total.find(item => item.period === label);
            return match ? parseFloat(match.value) : 0;
        });

        const startLabel = sortedLabels[0];
        const endLabel = sortedLabels[sortedLabels.length - 1];
        const displayFormat = ChartUtils.getDateFormat(new Date(startLabel), new Date(endLabel));
        const displayLabels = sortedLabels.map(label => ChartUtils.formatDate(new Date(label), displayFormat));

        const options = ChartUtils.getLineChartOptions(i18n);
        options.plugins.tooltip.callbacks.label = (context) => {
            let label = context.dataset.label || '';
            if (label) {
                label += ': ';
            }
            if (context.parsed.y !== null) {
                label += ddwcafDashboardData.currencySymbol + context.parsed.y.toLocaleString();
            }
            return label;
        };

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: displayLabels,
                datasets: [
                    {
                        label: i18n.affiliateRevenue,
                        data: affiliateValues,
                        borderColor: '#ec4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#ec4899',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: i18n.storeRevenue,
                        data: totalValues,
                        borderColor: '#0256ff',
                        backgroundColor: 'rgba(2, 86, 255, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#0256ff',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: options
        });
    }

    /**
     * Conversion Sources Chart
     */
    initConversionSourcesChart() {
        const canvas = document.getElementById('ddwcaf-conversion-sources-chart');
        if (!canvas || !window.ddwcafDashboardData) return;

        const chartData = window.ddwcafDashboardData.charts.conversion_sources;
        const i18n = window.ddwcafDashboardData.i18n;

        if (!chartData || !chartData.length) {
            canvas.parentElement.innerHTML = ChartUtils.getEmptyStateHTML(
                'donut',
                i18n.noConversionSourcesData || i18n.noPerformanceData,
                ''
            );
            return;
        }

        const labels = chartData.map(item => item.source);
        const values = chartData.map(item => parseInt(item.count));

        // Generate colors from matched brand shades
        const colors = ChartUtils.generateColors(labels.length);

        new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.backgrounds,
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: ChartUtils.getDonutChartOptions(i18n)
        });
    }
}
