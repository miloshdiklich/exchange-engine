<script setup lang="ts">
import { RouterView, useRoute } from 'vue-router'
import { useAuth } from './composables/useAuth'

const route = useRoute()
const { logout } = useAuth()
</script>

<template>
	<div class="min-h-screen bg-slate-900 text-slate-200">
		<!-- Top bar -->
		<header class="border-b border-slate-800">
			<div class="max-w-5xl mx-auto flex items-center justify-between py-4 px-4">
				<h1 class="text-xl font-semibold tracking-tight">
					Match Exchange Engine
				</h1>
				
				<button
					v-if="route.name !== 'login'"
					@click="logout"
					class="text-sm px-3 py-1 rounded-md bg-slate-800 hover:bg-slate-700
                 border border-slate-600"
				>
					Logout
				</button>
			</div>
		</header>
		
		<!-- Page content -->
		<main class="max-w-5xl mx-auto py-8 px-4">
			<RouterView />
		</main>
	</div>
</template>
