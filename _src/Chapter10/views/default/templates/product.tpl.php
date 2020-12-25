<h2>{product_name}</h2>
{product_description}
<p>Cost: &pound;{product_price}, number in stock {product_stock}. Weight: {product_weight}Kg.</p>
<p>
<img src="product_images/{product_image}" alt="{product_name} image" />
</p>
<p><a href="wishlist/add/{product_path}" title="Add {product_name} to your wishlist">Add to wishlist.</a></p>
{stock}
<h2>Related products</h2>
<!-- START relatedproducts -->
<div class="floatingbox">
<a href="products/view/{product_path}">{product_name}</a>
</div>
<!-- END relatedproducts -->