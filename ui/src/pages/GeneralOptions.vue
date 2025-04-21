<script setup>
import CardBox from '@/components/global/CardBox.vue';
import { RouterLink, RouterView } from 'vue-router'
import Icon from '@/components/global/LucidIcon.vue'
// import { CircleAlert } from 'lucide-vue-next'
</script>

<template>
	<div class="inner-box">
		<CardBox :width=16 :height=40>
			<div class="coupolic-general-sub-nav-menus">
				<div class="sub-nav-menu nav-active">
					<Icon name="Settings" :size=16 :stroke-width=1.5 />
					<RouterLink to="#">General</RouterLink>
				</div>
				<div class="sub-nav-menu">
					<Icon name="OctagonAlert" :size=16 :stroke-width=1.5 />
					<RouterLink to="#">Usage Restriction</RouterLink>
				</div>
				<div class="sub-nav-menu">
					<Icon name="Hourglass" :size=16 :stroke-width=1.5 />
					<RouterLink to="#">Usage Limit</RouterLink>
				</div>
			</div>
		</CardBox>
		<CardBox :width=80>
			<!-- <RouterView /> -->
			Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid, iusto? Possimus labore animi eligendi
			tenetur, aperiam aspernatur. Ullam perspiciatis cum eligendi esse temporibus cumque aut vero saepe,
			repudiandae officiis, perferendis, consequatur dolores inventore eveniet? Nemo, veniam. Possimus, quas
			nostrum explicabo veritatis necessitatibus ipsum id minus neque repellat modi reiciendis pariatur sapiente
			iusto porro libero facilis aperiam, debitis ab quibusdam reprehenderit quam nam! Labore exercitationem
			commodi alias rerum quidem ex ea vero? Quos ea harum doloremque aspernatur deleniti praesentium, iure
			repellendus. Exercitationem reiciendis officiis dolores mollitia tempora quod atque at iusto nesciunt,
			doloremque pariatur rerum repudiandae nostrum! Sit excepturi sequi qui ab cum itaque? At, distinctio
			repudiandae, provident tempora assumenda sint, soluta maiores voluptates suscipit aspernatur itaque
			obcaecati placeat! Labore, assumenda!
		</CardBox>
	</div>

</template>