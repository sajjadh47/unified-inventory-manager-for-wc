( function () {
	"use strict";
	/**
	 * Manages the user interface and functionality for simple/variable products inventory in WooCommerce.
	 */
	var uimfwcUnifiedInventoryManagerForWc = {
		/**
		 * Initializes the object and sets up the document ready handler.
		 *
		 * It waits for the document to be ready and 
		 * then calls {@link unbindEvents}, {@link bindEvents}.
		 */
		init: function()
		{
			// refer to the parent object
			var self = this;

			window.jQuery( document ).ready( function( $ )
			{
				// unbind all events before binding
				self.unbindEvents( $ );
				
				self.bindEvents( $ );
			} );
		},

		/**
		 * Unbinds all previously bound event handlers.
		 * @param {jQuery} $ - jQuery object.
		 */
		unbindEvents: function( $ )
		{
			$( document ).off( 'click', '#uimfwc_save_bulk' );

			$( document ).off( 'change', '.uimfwc_product_inventory_quantity_bulk' );
		},

		/**
		 * Binds events to DOM elements.
		 *
		 * This method attaches event listeners to various elements on the page to
		 * handle user interactions such as adding, removing, and saving stock.
		 *
		 * @param {jQuery} $ The jQuery object.
		 */
		bindEvents: function( $ )
		{
			// refer to the parent object
			var self = this;

			$( document ).on( 'click', '#uimfwc_save_bulk', function( event )
			{
				self.saveProductsBulk( event, $ );
			} );
			
			$( document ).on( 'change', '.uimfwc_product_inventory_quantity_bulk', function( event )
			{
				self.enableSubmitBtn( event, $ );
			} );
		},

		/**
		 * Changes the state of submit button.
		 *
		 * This method is called when the user types in the quantity
		 * input fields. It changes the disabled attribute state to false
		 * so that user can submit.
		 *
		 * @param {Event}  event The keyup event.
		 * @param {jQuery} $     The jQuery object.
		 */
		enableSubmitBtn: function( event, $ )
		{
			event.preventDefault();
			
			$( '#uimfwc_save_bulk' ).attr( 'disabled', false );
		},

		/**
		 * Asynchronously saves product data via AJAX.
		 * 
		 * @param {jQuery} $ - jQuery object.
		 * @return {Promise} Promise for async operations.
		 */
		saveProductsAsync: function( $ )
		{
			var elements = $( '.uimfwc_product_inventory_quantity_bulk' ).toArray();
			var promises = [];

			elements.forEach( function( el )
			{
				var $el         = $( el );
				var productId   = $el.data( 'product_id' );
				var productData = {};
				var productType = $el.data( 'product_type' );

				if ( productType === 'simple' || productType === 'variable' )
				{
					productData[productId] = { quantity: $el.val() };
				}

				$el.addClass( 'product_saving_loading_image' );

				var promise = $.ajax( {
					url: window.uimfwcUnifiedInventoryManagerForWc.ajaxurl,
					type: 'POST',
					data: {
						action: 'uimfwc_save_product',
						product_id: $el.data( 'product_id' ),
						data: productData,
						uimfwc_nonce: $( '#uimfwc_nonce' ).val()
					}
				} )
				.done( function( response )
				{
					$el.closest( 'tr' ).find( '.stock.column-stock mark span' ).text( response );

					$el.removeClass( 'product_saving_loading_image' );
				} )
				.fail( function( error )
				{
					console.error( error );
				} );

				promises.push( promise );
			} );

			return $.when.apply( $, promises );
		},

		/**
		 * Saves the product data in bulk via an AJAX request.
		 *
		 * @param {Event}  event The click event.
		 * @param {jQuery} $     The jQuery object.
		 */
		saveProductsBulk: function( event, $ )
		{
			event.preventDefault();
			
			$( '.uimfwc_product_inventory_quantity_bulk' ).attr( 'disabled', true );
			
			$( '#uimfwc_save_bulk').attr( 'disabled', true ).val( window.uimfwcUnifiedInventoryManagerForWc.ajaxSavingMsgI18n );
			
			$( '.uimfwc_save_products_submit_container img' ).removeClass( 'uimfwc-d-none' );

			this.saveProductsAsync( $ ).then( function()
			{
				$( '.uimfwc_product_inventory_quantity_bulk' ).attr( 'disabled', false );
				
				$( '#uimfwc_save_bulk' ).val( window.uimfwcUnifiedInventoryManagerForWc.ajaxSaveMsgI18n );
				
				$( '.uimfwc_save_products_submit_container img' ).addClass( 'uimfwc-d-none' );
			} );
		},
	};

	// Initialize the object
	uimfwcUnifiedInventoryManagerForWc.init();
} )();