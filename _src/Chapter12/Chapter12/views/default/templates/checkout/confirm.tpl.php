<h1>Basket contents</h1>
<table>
	<tr>
		<td>Product</td>
		<td>Quantity</td>
		<td>Cost</td>
		<td>Remove</td>
	</tr>
	<!-- START products -->
	<tr>
		<td>{name}</td>
		<td><input type="text" id="qty_{basket_id}" name="qty_{basket_id}" value="{quantity}" /></td>
		<td><a href="basket/remove-product/{basket_id}">Remove</a></td>
		<td>Cost</td>
	</tr>
	<!-- END products -->
	<tr>
		<td colspan="3">Subtotal</td>
		<td>{basket_subtotal}</td>
	</tr>
	<tr>
		<td colspan="3">Shipping</td>
		<td>{shippingCost}</td>
	</tr>
	<tr>
		<td colspan="3">Total</td>
		<td>{basket_total}</td>
	</tr>
</table>
<h2>Shipping method</h2>
<p>{shipping_method}</p>
<h2>Payment method</h2>
<p>{payment_method}</p>
<h2>Delivery address</h2>
<p>{address_name},{address_lineone},{address_linetwo},{address_city},{address_postcode},{address_country}</p>
<p><a href="checkout/save-order">Confirm order</a></p>