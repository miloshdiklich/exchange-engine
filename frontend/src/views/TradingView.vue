<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import WalletOverview from '../components/trading/WalletOverview.vue'
import OrderForm from '../components/trading/OrderForm.vue'
import OrdersTable from '../components/trading/OrdersTable.vue'
import { fetchProfile, fetchOrders, cancelOrder as cancelOrderApi } from '../utils/api'
import { useRealtime } from '../composables/useRealtime'
import type { AssetBalance, Order } from '../types/trading'

const router = useRouter()

const usdBalance = ref<string>('0.00')
const assetBalances = ref<AssetBalance[]>([])
const orders = ref<Order[]>([])

const symbol = ref<'BTC' | 'ETH'>('BTC')
const cancellingId = ref<number | null>(null)

const loadData = async () => {
	await Promise.all([loadProfile(), loadOrders()])
}

const loadProfile = async () => {
	try {
		const profile = await fetchProfile()
		usdBalance.value = profile.balance
		assetBalances.value = profile.assets
	} catch (e) {
		console.error('Error fetching profile', e)
	}
}

const loadOrders = async () => {
	try {
		orders.value = await fetchOrders(symbol.value)
	} catch (e) {
		console.error('Error fetching orders', e)
	}
}

const handleOrderPlaced = async () => {
	await loadData()
}

const handleCancelOrder = async (order: Order) => {
	cancellingId.value = order.id
	
	try {
		await cancelOrderApi(order.id)
		await loadData()
	} catch (e: any) {
		console.error(e)
		alert(e?.response?.data?.message ?? 'Failed to cancel order.')
	} finally {
		cancellingId.value = null
	}
}

const { setupRealtime } = useRealtime(loadData)

onMounted(async () => {
	const token = localStorage.getItem('auth_token')
	if (!token) {
		await router.push('/login')
		return
	}
	
	await loadData()
	await setupRealtime()
})
</script>

<template>
	<div class="space-y-8">
		<WalletOverview
			:usd-balance="usdBalance"
			:asset-balances="assetBalances"
		/>
		
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<OrderForm
				:symbol="symbol"
				@order-placed="handleOrderPlaced"
			/>
			
			<OrdersTable
				:orders="orders"
				:symbol="symbol"
				:cancelling-id="cancellingId"
				@cancel="handleCancelOrder"
			/>
		</div>
	</div>
</template>
