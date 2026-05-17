<script setup>
import { RouterLink } from 'vue-router'
import { Icon } from '@iconify/vue'

const navItems = [
    { path: '/settings', label: 'Settings', icon: 'proicons:settings' },
    { path: '/coupon-wizard', label: 'Coupon Wizard', icon: 'proicons:rocket' },
    { path: '/logs', label: 'Logs & History', icon: 'proicons:history' },
]

</script>

<template>
    <header class="app-header">
        <nav class="main-nav">
            <div class="nav-brand">
                <span class="brand-name">Coupolic</span>
                <span class="brand-version">v{{ coupolic.version }}</span>
            </div>

            <ul class="nav-menu">
                <li
                    v-for="item in navItems"
                    :key="item.path"
                    class="nav-item"
                >
                    <RouterLink
                        :to="item.path"
                        class="nav-link"
                        :class="{ 'nav-active': $route.path === item.path }"
                    >
                        <Icon :icon="item.icon" width="18" height="18" class="nav-icon" />
                        <span class="nav-text">{{ item.label }}</span>
                    </RouterLink>
                </li>
            </ul>
        </nav>
    </header>
</template>

<style scoped>
.app-header {
    margin-bottom: 16px;

}

.main-nav {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
}

.nav-brand {
    display: flex;
    align-items: end;
    gap: 0.75rem;
}

.brand-icon {
    color: var(--accent-blue);
    flex-shrink: 0;
}

.brand-name {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.02em;
    text-transform: uppercase;
    line-height: 1;
}

.brand-version {
    font-size: 11px;
    line-height: 11px;
    color: #000;
    font-weight: 700;
    border: 1px solid var(--coupolic-palette-950);
    border-radius: 2px;
    background-color: var(--coupolic-palette-50);
    display: inline-block;
    margin-bottom: 0.25rem;
    padding: 2px 4px;
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: var(--radius-md);
    transition: all var(--transition-base);
    position: relative;
}

.nav-link::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    width: calc(100% - 1rem);
    height: 2px;
    background: var(--accent-blue);
    transition: transform var(--transition-base);
    border-radius: 1px;
}

.nav-link:hover {
    color: var(--text-primary);
    background: var(--bg-secondary);
}

.nav-link:hover::before {
    transform: translateX(-50%) scaleX(1);
}

.nav-link.nav-active {
    color: var(--accent-blue);
    background: var(--info-bg);
}

.nav-link.nav-active::before {
    transform: translateX(-50%) scaleX(1);
}

.nav-icon {
    flex-shrink: 0;
}

.nav-text {
    white-space: nowrap;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .main-nav {
        padding: 0 1rem;
        height: 56px;
    }

    .brand-name {
        font-size: 1.125rem;
    }

    .brand-version {
        display: none;
    }

    .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    .nav-link::before {
        width: calc(100% - 0.75rem);
    }

    .nav-text {
        display: none;
    }

    .nav-icon {
        width: 20px;
        height: 20px;
    }
}

/* Tablet */
@media (min-width: 769px) and (max-width: 1024px) {
    .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    .nav-text {
        font-size: 0.8125rem;
    }
}
</style>