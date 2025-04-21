<script setup>
import NavMenu from "@/components/settings/NavMenu.vue";


</script>

<template>
    <header class="coupolic-settings-header">
        <div class="coupolic-header-upper-section">
            <span class="app-title">Coupolic</span>
            <span class="app-version">v1.0.0</span>
        </div>
        <div>
        <div class="inner-container">
            <ul class="nav-items">
                <li class="nav-item" exact :class="{ 'nav-active': $route.path === '/general-options' }">
                    <RouterLink to="/general-options" className="hello-world">General Options</RouterLink>
                </li>
                <li class="nav-item" exact :class="{ 'nav-active': $route.path === '/coupon-options' }">
                    <RouterLink to="/coupon-options" className="hello-world">Coupon Options</RouterLink>
                </li>
                <li class="nav-item" exact :class="{ 'nav-active': $route.path === '/generator-options' }">
                    <RouterLink to="/generator-options" className="hello-world">Generator Options</RouterLink>
                </li>
                <li class="nav-item" exact :class="{ 'nav-active': $route.path === '/generating-coupons' }">
                    <RouterLink to="/generating-coupons" className="hello-world">Generating Coupons</RouterLink>
                </li>
                <li class="nav-item" exact :class="{ 'nav-active': $route.path === '/export-options' }">
                    <RouterLink to="/export-options" className="hello-world">Export Options</RouterLink>
                </li>
            </ul>
        </div>
    </div>
    </header>
</template>