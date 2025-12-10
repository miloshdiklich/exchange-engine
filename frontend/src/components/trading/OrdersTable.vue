<script setup lang="ts">
import type { Order } from '../../types/trading'
import { formatStatus, isOpen } from '../../utils/orderHelpers'

const props = defineProps<{
	orders: Order[]
	symbol: string
	cancellingId: number | null
}>()

const emit = defineEmits<{
	cancel: [order: Order]
}>()

const handleCancel = (order: Order) => {
	if (isOpen(order)) {
		emit('cancel', order)
	}
}
</script>

<template>
	<section class="bg-slate-800 rounded-xl p-6 shadow-md border border-slate-700">
		<h2 class="text-lg font-semibold mb-6">My Orders ({{ symbol }})</h2>
		
		<div v-if="orders.length === 0" class="text-sm text-slate-400">
			No orders yet.
		</div>
		
		<table v-else class="w-full text-sm">
			<thead class="border-b border-slate-700 text-xs text-slate-400">
				<tr>
					<th class="text-left py-2">Side</th>
					<th class="text-left py-2">Amount</th>
					<th class="text-left py-2">Price</th>
					<th class="text-left py-2">Status</th>
					<th class="py-2"></th>
				</tr>
			</thead>
			<tbody>
				<tr
					v-for="order in orders"
					:key="order.id"
					class="border-b border-slate-800 hover:bg-slate-700/40 transition-colors"
				>
					<td class="py-2 font-semibold">
						<span
							:class="order.side === 'buy' ? 'text-green-400' : 'text-red-400'"
						>
							{{ order.side.toUpperCase() }}
						</span>
					</td>
					<td class="py-2">{{ order.amount }}</td>
					<td class="py-2">{{ order.price }}</td>
					<td class="py-2">
						<span
							class="px-2 py-1 rounded-md text-xs border"
							:class="formatStatus(order.status) === 'FILLED'
								? 'bg-green-500/10 text-green-400 border-green-500/40'
								: formatStatus(order.status) === 'OPEN'
								? 'bg-yellow-500/10 text-yellow-300 border-yellow-500/40'
								: 'bg-red-500/10 text-red-400 border-red-500/40'"
						>
							{{ formatStatus(order.status) }}
						</span>
					</td>
					<td class="py-2 text-right">
						<button
							v-if="isOpen(order)"
							@click="handleCancel(order)"
							class="text-xs px-3 py-1 rounded-md bg-red-600 hover:bg-red-500 disabled:opacity-50"
							:disabled="cancellingId === order.id"
						>
							{{ cancellingId === order.id ? '…' : 'Cancel' }}
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</section>
</template>

