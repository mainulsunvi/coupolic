<script setup>

import Input from '@/components/fields/Input.vue';
import Select from '@/components/fields/Select.vue';
import DateTime from '@/components/fields/DateTime.vue';
import TextField from '@/components/fields/TextField.vue';
// import DateTime from '@/components/fields/TextArea.vue';

let options = {
	percent: 'Percentage Discount',
	fixed_cart: 'Fixed Cart Discount',
	fixed_product: 'Fixed Product Discount'
}

// async function fetchData() {
// 	const response = await axios.get('/wp-json/wc/v3/products');
// 	const data = response.data;
// 	console.log(data);
// }
// fetchData(); // Call the function to fetch data

document.addEventListener('submit', function (event) {
	event.preventDefault(); // Prevent the default form submission
	const formData = new FormData(event.target); // Get the form data
	const data = Object.fromEntries(formData.entries());

	console.log(data)
})

</script>

<template>

	<form action="" method="post" class="general-form" style="margin-bottom: 20px;">
		<Select :options=options name="discount_type" id="discount_type" label="Discount Type"
			subtitle="The Discount Type of the Coupon ( i.e.: Percentage Discount )" />
		<Input type="tel" name="coupon_amount" id="coupon_amount" label="Coupon Amount"
			subtitle="The Value of the Coupon that will be Deducted from main Price" :value=0 />
		<DateTime name="expiry_date" label="Coupon Expiry Date"
			subtitle="The Coupon will Expire at 00:00:00 of this Date" />
		<TextField :height=150 placeholder="Enter description ..." label="Description (Optional)" subtitle="This Description will show across all of your generated coupon" name="excerpt"/>
		<input type="submit" value="Submit" />
	</form>
</template>