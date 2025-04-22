<script setup>

import axios from 'axios';

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
		<input type="text" name="name" placeholder="Enter your name" />
		<input type="email" name="email" placeholder="Enter your email" />
		<select name="discount_type" id="options">
			<option value="percent">Percentage Discount</option>
			<option value="fixed_cart">Fixed Cart Discount</option>
			<option value="fixed_product">Fixed Product Discount</option>
		</select>

		<input type="submit" value="Submit" />
	</form>

	<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Fugiat temporibus explicabo non saepe cumque aliquam
		ipsa labore libero tenetur quaerat, nesciunt ab voluptatum, enim nihil qui rerum iure similique minima sint
		ipsam aspernatur! Recusandae, sunt? Ab quia, odit soluta suscipit fugit quisquam perspiciatis assumenda error,
		vitae similique rem! Dolorem a sed impedit.
	</p>
	<p>
		Hic rem quis earum dolores a dolore odio explicabo enim
		reprehenderit consequatur. Vitae at dolore cupiditate nihil vero perferendis illum fuga quasi, tempore ab, iusto
		optio quidem autem dolores minima? Non debitis tempore eius aspernatur iure voluptate minus libero atque illo.
		Consequuntur, optio aut. Totam veritatis et inventore dolore aliquam ipsam voluptas expedita, porro aliquid
		ratione suscipit, nisi assumenda sit voluptate. Magnam enim, nam fuga perspiciatis impedit natus!
	</p>
</template>