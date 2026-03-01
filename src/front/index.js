"use strict";
import './front.less';

document.addEventListener( 'DOMContentLoaded', () => {
    const handleCopyTrigger = () => {
        const copyTriggerElements = document.querySelectorAll( '.ddwcaf-copy-trigger' );
        if ( copyTriggerElements.length ) {
            const changeCopyTooltip = (element, text) => {
                if ( text ) {
                    element.innerHTML = text;
                } else {
                    element.innerHTML = ddwcafFrontObject.i18n.copied;
                }
            }

            copyTriggerElements.forEach( copyTriggerElement => {
                copyTriggerElement.addEventListener( 'click', e => {
                    e.preventDefault();

                    const copyFieldContainer = e.target.closest( '.ddwcaf-copy-field-container' );
                    const copyText           = copyFieldContainer.querySelector( '.ddwcaf-copy-target' ).getAttribute( 'data-copy-text' );

                    navigator.clipboard.writeText(copyText);

                    changeCopyTooltip( copyFieldContainer.querySelector( '.ddwcaf-copy-tooltip' ) );
                } );

                copyTriggerElement.addEventListener( 'mouseover', e => {
                    e.preventDefault();

                    const currentCopyTriggerElement = e.target.closest( '.ddwcaf-copy-trigger' );

                    changeCopyTooltip( currentCopyTriggerElement.querySelector( '.ddwcaf-copy-tooltip' ), currentCopyTriggerElement.getAttribute( 'data-tooltip' ) );
                } );
            } );
        }
    }

    handleCopyTrigger();

    let timeout = '';

    const ddwcafGetCustomReferralURL = e => {
        if ( timeout ) {
            clearTimeout( timeout );
        }

        if ( ! e.target.value ) {
            return;
        }

        const containerElement = e.target.closest( '.ddwcaf-details-container' );

        timeout = setTimeout( () => {
            let rowElement = containerElement.querySelector( '.ddwcaf-generated-link-row' );
            if ( rowElement ) {
                rowElement.classList.add( 'loading' );
            }
            containerElement.style.opacity = .5;

            fetch( ddwcafFrontObject.ajax.ajaxUrl, {
                method : 'POST',
                headers: new Headers( {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept'      : 'application/json',
                } ),
                body: `action=ddwcaf_get_custom_referral_html&nonce=${ddwcafFrontObject.ajax.ajaxNonce}&custom_page_url=${encodeURIComponent(e.target.value)}`
            })
            .then( response => response.json() )
            .then( response => {
                containerElement.style.opacity = 1;

                if ( response.success ) {
                    let rowElement = containerElement.querySelector( '.ddwcaf-generated-link-row' );
                    if ( rowElement ) {
                        rowElement.innerHTML = response.html;
                        rowElement.classList.remove( 'loading' );
                    }
                    handleCopyTrigger();
                }
            } )
            .catch( err => {
                containerElement.style.opacity = 1;
                console.log( err );
            } );
        }, 500 );
    }

    const handleCustomPageReferralURLGeneration = () => {
        const customPageURLElement = document.querySelector( '.ddwcaf-custom-page-url' );

        if ( customPageURLElement ) {

            customPageURLElement.addEventListener( 'keyup', e => ddwcafGetCustomReferralURL(e) );
            customPageURLElement.addEventListener( 'change', e => ddwcafGetCustomReferralURL(e) );
        }
    }

    handleCustomPageReferralURLGeneration();

    const charts = document.querySelectorAll( '.ddwcaf-analytic-chart' );

    if ( charts.length ) {
        const getReportChartData = async () => {
            var reportsData = {
                commissions: {},
                visits: {},
            };

            let p = [];
            let i = 0;

            for (const endpoint in reportsData) {
                p[i] = new Promise((resolve, reject) => {
                    AjAxGetRevenueReport(endpoint).then(response => {
                        if (response) {
                            reportsData[endpoint] = response;
                        }
                        resolve(reportsData);
                    });
                });
                ++i;
            }

            return await Promise.all(p).then(function (values) {
                return reportsData;
            }); 
        }

        const AjAxGetRevenueReport = async endpoint => {
            const MAX_PER_PAGE = 100;

            const fetchArgs = {
                parse: false,
            };

            const month = document.querySelector( '.ddwcaf-month' ).value;
            const year  = document.querySelector( '.ddwcaf-year' ).value;

            let lastDay = new Date( year, month, 0 );
            lastDay = lastDay.getDate();

            var url = new URL(`${ddwcafFrontObject.SITE_URL}/wp-json/ddwcaf/v1/reports/${ endpoint }/stats`);
            url.searchParams.append( 'order', 'asc' );
            url.searchParams.append( 'interval', 'day' );
            url.searchParams.append( 'per_page', MAX_PER_PAGE );
            url.searchParams.append( 'affiliate_id', ddwcafFrontObject.affiliate_id );
            url.searchParams.append( 'after', `${year}-${month}-01T00:00:00` );
            url.searchParams.append( 'before', `${year}-${month}-${lastDay}T23:59:59` );

            fetchArgs.path = url;

            try {
                const response = await fetch(fetchArgs.path);

                const stats = await response.json();
                const totalResults = parseInt(response.headers.get('x-wp-total')); 

                if (stats) {
                    const totals = ( stats && stats.totals ) || null;
                    let intervals = ( stats && stats.intervals ) || []; 

                    // If we have more than 100 results for this time period,
                    // we need to make additional requests to complete the response.
                    if ( totalResults > MAX_PER_PAGE ) {
                        let isFetching = true;
                        const pagedData = [];
                        const totalPages = Math.ceil( totalResults / MAX_PER_PAGE );
                        let pagesFetched = 1;

                        for ( let i = 2; i <= totalPages; i++ ) {
                            url.searchParams.set( 'page', i );

                            const _data = await ddwcafGetReportStats( url ).then( res => {
                                return res;
                            } );

                            pagedData.push( _data );
                            pagesFetched++;

                            if ( pagesFetched === totalPages ) {
                                isFetching = false;
                                break;
                            }
                        }

                        if ( isFetching ) {
                            return { ...response };
                        }

                        pagedData.forEach(  _data => {
                            intervals = intervals.concat( _data.intervals );
                        } );
                    }

                    return { ...response, ...{ totals, intervals } };
                }
            } catch (error) {
                return error;
            } 
        }

        const ddwcafGetReportStats = async url => {
            const fetchArgs = {
                parse: false,
            };

            fetchArgs.path = url;

            try {
                const response = await fetch(fetchArgs.path);
                const stats = await response.json();

                return stats;
            } catch (error) {
                return error;
            }
        }

        const prepareChartData = () => {
            const loaderElements = document.querySelectorAll( '.ddwcaf-loader' );
            getReportChartData().then ( reportsData => {
                loaderElements.forEach( loaderElement => {
                    loaderElement.classList.add( 'ddwcaf-hide' );
                } );

                const month = document.querySelector( '.ddwcaf-month' ).value;
                const year  = document.querySelector( '.ddwcaf-year' ).value;
                let lastDay = new Date( year, month, 0 );
                lastDay = lastDay.getDate();

                let intervals = [];
                for ( let i = 1; i <= lastDay; i++ ) {
                    intervals.push(i);
                }

                charts.forEach( chart => {
                    const report   = chart.getAttribute( 'data-report' );
                    const endpoint = chart.getAttribute( 'data-endpoint' );
                    const title    = chart.getAttribute( 'data-title' );
                    const data     = reportsData[endpoint].intervals.map( arr => arr.subtotals[report] );

                    const totalValue = reportsData[endpoint].totals[report];

                    if ( endpoint === 'commissions' ) {
                        chart.closest( '.ddwcaf-chart-wrapper' ).querySelector( '.ddwcaf-total-amount' ).innerHTML = formatPrice(totalValue);
                    } else if ( report === 'conversion_rate' ) {
                        chart.closest( '.ddwcaf-chart-wrapper' ).querySelector( '.ddwcaf-total-amount' ).innerHTML = totalValue + '%';
                    } else {
                        chart.closest( '.ddwcaf-chart-wrapper' ).querySelector( '.ddwcaf-total-amount' ).innerHTML = totalValue;
                    }


                    new Chart( chart, {
                        type: 'line',
                        data: {
                            labels: intervals,
                            datasets: [{
                                label: title,
                                data: data,
                                lineTension: 0.3,
                                backgroundColor: "rgba(78, 115, 223, 0.05)",
                                borderColor: ddwcafFrontObject.primaryColor,
                                pointRadius: 2,
                                pointBackgroundColor: ddwcafFrontObject.primaryColor,
                                pointBorderColor: ddwcafFrontObject.primaryColor,
                                pointHoverRadius: 2,
                                pointHoverBackgroundColor: ddwcafFrontObject.primaryColor,
                                pointHoverBorderColor: ddwcafFrontObject.primaryColor,
                                pointHitRadius: 10,
                                pointBorderWidth: 2,
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                xAxes: [{
                                    time: {
                                        unit: 'date'
                                    },
                                    gridLines: {
                                        display: false,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        maxTicksLimit: 10,
                                        padding: 10
                                    }
                                }],
                                yAxes: [{
                                    ticks: {
                                        maxTicksLimit: 5,
                                        padding: 10,
                                        beginAtZero: true,
                                        callback: value => {
                                            if ( endpoint === 'commissions' ) {
                                                return formatPrice(value);
                                            } else if ( report === 'conversion_rate' ) {
                                                return value + '%';
                                            }
                                            return value;
                                        }
                                    },
                                    gridLines: {
                                        color: "rgb(234, 236, 244)",
                                        zeroLineColor: "rgb(234, 236, 244)",
                                        drawBorder: false,
                                        borderDash: [2],
                                        zeroLineBorderDash: [2]
                                    }
                                }],
                            },
                            legend: {
                                display: false
                            },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                titleMarginBottom: 10,
                                titleFontColor: ddwcafFrontObject.primaryColor,
                                titleFontSize: 14,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10,
                                callbacks: {
                                    title: (tooltipItem, data) => {
                                        let dateString = tooltipItem[0].label.toString();

                                        if ( dateString <= 9 ) {
                                            dateString = `0${dateString}`;
                                        }
                                        return `${year}-${month}-${dateString}`;
                                    },
                                    label: (tooltipItem, data) => {
                                        let label = data.datasets[tooltipItem.datasetIndex].label;

                                        if ( endpoint === 'commissions' ) {
                                            label += `: ${formatPrice(tooltipItem.yLabel)}`;
                                        } else if ( report === 'conversion_rate' ) {
                                            label += ` ${tooltipItem.yLabel}%`;
                                        } else {
                                            label += ` ${tooltipItem.yLabel}`;
                                        }

                                        return label;
                                    },
                                },
                            }
                        }
                    });
                } );
            } ).catch( error => {
                console.log(error)
            } );
        }

        prepareChartData();
    }

    const paginationButtonsElements = document.querySelectorAll( '.ddwcaf-pagination-button' );

    if ( paginationButtonsElements.length ) {
        paginationButtonsElements.forEach( paginationButtonsElement => {
            paginationButtonsElement.addEventListener( 'click', e => {
                e.preventDefault();
                const tableContainer     = e.target.closest( '.ddwcaf-table-container' );
                const table              = e.target.getAttribute( 'data-table' );
                const perform            = e.target.getAttribute( 'data-perform' );
                const currentPageElement = tableContainer.querySelector( 'input.ddwcaf-current-page' );
                const totalCount         = tableContainer.querySelector( 'input.ddwcaf-total-count' ).value;
                const perPage            = 10;
                let   currentPage        = parseInt( currentPageElement.value );

                if ( 'next' === perform ) {
                    ++currentPage;
                } else if ( 'previous' === perform ) {
                    --currentPage;
                }

                currentPageElement.value = currentPage;

                const loaderOverlay = tableContainer.querySelector( '.ddwcaf-table-loader-overlay' );
                loaderOverlay.style.display = 'flex';

                e.target.disabled = true;

                fetch( ddwcafFrontObject.ajax.ajaxUrl, {
                    method: "post",
                    headers: new Headers( {
                        "Content-Type": "application/x-www-form-urlencoded",
                        "Accept": "application/json"
                    } ),
                    body: `action=ddwcaf_get_table_rows&nonce=${ddwcafFrontObject.ajax.ajaxNonce}&table=${table}&perform=${perform}&current_page=${currentPage}`,
                } )
                .then( response => response.json() )
                .then( response => {
                    loaderOverlay.style.display = 'none';
                    e.target.disabled = false;

                    if ( response.success && response.html ) {
                        tableContainer.querySelector( 'table tbody' ).innerHTML = response.html;

                        tableContainer.querySelector( '.woocommerce-Button--next' ).disabled = true;
                        tableContainer.querySelector( '.woocommerce-Button--previous' ).disabled = true;

                        if ( Math.ceil( totalCount / perPage ) > currentPage ) {
                            tableContainer.querySelector( '.woocommerce-Button--next' ).disabled = false;
                        }

                        if ( 1 !== currentPage && currentPage > 1 ) {
                            tableContainer.querySelector( '.woocommerce-Button--previous' ).disabled = false;
                        }
                    }
                } ).catch(error => {
                    console.error( error );
                    loaderOverlay.style.display = 'none';
                    e.target.disabled = false;
                });
            });
        } );
    }
} );

function ddwcafOnSocialShareClick( href ) {
	var windowWidth 	= '640',
		windowHeight 	= '480',
		windowTop 		= screen.height / 2 - windowHeight / 2,
		windowLeft 		= screen.width / 2 - windowWidth / 2,
		shareWindow 	= 'toolbar=0,status=0,width=' + windowWidth + ',height=' + windowHeight + ',top=' + windowTop + ',left=' + windowLeft;

	open( href, '', shareWindow );
}