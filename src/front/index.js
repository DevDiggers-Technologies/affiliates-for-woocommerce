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
                    containerElement.innerHTML = response.html;
                    handleCopyTrigger();
                    handleCustomPageReferralURLGeneration();
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
} );

function ddwcafOnSocialShareClick( href ) {
	var windowWidth 	= '640',
		windowHeight 	= '480',
		windowTop 		= screen.height / 2 - windowHeight / 2,
		windowLeft 		= screen.width / 2 - windowWidth / 2,
		shareWindow 	= 'toolbar=0,status=0,width=' + windowWidth + ',height=' + windowHeight + ',top=' + windowTop + ',left=' + windowLeft;

	open( href, '', shareWindow );
}