import { createRouter, createWebHashHistory } from 'vue-router'

import GeneralPage from '@/pages/GeneralOptions.vue'
import CouponOptions from '@/pages/CouponOptions.vue'
import GeneratorOptions from '@/pages/GeneratorOptions.vue'
import GeneratingCoupons from '@/pages/GeneratingCoupons.vue'
import ExportOptions from '@/pages/ExportOptions.vue'

import General from '@/pages/GeneralOptions/General.vue'
import UsageRestriction from '@/pages/GeneralOptions/UsageRestriction.vue'
import UsageLimit from '@/pages/GeneralOptions/UsageLimit.vue'

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
      redirect: { path: '/general-options/general' },
      component: GeneralPage,
      children: [
        {
          path: 'general',
          name: 'general-options-general',
          component: General,
        },
        {
          path: 'restriction',
          name: 'general-options-restriction',
          component: UsageRestriction,
        },
        {
          path: 'limit',
          name: 'general-options-limit',
          component: UsageLimit,
        },
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
