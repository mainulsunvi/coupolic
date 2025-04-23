<script setup>
import CardBox from '../global/CardBox.vue';

defineProps({
	placeholder: {
		type: String,
		default: 'Enter the Text'
	},
	name: {
		type: String,
		default: 'text-field'
	},
	id: {
		type: String,
		default: 'text-field'
	},
	disabled: {
		type: Boolean,
		default: false
	},
	readonly: {
		type: Boolean,
		default: false
	},
	label: {
		type: String,
		default: 'Enter Text'
	},
	value: {
		type: [String, Number],
		default: null
	},
	subtitle: {
		type: String,
		default: ''
	},
	height: {
		type: Number,
		default: 100
	}

})

</script>
<template>
	<div class="coupolic-input-container">
		<label v-if="label" :for="name" class="coupolic-input-label">{{ label }}</label>
		<CardBox :width=75>
			<textarea :placeholder="placeholder" :name="name" :id="id" :disabled="disabled"
				:readonly="readonly" :value=value class="coupolic-input-field" :style="{ height: height + 'px' }" />
			<p v-if="subtitle" class="coupolic-input-subtitle">{{ subtitle }}</p>
		</CardBox>
	</div>
</template>