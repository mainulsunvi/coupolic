<script setup>
import Input from '@/components/fields/Input.vue';
</script>

<template>
	<Input type="tel" name="usage_limit" label="Usage Limit per Coupon"
			subtitle="How many times this coupon can be used by an individual user. Uses billing email for guests, and user ID for logged in users."
			placeholder="Unlimited usage" :multiple="true" />
	<Input type="tel" name="usage_limit_per_user" label="Usage Limit per User"
			subtitle="How many times this coupon can be used by an individual user. Uses billing email for guests, and user ID for logged in users."
			placeholder="Unlimited usage" :multiple="true" />
</template>