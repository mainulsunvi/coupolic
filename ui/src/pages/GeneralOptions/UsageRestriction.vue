<script setup>

import Input from '@/components/fields/Input.vue';
import Select from '@/components/fields/Select.vue';
import CheckBox from '@/components/fields/CheckBox.vue';

// Transform products into an options-like object
let products = {};
let categories = {};
let brands = {};

if (typeof coupolic !== 'undefined' && coupolic.categories) {
	const parsedCategories = JSON.parse(coupolic.categories);
	categories = parsedCategories.reduce((acc, category) => {
		acc[category.id] = `${category.title}`;
		return acc;
	}, {});
} else {
	console.warn('coupolic is not defined or categories are missing.');
}

if (typeof coupolic !== 'undefined' && coupolic.brands) {
	const parsedbrands = JSON.parse(coupolic.brands);
	brands = parsedbrands.reduce((acc, brand) => {
		acc[brand.id] = `${brand.title}`;
		return acc;
	}, {});
} else {
	console.warn('coupolic is not defined or categories are missing.');
}

if (typeof coupolic !== 'undefined' && coupolic.products) {
	const parsedProducts = JSON.parse(coupolic.products);
	products = parsedProducts.reduce((acc, product) => {
		acc[product.id] = ` [${product.id}] - ${product.title} - ${product.price}`; // Assuming each product has 'id' and 'name'
		return acc;
	}, {});
} else {
	console.warn('coupolic is not defined or products are missing.');
}

console.log(JSON.parse(coupolic.brands));

document.addEventListener('submit', function (event) {
	event.preventDefault(); // Prevent the default form submission
	const formData = new FormData(event.target); // Get the form data
	const data = Object.fromEntries(formData.entries());

	console.log(data)
})

</script>

<template>

	<form action="" method="post" class="general-form" style="margin-bottom: 20px;">
		<Input type="text" name="minimum_amount" label="Minimum Spend"
			subtitle="This field allows you to set the minimum spend (subtotal) allowed to use the coupon."
			placeholder="No minimum" />
		<Input type="text" name="maximum_amount" label="Maximum Spend"
			subtitle="This field allows you to set the maximum spend (subtotal) allowed to use the coupon."
			placeholder="No maximum" />
		<CheckBox name="individual_use" label="Individual Use Only"
			subtitle="Check this box if the coupon cannot be used in conjunction with other coupons." />
		<CheckBox name="exclude_sale_items" label="Exclude Sale Items"
			subtitle="Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale." />
		
		<hr>

		<Select :options="products" name="product_ids" id="product_ids" label="Products"
			subtitle="List of product IDs that this coupon will apply to. Leave blank to apply to all products." />
		<Select :options="products" name="exclude_product_ids" id="exclude_product_ids" label="Exclude Products"
			subtitle="List of product IDs that this coupon will not apply to. Leave blank to apply to all products." />
		
		<hr>

		<Select :options="categories" name="product_categories" id="category_ids" label="Product Categories"
			subtitle="List of category IDs that this coupon will apply to. Leave blank to apply to all categories." />

		<Select :options="categories" name="exclude_product_categories" id="exclude_category_ids"
			label="Exclude Categories"
			subtitle="List of category IDs that this coupon will not apply to. Leave blank to apply to all categories." />
		
		<hr>

		<Input type="email" name="customer_email" label="Allowed Emails"
			subtitle="List of allowed billing emails to check against when an order is placed. Separate email addresses with commas. You can also use an asterisk (*) to match parts of an email. For example &quot;*@gmail.com&quot; would match all gmail addresses."
			placeholder="No restrictions" :multiple="true" />
		
		<hr>

		<Select :options="brands" name="product_brands" id="product_brands" label="Product Brands"
			subtitle="List of brand IDs that this coupon will apply to. Leave blank to apply to all brands." />

		<Select :options="brands" name="exclude_product_brands" id="exclude_brand_ids" label="Exclude Brands"
			subtitle="List of brand IDs that this coupon will not apply to. Leave blank to apply to all brands." />
		<Input type="submit" value="submit" label="" />
	</form>
</template>