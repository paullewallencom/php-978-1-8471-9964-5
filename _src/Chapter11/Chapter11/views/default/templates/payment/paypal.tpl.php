<h1>Pay for your order</h1>
<form action="https://www{payment.testmode}.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="custom" value="{reference}">
<input type="hidden" name="business" value="{payment.paypal.email}">
<input type="hidden" name="item_name" value="{sitename} Purchase">
<input type="hidden" name="item_number" value="{siteshortname}-{reference}">
<input type="hidden" name="amount" value="{cost}">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="{payment.currency}">
<input type="hidden" name="src" value="1">
<input type="hidden" name="notify_url" value="{siteurl}payment/process-payment/{reference}">
<input type="hidden" name="return" value="{siteurl}payment/payment-received">
<input type="hidden" name="cancel_return" value="{siteurl}payment/payment-cancelled">
<input type="hidden" name="lc" value="GB">
<input type="hidden" name="bn" value="PP-BuyNowBF">
<input type="image" class="paypal-button" src="https://www.paypal.com/en_US/i/btn/x-click-but6.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" >
<img alt="" border="0" src="https://www{paymen.testmode}.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>