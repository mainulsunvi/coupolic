<script setup>
import CardBox from '../global/CardBox.vue';


defineProps({
    name: {
        type: String,
        default: "checkbox-field"
    },
    label: {
        type: String,
        default: "Checkbox Label"
    },
    subtitle: {
        type: String,
        default: "The Subtitle of checkbox"
    }
})

</script>
<template>
    <div class="coupolic-input-container">
        <label :for=name class="coupolic-input-label">{{ label }}</label>
        <CardBox :width=75>
            <input :name=name type="checkbox" class="coupolic-input-checkbox" />
            <span class="coupolic-input-subtitle">{{ subtitle }}</span>
        </CardBox>
    </div>
</template>