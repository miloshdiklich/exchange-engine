import type { Order, OrderStatus } from '../types/trading'

export const formatStatus = (status: OrderStatus): string => {
	if (typeof status === 'number') {
		if (status === 1) return 'OPEN'
		if (status === 2) return 'FILLED'
		if (status === 3) return 'CANCELLED'
	}
	return String(status).toUpperCase()
}

export const isOpen = (order: Order): boolean => {
	if (typeof order.status === 'number') {
		return order.status === 1
	}
	return String(order.status).toLowerCase() === 'open'
}

export const calculateVolume = (price: string, amount: string): string | null => {
	const p = Number(price)
	const a = Number(amount)
	if (!p || !a) return null
	return (p * a).toFixed(2)
}

