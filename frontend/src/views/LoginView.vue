<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost'

const handleSubmit = async () => {
	error.value = ''
	loading.value = true
	
	try {
		const response = await axios.post(`${API_BASE_URL}/api/login`, {
			email: email.value,
			password: password.value,
		})
		
		const token =
			(response.data && (response.data.token || response.data.access_token)) ||
			null
		
		if (!token) {
			throw new Error('No token returned from API')
		}
		
		localStorage.setItem('auth_token', token)
		
		await router.push('/')
	} catch (e: any) {
		console.error(e)
		error.value =
			e?.response?.data?.message || 'Invalid credentials or server error.'
	} finally {
		loading.value = false
	}
}
</script>

<template>
	<div class="max-w-md mx-auto bg-slate-800 rounded-xl shadow-lg p-6">
		<h2 class="text-lg font-semibold mb-4">Login</h2>
		
		<form @submit.prevent="handleSubmit" class="space-y-4">
			<div>
				<label class="block text-sm mb-1">Email</label>
				<input
					v-model="email"
					type="email"
					required
					class="w-full rounded-md bg-slate-900 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
				/>
			</div>
			
			<div>
				<label class="block text-sm mb-1">Password</label>
				<input
					v-model="password"
					type="password"
					required
					class="w-full rounded-md bg-slate-900 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
				/>
			</div>
			
			<p v-if="error" class="text-sm text-red-400">
				{{ error }}
			</p>
			
			<button
				type="submit"
				class="w-full rounded-md bg-indigo-500 hover:bg-indigo-600 px-3 py-2 text-sm font-medium disabled:opacity-50"
				:disabled="loading"
			>
				{{ loading ? 'Logging in…' : 'Login' }}
			</button>
		</form>
	</div>
</template>
