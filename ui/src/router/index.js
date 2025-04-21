import { createRouter, createWebHashHistory } from 'vue-router'

import GeneralPage from '@/pages/GeneralOptions.vue'
import CouponOptions from '@/pages/CouponOptions.vue'
import GeneratorOptions from '@/pages/GeneratorOptions.vue'
import GeneratingCoupons from '@/pages/GeneratingCoupons.vue'
import ExportOptions from '@/pages/ExportOptions.vue'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      redirect: { path: '/general-options' },
      component: GeneralPage,
    },
    {
      path: '/general-options',
      name: 'general-options',
      component: GeneralPage,
      children: [
        {
          path: 'general',
        }
      ]
    },
    {
      path: '/coupon-options',
      name: 'coupon-options',
      component: CouponOptions,
    },    
    {
      path: '/generator-options',
      name: 'generator-options',
      component: GeneratorOptions,
    },    
    {
      path: '/generating-coupons',
      name: 'generating-coupons',
      component: GeneratingCoupons,
    },
    {
      path: '/export-options',
      name: 'export-options',
      component: ExportOptions,
    },
  ],
})

export default router
