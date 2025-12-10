<script setup lang="ts">
import { ref, computed } from 'vue'
import { placeOrder } from '../../utils/api'
import { calculateVolume } from '../../utils/orderHelpers'

const props = defineProps<{
	symbol: 'BTC' | 'ETH'
}>()

const emit = defineEmits<{
	orderPlaced: []
}>()

const side = ref<'buy' | 'sell'>('buy')
const price = ref<string>('')
const amount = ref<string>('')

const loading = ref(false)
const error = ref('')
const message = ref('')

const volumePreview = computed(() => {
	return calculateVolume(price.value, amount.value)
})

const handleSubmit = async () => {
	error.value = ''
	message.value = ''
	loading.value = true
	
	try {
		await placeOrder(
			props.symbol,
			side.value,
			Number(price.value),
			Number(amount.value),
		)
		
		message.value = 'Order placed successfully.'
		price.value = ''
		amount.value = ''
		
		emit('orderPlaced')
	} catch (e: any) {
		console.error(e)
		error.value = e?.response?.data?.message ?? 'Failed to place order.'
	} finally {
		loading.value = false
	}
}
</script>

<template>
	<section class="bg-slate-800 rounded-xl p-6 shadow-md border border-slate-700">
		<h2 class="text-lg font-semibold mb-6">Place Limit Order</h2>
		
		<form @submit.prevent="handleSubmit" class="space-y-5">
			<div class="grid grid-cols-2 gap-4">
				<div>
					<label class="block text-xs text-slate-400 mb-1">Side</label>
					<select
						v-model="side"
						class="w-full bg-slate-900 border border-slate-600 rounded-md px-3 py-2 text-sm
                       focus:outline-none focus:ring focus:ring-indigo-500"
					>
						<option value="buy">Buy</option>
						<option value="sell">Sell</option>
					</select>
				</div>
			</div>
			
			<div class="grid grid-cols-2 gap-4">
				<div>
					<label class="block text-xs text-slate-400 mb-1">Price (USD)</label>
					<input
						v-model="price"
						type="number"
						step="0.01"
						class="w-full bg-slate-900 border border-slate-600 rounded-md px-3 py-2 text-sm"
					/>
				</div>
				
				<div>
					<label class="block text-xs text-slate-400 mb-1">Amount</label>
					<input
						v-model="amount"
						type="number"
						step="0.00000001"
						class="w-full bg-slate-900 border border-slate-600 rounded-md px-3 py-2 text-sm"
					/>
				</div>
			</div>
			
			<p class="text-xs text-slate-400">
				Volume:
				<span>{{ volumePreview ? volumePreview + ' USD' : '—' }}</span>
			</p>
			
			<p v-if="error" class="text-xs text-red-400">{{ error }}</p>
			<p v-if="message" class="text-xs text-green-400">{{ message }}</p>
			
			<button
				type="submit"
				class="w-full bg-indigo-600 hover:bg-indigo-500 rounded-md px-4 py-2 text-sm font-semibold
                   disabled:opacity-50"
				:disabled="loading"
			>
				{{ loading ? 'Submitting…' : 'Place Order' }}
			</button>
		</form>
	</section>
</template>

