"use strict";
import './admin.less';

var ddwcaf = jQuery.noConflict();

document.addEventListener( 'DOMContentLoaded', () => {
    const ddwcafProductsSelect2 = () => {
        if ( ddwcaf( '.ddwcaf-products' ).length ) {
            ddwcaf( '.ddwcaf-products' ).select2( {
                ajax: {
                    method  : 'post',
                    url     : ddwcafAdminObject.ajax.ajaxUrl,   // AJAX URL is predefined in WordPress admin
                    dataType: 'json',
                    delay   : 250,                           // delay in ms while typing when to perform a AJAX search
                    data    : params => {
                        return {
                            query : params.term,                      // search query
                            action: 'ddwcaf_get_products_list',
                            nonce : ddwcafAdminObject.ajax.ajaxNonce,
                        };
                    },
                    processResults: products => {
                        let options = [];
                        if (products != undefined && products != null) {
                            // products is the array of objects, and each of them contains value and the Label of the option
                            products.forEach(product => {
                                options.push({
                                    id  : product.ID,
                                    text: product.title
                                });
                            });
                        }
                        return { results: options };
                    },
                    cache: true
                },
                multiple          : ddwcaf(this).attr( 'multiple' ),
                minimumInputLength: 1, // the minimum of symbols to input before perform a search
                language          : {
                    inputTooShort: args => `${ddwcafAdminObject.i18n.pleaseEnter} ${args.minimum - args.input.length} ${ddwcafAdminObject.i18n.moreCharacter}`,
                    noResults: () => ddwcafAdminObject.i18n.noResult,
                }
            } );
        }
    }

    const ddwcafCategoriesSelect2 = () => {
        if ( ddwcaf( '.ddwcaf-categories' ).length ) {
            ddwcaf( '.ddwcaf-categories' ).select2( {
                ajax: {
                    method  : 'post',
                    url     : ddwcafAdminObject.ajax.ajaxUrl,   // AJAX URL is predefined in WordPress admin
                    dataType: 'json',
                    delay   : 250,                           // delay in ms while typing when to perform a AJAX search
                    data    : params => {
                        return {
                            query : params.term,                      // search query
                            action: 'ddwcaf_get_categories_list',
                            nonce : ddwcafAdminObject.ajax.ajaxNonce,
                        };
                    },
                    processResults: response => {
                        let options = [];
                        let cats    = response.categories;

                        if ( cats != undefined && cats != null) {
                            cats.forEach( category => {
                                options.push({
                                    id  : category.term_id,
                                    text: category.name
                                });
                            } );
                        }

                        return { results: options };
                    },
                    cache: true
                },
                multiple          : ddwcaf(this).attr( 'multiple' ),
                minimumInputLength: 1, // the minimum of symbols to input before perform a search
                language          : {
                    inputTooShort: args => `${ddwcafAdminObject.i18n.pleaseEnter} ${args.minimum - args.input.length} ${ddwcafAdminObject.i18n.moreCharacter}`,
                    noResults: () => ddwcafAdminObject.i18n.noResult,
                }
            });
        }
    }

    const ddwcafSelect2 = () => {
        if ( ddwcaf( '.ddwcaf-select2' ).length ) {
            ddwcaf( '.ddwcaf-select2' ).select2();
        }
    }

    ddwcafProductsSelect2();
    ddwcafCategoriesSelect2();
    ddwcafSelect2();

    if ( ddwcaf( '.ddwcaf-affiliate' ).length ) {
        ddwcaf( '.ddwcaf-affiliate' ).select2( {
            ajax: {
                method  : 'post',
                url     : ddwcafAdminObject.ajax.ajaxUrl,   // AJAX URL is predefined in WordPress admin
                dataType: 'json',
                delay   : 250,                           // delay in ms while typing when to perform a AJAX search
                data    : params => {
                    return {
                        query : params.term,                      // search query
                        action: 'ddwcaf_get_affiliates_list',
                        nonce : ddwcafAdminObject.ajax.ajaxNonce,
                    };
                },
                processResults: response => {
                    let options = [];
                    if ( response.success && response.users != undefined && response.users != null && response.users.length ) {
                        // users is the array of objects, and each of them contains value and the Label of the option
                        response.users.forEach( user => {
                            options.push( {
                                id  : user.ID,
                                text: `(#${user.ID}) ${user.user_login} <${user.user_email}>`
                            } );
                        } );
                    }
                    return {
                        results: options
                    };
                },
                cache: true
            },
            width             : ddwcaf( '#from-date' ).length ? '25%': '50%',
            multiple          : ddwcaf(this).attr( 'multiple' ),
            minimumInputLength: 1,                                                               // the minimum of symbols to input before perform a search
            language          : {
                inputTooShort: args => `${ddwcafAdminObject.i18n.pleaseEnter} ${args.minimum - args.input.length} ${ddwcafAdminObject.i18n.moreCharacter}`,
                noResults: () => ddwcafAdminObject.i18n.noResult,
            }
        } );
    }

    const fieldTypeElement = ddwcaf( '#ddwcaf-type' );

    if ( fieldTypeElement.length ) {
        const showFieldOptions = fieldType => {
            if ( 'select' === fieldType || 'radio' === fieldType ) {
                document.querySelector( '#ddwcaf-options' ).closest( 'tr' ).style.display = 'table-row';
            } else {
                document.querySelector( '#ddwcaf-options' ).closest( 'tr' ).style.display = 'none';
            }
        }

        showFieldOptions( fieldTypeElement.val() );

        fieldTypeElement.on( 'change', e => {
            showFieldOptions( e.target.value );
        } );
    }

    if ( document.querySelectorAll( '.ddwcaf-editor' ).length ) {
		tinymce.init( {
			selector:  '.ddwcaf-editor',
			height: 160,
			menubar: false,
			plugins: [ 'textcolor', 'colorpicker', 'link', 'lists', 'hr', 'media', 'charmap', 'image' ],
			toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter | alignright alignjustify | bullist numlist outdent indent | hr |  styleselect | link forecolor | charmap removeformat | image',
			content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
			urlconverter_callback: (url, node, on_save, name) => url
		} );  
	}

    const withdrawalTypeElement = ddwcaf( '#ddwcaf-withdrawal-type' );

    if ( withdrawalTypeElement.length ) {
        const showWithdrawalTypeOptions = value => {
            if ( 'automatically_on_day' === value ) {
                document.querySelector( '#ddwcaf-withdrawal-day' ).closest( 'tr' ).style.display = 'table-row';
            } else {
                document.querySelector( '#ddwcaf-withdrawal-day' ).closest( 'tr' ).style.display = 'none';
            }
        }

        showWithdrawalTypeOptions( withdrawalTypeElement.val() );

        withdrawalTypeElement.on( 'change', e => {
            showWithdrawalTypeOptions( e.target.value );
        } );
    }
} );