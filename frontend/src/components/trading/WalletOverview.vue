<script setup lang="ts">
import type { AssetBalance } from '../../types/trading'

defineProps<{
	usdBalance: string
	assetBalances: AssetBalance[]
}>()
</script>

<template>
	<section class="bg-slate-800 rounded-xl p-6 shadow-md border border-slate-700">
		<div class="flex justify-between items-center mb-4">
			<h2 class="text-lg font-semibold">Wallet Overview</h2>
		</div>
		
		<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
			<div>
				<p class="text-xs text-slate-400">USD Balance</p>
				<p class="text-2xl font-bold mt-1">${{ usdBalance }}</p>
			</div>
			
			<div class="md:col-span-2">
				<p class="text-xs text-slate-400">Assets</p>
				<div class="flex flex-wrap gap-2 mt-1">
					<span
						v-if="assetBalances.length === 0"
						class="text-xs text-slate-500"
					>
						No assets yet
					</span>
					<span
						v-for="asset in assetBalances"
						:key="asset.symbol"
						class="bg-slate-700 px-3 py-1 rounded-md text-xs"
					>
						{{ asset.symbol }}: {{ asset.amount }}
					</span>
				</div>
			</div>
		</div>
	</section>
</template>

