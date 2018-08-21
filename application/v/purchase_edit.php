	<style type="text/css">
	</style>

	<script type="text/javascript">
		$(function() {
			click();
			init();
			reset();
			changeProduct();
			keypressProduct();
		});

		function addProduct() {
			var number = 0;
			$('.purchase-add-product').remove();

			$.each($('.purchase-product-list'), function(key, item) {
				number = $(item).attr('data-number');
			});

			var nextNumber = parseInt(number) + 1;

			var newItemList = '<tr id="purchase-item-list-'+ nextNumber +'" class="purchase-product-list" data-number="'+ nextNumber +'"><td class="td-icon"><span class="table-icon" data-content="Remove Item" onclick="removeItem('+ nextNumber +');"><i class="trash outline icon"></i></span></td><td><div id="purchase-product-selection-'+ nextNumber +'" class="ui search remote selection dropdown form-input"><input id="purchase-product-'+ nextNumber +'" class="purchase-product-list-selection" data-number="'+ nextNumber +'" type="hidden" class="data-important"><i class="dropdown icon"></i><div class="default text">-- Select Product --</div><div class="menu"><? foreach ($arr_product as $product): ?><div class="item" data-value="<?= $product->id; ?>"><?= $product->type; ?> - <?= $product->name; ?></div><? endforeach; ?></div></div></td><td class="td-price-quantity" style="text-align: right;"><input id="purchase-product-price-'+ nextNumber +'" type="text" class="purchase-item-price" data-number="'+ nextNumber +'" placeholder="Price.." style="text-align: right;"></td><td class="td-price-quantity" style="text-align: right;"><input id="purchase-product-quantity-'+ nextNumber +'" type="text" class="purchase-item-quantity" data-number="'+ nextNumber +'" placeholder="Quantity.." style="text-align: right;"></td><td class="td-price-quantity" style="text-align: right;"><span id="purchase-product-price-total-'+ nextNumber +'" data-number="'+ nextNumber +'" data-total="0">Rp 0</span></td></tr><tr><td class="purchase-add-product" style="cursor: pointer;" colspan="5" onclick="addProduct();"><span><i class="plus circle icon"></i></span> Add Product</td></tr>';

			$('#purchase-item-list').append(newItemList);
			$('#purchase-product-price-'+ nextNumber).val("0");
			$('#purchase-product-quantity-'+ nextNumber).val("1");
			$('#purchase-product-selection-'+ nextNumber).dropdown('clear');

			$('.ui.search.remote.selection.dropdown').dropdown({
				apiSettings: {
					url: '<?= base_url(); ?>product/ajax_search/{query}/'
				},
			});

			changeProduct();
			keypressProduct();
		}

		function back() {
			window.location.href = '<?= base_url(); ?>purchase/view/1/';
		}

		function calculateQuantity() {
			var subtotal = 0;

			$.each($('.purchase-product-list'), function(key, item) {
				var number = $(item).attr('data-number');
				var qty = ($('#purchase-product-quantity-'+ number).val() != '') ? $('#purchase-product-quantity-'+ number).val() : 0;
				var price = ($('#purchase-product-price-'+ number).val() != '') ? $('#purchase-product-price-'+ number).val() : 0;

				var total = parseInt(qty) * parseInt(price);
				var totalDisplay = $.number(total, 0, ',', '.');

				$('#purchase-product-price-total-'+ number).html('Rp '+ totalDisplay);
				$('#purchase-product-price-total-'+ number).attr('data-total', total);

				subtotal += parseInt(total);
			});

			calculateTotal(subtotal);
		}

		function calculateTotal(subtotal) {
			var subtotalDisplay = $.number(subtotal, 0, ',', '.');
			$('#purchase-subtotal').attr('data-subtotal', subtotal);
			$('#purchase-subtotal').html('Rp '+ subtotalDisplay);

			var discount = ($('#purchase-discount').val() != '') ? $('#purchase-discount').val() : 0;
			var tax = ($('#purchase-tax').val() != '') ? $('#purchase-tax').val() : 0;
			var shipping = ($('#purchase-shipping').val() != '') ? $('#purchase-shipping').val() : 0;

			var subtotalDiscount = parseInt(discount);
			var subtotalDiscountDisplay = $.number(subtotalDiscount, 0, ',', '.');
			$('#purchase-discount-display').html('Rp '+ subtotalDiscountDisplay);

			var subtotalDiscountTax = (parseInt(tax) / 100) * (subtotal - subtotalDiscount);
			var subtotalDiscountTaxDisplay = $.number(subtotalDiscountTax, 0, ',', '.');
			$('#purchase-tax-display').html('Rp '+ subtotalDiscountTaxDisplay);

			var shippingDisplay = $.number(shipping, 0, ',', '.');
			$('#purchase-shipping-display').html('Rp '+ shippingDisplay);

			var grandTotal = parseInt(subtotal) - parseInt(subtotalDiscount) + parseInt(subtotalDiscountTax) + parseInt(shipping);
			var grandTotalDisplay = $.number(grandTotal, 0, ',', '.');
			$('#purchase-total').html('Rp '+ grandTotalDisplay);
			$('#purchase-total').attr('data-total', grandTotal);
		}

		function changeProduct() {
			$('.purchase-product-list-selection').change(function() {
				var number = $(this).attr('data-number');
				var productId = $(this).val();

				if (productId > 0) {
					$.ajax({
						data :{
							"<?= $csrf['name'] ?>": "<?= $csrf['hash'] ?>"
						},
						dataType: 'JSON',
						error: function() {
							alert('Server Error.');
						},
						success: function(data){
							if (data.status == 'success') {
								$('#purchase-product-price-'+ number).val(data.product.price_display);

								calculateQuantity();
							}
							else {
								alert(data.message);
							}
						},
						type : 'POST',
						url : '<?= base_url() ?>purchase/ajax_get_product/'+ productId +'/',
					});
				}
			});
		}

		function click() {
			$('#form-back').click(function() {
				back();
			});

			$('#form-submit').click(function() {
				submit(0);
			});

			$('#form-submit-draft').click(function() {
				submit(1);
			});

			$('.form-input').click(function() {
				$(this).removeClass('input-error');
			});

			$('.shipping-address-button').click(function() {
				$('.ui.modal.shipping-address-modal').modal({
					inverted: false,
				}).modal('show');
			});
		}

		function init() {
			$('.ui.search.dropdown.form-input').dropdown('clear');

			$('.ui.search.remote.selection.dropdown').dropdown({
				apiSettings: {
					url: '<?= base_url(); ?>product/ajax_search/{query}/'
				},
			});

			$('#purchase-date').datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: 0
            });
		}

		function keypressProduct() {
			$('.purchase-item-price, .purchase-item-quantity, #purchase-discount, #purchase-tax, #purchase-shipping').keyup(function (e) {
				calculateQuantity();
			});
		}

		function removeItem(number) {
			$('#purchase-item-list-'+ number).remove();
			calculateQuantity();
		}

		function reset() {
			$('#purchase-number').val("<?= $purchase->number; ?>");
			$('#purchase-date').val("<?= $purchase->date_display; ?>");
			$('#purchase-discount').val('<?= $purchase->discount_display; ?>');
			$('#purchase-shipping').val('<?= $purchase->shipping_display; ?>');
			$('#purchase-tax').val('<?= $purchase->tax_display; ?>');

			$('#purchase-location').val("<?= $purchase->location_id; ?>");
			$('#purchase-location-selection').dropdown('set selected', "<?= $purchase->location_id; ?>");

			$('#purchase-vendor').val("<?= $purchase->vendor_id; ?>");
			$('#purchase-vendor-selection').dropdown('set selected', "<?= $purchase->vendor_id; ?>");

			$('#purchase-method').val("<?= $purchase->type; ?>");
			$('#purchase-method-selection').dropdown('set selected', "<?= $purchase->type; ?>");

			$('#purchase-statement').val("<?= $purchase->statement_id; ?>");
			$('#purchase-statement-selection').dropdown('set selected', "<?= $purchase->statement_id; ?>");

			<? foreach ($purchase->arr_purchase_item as $key => $purchase_item): ?>
				$('#purchase-product-selection-<?= $key + 1; ?>').dropdown('set selected', "<?= $purchase_item->product_id; ?>");
				$('#purchase-product-price-<?= $key + 1; ?>').val("<?= $purchase_item->price_display; ?>");
				$('#purchase-product-quantity-<?= $key + 1; ?>').val("<?= $purchase_item->quantity_display; ?>");
			<? endforeach; ?>

			calculateQuantity();
		}

		function submit(draft) {
			var purchaseNumber = $('#purchase-number').val();
			var purchaseDate = $('#purchase-date').val();
			var purchaseLocation = $('#purchase-location').val();
			var purchasevendor = $('#purchase-vendor').val();
			var purchaseStatus = $('#purchase-method').val();
			var purchaseStatement = $('#purchase-statement').val();
			var purchaseSubtotal = $('#purchase-subtotal').attr('data-subtotal');
			var purchaseDiscount = $('#purchase-discount').val();
			var purchaseTax = $('#purchase-tax').val();
			var purchaseShipping = $('#purchase-shipping').val();
			var purchaseTotal = $('#purchase-total').attr('data-total');
			var found = 0;

			if (found > 0) {
				return;
			}

			$.each($('.data-important'), function(key, data) {
				if ($(data).val() == '') {
					found += 1;

					$(data).addClass('input-error');
				}
			});

			/* get all purchase product list */
			var arrpurchaseItem = [];
			var purchaseItem = {};

			$.each($('.purchase-product-list'), function(key, item) {
				var number = $(item).attr('data-number');

				if ($('#purchase-product-'+ number).val() > 0 || $('#purchase-product-'+ number).val() != '') {
					purchaseItem = {};
					purchaseItem.vendor_id  = purchasevendor;
					purchaseItem.location_id = purchaseLocation;
					purchaseItem.product_id = $('#purchase-product-'+ number).val();
					purchaseItem.quantity = $('#purchase-product-quantity-'+ number).val();
					purchaseItem.price = $('#purchase-product-price-'+ number).val();

					arrpurchaseItem.push(purchaseItem);
				}
			});

			if (arrpurchaseItem.length <= 0) {
				found += 1;

				$('.ui.dimmer.all-loader').dimmer('hide');
				$('.ui.basic.modal.all-error').modal('show');
				$('.all-error-text').html('Item cannot be empty.');
			}

			if (found > 0) {
				return;
			}

			$('.ui.text.loader').html('Connecting to Database...');
			$('.ui.dimmer.all-loader').dimmer('show');

			$.ajax({
				data :{
					number: purchaseNumber,
					date: purchaseDate,
					location_id: purchaseLocation,
					vendor_id: purchasevendor,
					statement_id: purchaseStatement,
					type: purchaseStatus,
					purchase_item_purchase_item: JSON.stringify(arrpurchaseItem),
					subtotal: purchaseSubtotal,
					discount: purchaseDiscount,
					tax: purchaseTax,
					shipping: purchaseShipping,
					total: purchaseTotal,
					draft: draft,
					"<?= $csrf['name'] ?>": "<?= $csrf['hash'] ?>"
				},
				dataType: 'JSON',
				error: function() {
					$('.ui.dimmer.all-loader').dimmer('hide');
					$('.ui.basic.modal.all-error').modal('show');
					$('.all-error-text').html('Server Error.');
				},
				success: function(data){
					if (data.status == 'success') {
						$('.ui.text.loader').html('Redirecting...');

						back();
					}
					else {
						$('.ui.dimmer.all-loader').dimmer('hide');
						$('.ui.basic.modal.all-error').modal('show');
						$('.all-error-text').html(data.message);
					}
				},
				type : 'POST',
				url : '<?= base_url() ?>purchase/ajax_edit/<?= $purchase->id; ?>',
				xhr: function() {
					var percentage = 0;
					var xhr = new window.XMLHttpRequest();

					xhr.upload.addEventListener('progress', function(evt) {
						$('.ui.text.loader').html('Checking Data..');
					}, false);

					xhr.addEventListener('progress', function(evt) {
						$('.ui.text.loader').html('Updating Database...');
					}, false);

					return xhr;
				},
			});
		}
	</script>

	<!-- Dashboard Here -->
	<div class="main-content">
		<div class="ui stackable one column centered grid">
			<div class="column">
				<div class="ui attached message setting-header">
					<div class="header"><? if ($purchase->draft > 0): ?>[DRAFT]<? endif; ?> Edit purchase</div>
				</div>
				<div class="form-content">
					<div class="ui form">
						<div class="field">
							<div class="three fields">
								<div class="field">
									<label>purchase Number</label>
									<input id="purchase-number" class="form-input" placeholder="AUTO.." type="text">
								</div>
								<div class="field">
									<label>Date</label>
									<input id="purchase-date" class="form-input" placeholder="Date.." type="text">
								</div>
								<div class="field">
									<label>Payment Method</label>
									<div id="purchase-method-selection" class="ui search disabled selection dropdown form-input">
										<input id="purchase-method" type="hidden" class="data-important">
										<i class="dropdown icon"></i>
										<div class="default text">-- Select Status --</div>
										<div class="menu">
											<div class="item" data-value="Cash">Cash</div>
											<div class="item" data-value="Credit">Credit</div>
										</div>
									</div>
								</div>
							</div>

							<div class="three fields">
								<div class="field">
									<label>Location</label>
									<div id="purchase-location-selection" class="ui search selection <? if ($account->location_id > 0): ?>disabled<? endif; ?> dropdown form-input">
										<input id="purchase-location" type="hidden" class="data-important">
										<i class="dropdown icon"></i>
										<div class="default text">-- Select Location --</div>
										<div class="menu">
											<? foreach ($arr_location as $location): ?>
												<div class="item" data-value="<?= $location->id; ?>"><?= $location->name; ?></div>
											<? endforeach; ?>
										</div>
									</div>
								</div>
								<div class="field">
									<label>Vendor</label>
									<div id="purchase-vendor-selection" class="ui search selection dropdown form-input">
										<input id="purchase-vendor" type="hidden" class="data-important">
										<i class="dropdown icon"></i>
										<div class="default text">-- Select vendor --</div>
										<div class="menu">
											<? foreach ($arr_vendor as $vendor): ?>
												<div class="item" data-value="<?= $vendor->id; ?>"><?= $vendor->name; ?></div>
											<? endforeach; ?>
										</div>
									</div>
								</div>
								<div class="field">
									<label>Account</label>
									<div id="purchase-statement-selection" class="ui search selection dropdown form-input">
										<input id="purchase-statement" type="hidden" class="data-important">
										<i class="dropdown icon"></i>
										<div class="default text">-- Select Account --</div>
										<div class="menu">
											<? foreach ($arr_statement as $statement): ?>
												<div class="item" data-value="<?= $statement->id; ?>"><?= $statement->name; ?></div>
											<? endforeach; ?>
										</div>
									</div>
								</div>
							</div>

							<div class="field">
								<table class="ui striped selectable celled table" style="border: 1px solid rgba(34, 36, 38, 0.15); border-radius: 0;">
									<thead>
										<tr>
											<th class="td-icon">Action</th>
											<th>Product</th>
											<th style="text-align: right;">Price</th>
											<th style="text-align: right;">Quantity</th>
											<th style="text-align: right;">Total</th>
										</tr>
									</thead>
									<tbody id="purchase-item-list">
										<? foreach ($purchase->arr_purchase_item as $key => $purchase_item): ?>
											<tr id="purchase-item-list-<?= $key + 1; ?>" class="purchase-product-list" data-number="<?= $key + 1; ?>">
												<td class="td-icon">
													<span class="table-icon" data-content="Remove Item" onclick="removeItem('<?= $key + 1; ?>');">
														<i class="trash outline icon"></i>
													</span>
												</td>
												<td>
													<div id="purchase-product-selection-<?= $key + 1; ?>" class="ui search remote selection dropdown form-input">
														<input id="purchase-product-<?= $key + 1; ?>" class="purchase-product-list-selection" data-number="<?= $key + 1; ?>" type="hidden" class="data-important">
														<i class="dropdown icon"></i>
														<div class="default text">-- Select Product --</div>
														<div class="menu">
															<? foreach ($arr_product as $product): ?>
																<div class="item" data-value="<?= $product->id; ?>"><?= $product->type; ?> - <?= $product->name; ?></div>
															<? endforeach; ?>
														</div>
													</div>
												</td>
												<td class="td-price-quantity" style="text-align: right;">
													<input id="purchase-product-price-<?= $key + 1; ?>" type="text" class="purchase-item-price" data-number="<?= $key + 1; ?>" placeholder="Price.." style="text-align: right;">
												</td>
												<td class="td-price-quantity" style="text-align: right;">
													<input id="purchase-product-quantity-<?= $key + 1; ?>" type="text" class="purchase-item-quantity" data-number="<?= $key + 1; ?>" placeholder="Quantity.." style="text-align: right;">
												</td>
												<td class="td-price-quantity" style="text-align: right;">
													<span id="purchase-product-price-total-<?= $key + 1; ?>" data-number="<?= $key + 1; ?>" data-total="0">Rp 0</span>
												</td>
											</tr>
										<? endforeach; ?>
										<tr>
											<td class="purchase-add-product" style="cursor: pointer;" colspan="5" onclick="addProduct();">
												<span>
													<i class="plus circle icon"></i>
												</span> Add Product
											</td>
										</tr>
									</tbody>
									<tfoot>
										<tr>
											<td colspan="3" style="text-align: right;">Subtotal</td>
											<td colspan="2" id="purchase-subtotal" style="text-align: right;" data-subtotal="0">Rp 0</td>
										</tr>
										<tr>
											<td colspan="3" style="text-align: right;">Discount</td>
											<td style="text-align: right;">
												<input id="purchase-discount" type="text" data-number="1" placeholder="Discount.." style="text-align: right;">
											</td>
											<td id="purchase-discount-display" style="text-align: right;">Rp 0</td>
										</tr>
										<tr>
											<td colspan="3" style="text-align: right;">PPN (%)</td>
											<td style="text-align: right;">
												<input id="purchase-tax" type="text" data-number="1" placeholder="PPN.." style="text-align: right;">
											</td>
											<td id="purchase-tax-display" style="text-align: right;">Rp 0</td>
										</tr>
										<tr>
											<td colspan="3" style="text-align: right;">Shipping</td>
											<td style="text-align: right;">
												<input id="purchase-shipping" type="text" data-number="1" placeholder="Shipping.." style="text-align: right;">
											</td>
											<td id="purchase-shipping-display" style="text-align: right;">Rp 0</td>
										</tr>
										<tr>
											<td colspan="3" style="text-align: right;">Grand Total</td>
											<td colspan="2" id="purchase-total" style="text-align: right;" data-total="0">Rp 0</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="ui bottom attached message text-right setting-header">
					<div class="ui buttons">
						<button id="form-back" class="ui button form-button">Back</button>
						<button id="form-submit-draft" class="ui button form-button">Save as Draft</button>
						<button id="form-submit" class="ui button form-button">Save</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>