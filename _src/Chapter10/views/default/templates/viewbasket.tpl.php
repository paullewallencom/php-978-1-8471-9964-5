<form action="basket/update" method="post">
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
<select id="shipping_method" name="shipping_method">
<!-- START shipping_methods -->
<option value="{shipping_method_id}" {shipping_method_selected}>{shipping_method_name}</option>
<!-- END shipping_methods -->
</select>
<input type="submit" id="update" name="update" value="Update basket" />
</form>