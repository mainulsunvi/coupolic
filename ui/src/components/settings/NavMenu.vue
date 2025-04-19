<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
    <div>
        <div class="inner-container">
            <ul class="nav-items">
                <li class="nav-item nav-active">
                    <RouterLink to="/general-options" className="hello-world">General Options</RouterLink>
                </li>
                <li class="nav-item">Coupon Options</li>
                <li class="nav-item">Generator Options</li>
                <li class="nav-item">Generating Coupons </li>
                <li class="nav-item">Export Options</li>
            </ul>
        </div>
    </div>
</template>