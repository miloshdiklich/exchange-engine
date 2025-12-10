<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import createEcho from "../echo.ts";

const router = useRouter()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost'

// ---- Types ----
type AssetBalance = {
	symbol: string
	amount: string
}

type OrderStatus = number | string

type Order = {
	id: number
	symbol: string
	side: string
	price: string
	amount: string
	status: OrderStatus
}

// ---- State ----
const userId = ref<number | null>(null)
const channelName = ref<string | null>(null)
const echoInstance = ref<ReturnType<typeof createEcho> | null>(null)

const usdBalance = ref<string>('0.00')
const assetBalances = ref<AssetBalance[]>([])
const orders = ref<Order[]>([])

const symbol = ref<'BTC' | 'ETH'>('BTC')
const side = ref<'buy' | 'sell'>('buy')
const price = ref<string>('')
const amount = ref<string>('')

const loading = ref(false)
const error = ref('')
const message = ref('')
const cancellingId = ref<number | null>(null)

// ---- Helpers ----
const authHeaders = () => {
	const token = localStorage.getItem('auth_token')
	if (!token) {
		router.push('/login')
		return {}
	}
	
	return { Authorization: `Bearer ${token}` }
}

const volumePreview = computed(() => {
	const p = Number(price.value)
	const a = Number(amount.value)
	if (!p || !a) return null
	return (p * a).toFixed(2)
})

const formatStatus = (status: OrderStatus) => {
	if (typeof status === 'number') {
		if (status === 1) return 'OPEN'
		if (status === 2) return 'FILLED'
		if (status === 3) return 'CANCELLED'
	}
	return String(status).toUpperCase()
}

const isOpen = (order: Order) => {
	if (typeof order.status === 'number') {
		return order.status === 1
	}
	return String(order.status).toLowerCase() === 'open'
}

// ---- API calls ----
const fetchProfile = async () => {
	try {
		const { data } = await axios.get(`${API_BASE_URL}/api/profile`, {
			headers: authHeaders(),
		})
		
		usdBalance.value = data.balance ?? '0.00'
		assetBalances.value = data.assets ?? []
	} catch (e) {
		console.error('Error fetching profile', e)
	}
}

const fetchOrders = async () => {
	try {
		const { data } = await axios.get(`${API_BASE_URL}/api/orders`, {
			headers: authHeaders(),
			params: { symbol: symbol.value },
		})
		
		orders.value = (data.my_orders ?? []) as Order[]
	} catch (e) {
		console.error('Error fetching orders', e)
	}
}

const placeOrder = async () => {
	error.value = ''
	message.value = ''
	loading.value = true
	
	try {
		await axios.post(
			`${API_BASE_URL}/api/orders`,
			{
				symbol: symbol.value,
				side: side.value,
				price: Number(price.value),
				amount: Number(amount.value),
			},
			{
				headers: authHeaders(),
			},
		)
		
		message.value = 'Order placed successfully.'
		price.value = ''
		amount.value = ''
		
		await Promise.all([fetchProfile(), fetchOrders()])
	} catch (e: any) {
		console.error(e)
		error.value = e?.response?.data?.message ?? 'Failed to place order.'
	} finally {
		loading.value = false
	}
}

const cancelOrder = async (order: Order) => {
	if (!isOpen(order)) return
	
	error.value = ''
	message.value = ''
	cancellingId.value = order.id
	
	try {
		await axios.post(
			`${API_BASE_URL}/api/orders/${order.id}/cancel`,
			{},
			{ headers: authHeaders() },
		)
		
		message.value = 'Order cancelled.'
		await Promise.all([fetchProfile(), fetchOrders()])
	} catch (e: any) {
		console.error(e)
		error.value =
			e?.response?.data?.message ?? 'Failed to cancel order.'
	} finally {
		cancellingId.value = null
	}
}

// ---- Realtime setup ----
const setupRealtime = async () => {
	const token = localStorage.getItem('auth_token')
	if (!token) {
		console.warn('[Realtime] No token, aborting realtime setup')
		return
	}
	
	console.log('[Realtime] Starting setupRealtime…')
	
	// Get current user id from /api/me
	try {
		console.log('[Realtime] Fetching /api/me …')
		const meResponse = await axios.get(`${API_BASE_URL}/api/me`, {
			headers: authHeaders(),
		})
		
		console.log('[Realtime] /api/me raw response:', meResponse.data)
		
		const payload = meResponse.data
		
		const extractedId = payload.user?.id
		
		userId.value = extractedId ?? null
		
		console.log('[Realtime] /api/me OK, user id =', userId.value)
	} catch (e) {
		console.error('[Realtime] Error fetching /api/me', e)
		return
	}
	
	if (!userId.value) {
		console.warn('[Realtime] No userId, aborting realtime setup')
		return
	}
	
	// Create Echo instance with this user's token
	echoInstance.value = createEcho(token)
	
	const name = `user.${userId.value}`
	channelName.value = name
	
	console.log('[Realtime] Subscribing to channel:', name)
	
	const channel = echoInstance.value.private(name)
	
	// Fires when subscription succeeds
	// @ts-ignore
	channel.subscribed(() => {
		console.log('[Realtime] SUBSCRIBED to', name)
	})
	
	// Fires when subscription fails
	// @ts-ignore
	channel.error((err: any) => {
		console.error('[Realtime] Channel error on', name, err)
	})
	
	// Event listener
	channel.listen('.OrderMatched', async (payload: any) => {
		console.log('[Realtime] OrderMatched EVENT RECEIVED:', payload)
		await Promise.all([fetchProfile(), fetchOrders()])
	})
}

// ---- Lifecycle ----
onMounted(async () => {
	const token = localStorage.getItem('auth_token')
	if (!token) {
		await router.push('/login')
		return
	}
	
	await fetchProfile()
	await fetchOrders()
	await setupRealtime()
})

onBeforeUnmount(() => {
	if (echoInstance.value && channelName.value) {
		echoInstance.value.leave(channelName.value) // leave "user.{id}"
	}
})
</script>

<template>
	<div class="space-y-6">
		<!-- Wallet -->
		<section class="bg-slate-800 rounded-xl p-4 shadow-md">
			<h2 class="text-sm font-semibold mb-3">Wallet Overview</h2>
			<div class="flex items-center justify-between gap-4">
				<div>
					<p class="text-xs text-slate-400">USD Balance</p>
					<p class="text-lg font-semibold">${{ usdBalance }}</p>
				</div>
				<div class="flex-1">
					<p class="text-xs text-slate-400 mb-1">Assets</p>
					<div class="flex flex-wrap gap-2 text-sm">
            <span
	            v-if="assetBalances.length === 0"
	            class="text-xs text-slate-500"
            >
              No assets
            </span>
				<span
					v-for="asset in assetBalances"
					:key="asset.symbol"
					class="inline-flex items-center gap-1 bg-slate-900 px-2 py-1 rounded-md"
				>
              <span class="font-mono text-xs">{{ asset.symbol }}</span>
              <span class="text-slate-300 text-xs">{{ asset.amount }}</span>
            </span>
					</div>
				</div>
			</div>
		</section>
		
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<!-- Limit Order Form -->
			<section class="bg-slate-800 rounded-xl p-4 shadow-md">
				<h2 class="text-sm font-semibold mb-3">Place Limit Order</h2>
				
				<form @submit.prevent="placeOrder" class="space-y-3">
					<div class="grid grid-cols-2 gap-3">
						<div>
							<label class="block text-xs mb-1">Symbol</label>
							<select
								v-model="symbol"
								class="w-full bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
							>
								<option value="BTC">BTC</option>
								<option value="ETH">ETH</option>
							</select>
						</div>
						
						<div>
							<label class="block text-xs mb-1">Side</label>
							<select
								v-model="side"
								class="w-full bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
							>
								<option value="buy">Buy</option>
								<option value="sell">Sell</option>
							</select>
						</div>
					</div>
					
					<div class="grid grid-cols-2 gap-3">
						<div>
							<label class="block text-xs mb-1">Price (USD)</label>
							<input
								v-model="price"
								type="number"
								step="0.01"
								min="0"
								required
								class="w-full bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
							/>
						</div>
						
						<div>
							<label class="block text-xs mb-1">Amount</label>
							<input
								v-model="amount"
								type="number"
								step="0.00000001"
								min="0"
								required
								class="w-full bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
							/>
						</div>
					</div>
					
					<p class="text-xs text-slate-400">
						Volume:
						<span v-if="volumePreview !== null">
              {{ volumePreview }} USD
            </span>
						<span v-else>–</span>
					</p>
					
					<p v-if="error" class="text-xs text-red-400">
						{{ error }}
					</p>
					
					<p v-if="message" class="text-xs text-emerald-400">
						{{ message }}
					</p>
					
					<button
						type="submit"
						class="w-full rounded-md bg-indigo-500 hover:bg-indigo-600 px-3 py-2 text-sm font-medium disabled:opacity-50"
						:disabled="loading"
					>
						{{ loading ? 'Submitting…' : 'Place Order' }}
					</button>
				</form>
			</section>
			
			<!-- My Orders -->
			<section class="bg-slate-800 rounded-xl p-4 shadow-md">
				<h2 class="text-sm font-semibold mb-3">
					My Orders ({{ symbol }})
				</h2>
				
				<div v-if="orders.length === 0" class="text-xs text-slate-400">
					No orders yet.
				</div>
				
				<ul v-else class="space-y-2 max-h-80 overflow-y-auto text-xs">
					<li
						v-for="order in orders"
						:key="order.id"
						class="flex items-center justify-between bg-slate-900 rounded-md px-2 py-1.5"
					>
						<div>
							<p class="font-mono">
								{{ order.side.toUpperCase() }}
								{{ order.amount }}
								{{ order.symbol }}
								@ {{ order.price }}
							</p>
							<p class="text-[11px] text-slate-400">
								Status: {{ formatStatus(order.status) }}
							</p>
						</div>
						
						<button
							v-if="isOpen(order)"
							class="text-[11px] px-2 py-1 rounded-md border border-red-500 text-red-300 hover:bg-red-500/10 disabled:opacity-50"
							:disabled="cancellingId === order.id"
							@click="cancelOrder(order)"
						>
							{{ cancellingId === order.id ? 'Cancelling…' : 'Cancel' }}
						</button>
					</li>
				</ul>
			</section>
		</div>
	</div>
</template>
