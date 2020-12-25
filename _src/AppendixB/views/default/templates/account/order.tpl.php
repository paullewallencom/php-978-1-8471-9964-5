<p>Order placed: {date_placed}</p>
<p>Order status: {status}</p>
<table>
<tr>
<th>Product</th><th>Quantity</th><th>Subtotal</th>
</tr>
<!-- START items -->
<tr>
<td>{name}</td><td>{qty}</td><td>${cost}</td>
</tr>
<!-- END items -->
</table>
<p>Order cost: ${pc}</p>
<p>Order shipping cost: ${sc}</p>
<p>Total order cost: ${toc}</p>
<p><a href="useraccount/confirm-cancel-order/{order}">Cancel this order</a></p>