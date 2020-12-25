<h1>Manage your account</h1>
<form action="useraccount/update-account" method="post">
<input type="text" id="default_shipping_name" name="default_shipping_name" value="{default_shipping_name}" /><br />
<input type="text" id="default_shipping_address" name="default_shipping_address" value="{default_shipping_address}" /><br />
<input type="text" id="default_shipping_address2" name="default_shipping_address2" value="{default_shipping_address2}" /><br />
<input type="text" id="default_shipping_city" name="default_shipping_city" value="{default_shipping_city}" /><br />
<input type="text" id="default_shipping_postcode" name="default_shipping_postcode" value="{default_shipping_postcode}" /><br />
<input type="text" id="default_shipping_country" name="default_shipping_country" value="{default_shipping_country}" /><br />
<input type="text" id="email" name="email" value="{email}" /><br />
<input type="submit" id="save" name="save" value="Save" />
</form>
<h2>Your orders</h2>
<table>
	<tr>
		<th>ID</th><th>Order placed</th><th>Order status</th><th>Order total</th>
	</tr>
	<!-- START orders -->
	<tr>
		<td><a href="useraccount/view-order/{order_id}">{order_id}</a></td><td>{order_placed}</td><td>{status_name}</td><td>${cost}</td>
	</tr>
	<!-- END orders -->
</table>
<h2>Change your password</h2>
<p><a href="useraccount/change-password">Change your password.</a></p>
