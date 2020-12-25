<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<base href="http://localhost/book4/chapter10/">
	<title></title> 
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" /> 
	<meta name="description" content="{metadescription}" /> 
	<meta name="keywords" content="{metakeywords}" /> 
	<link rel="stylesheet" type="text/css" href="views/default/default.css" />
</head> 
<body>
<div id="wrapper">
<div id="search">
<form action="products/search" method="post">
<label for="product_search">Search for a product</label><input type="text" id="product_search" name="product_search" />
<input type="submit" id="search" name="search" value="Search" />
</form>
</div>
{basket}