<form action="checkout/select-payment-method" method="post">
<select id="payment_method" name="payment_method">
<!-- START payment_methods -->
<option value="{method_id}" {selected}>{method_name}</option>
<!-- END payment_methods -->
</select>
<input type="submit" id="setpayment" name="setpayment" value="Save payment method" />
</form>