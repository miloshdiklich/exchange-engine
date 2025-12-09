<script setup lang="ts">
</script>

<template>
	<div class="min-h-screen bg-slate-900 text-slate-100">
		<header class="border-b border-slate-800">
			<div class="max-w-4xl mx-auto flex items-center justify-between py-4 px-4">
				<h1 class="text-xl font-semibold">
					Match Exchange Engine
				</h1>
				
				<RouterLink
					v-if="$route.name !== 'login'"
					to="/login"
					class="text-sm text-slate-300 hover:text-white"
				>
					Logout
				</RouterLink>
			</div>
		</header>
		
		<main class="max-w-4xl mx-auto py-8 px-4">
			<RouterView />
		</main>
	</div>
</template>
