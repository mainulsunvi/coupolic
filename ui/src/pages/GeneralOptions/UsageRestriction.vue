<script setup>

import Input from '@/components/fields/Input.vue';
import Select from '@/components/fields/Select.vue';
import DateTime from '@/components/fields/DateTime.vue';
import TextField from '@/components/fields/TextField.vue';
import CardBox from '@/components/global/CardBox.vue';
import CheckBox from '@/components/fields/CheckBox.vue';

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
		<Input type="email" name="customer_email" label="Allowed Emails"
			subtitle="List of allowed billing emails to check against when an order is placed. Separate email addresses with commas. You can also use an asterisk (*) to match parts of an email. For example &quot;*@gmail.com&quot; would match all gmail addresses."
			placeholder="No restrictions" :multiple="true" />

		<Input type="submit" value="submit" label="" />
	</form>
</template>