import axios from 'axios'
import type { Order, Profile } from '../types/trading'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost'

export const getAuthHeaders = () => {
	const token = localStorage.getItem('auth_token')
	if (!token) {
		return {}
	}
	return { Authorization: `Bearer ${token}` }
}

export const fetchProfile = async (): Promise<Profile> => {
	const { data } = await axios.get(`${API_BASE_URL}/api/profile`, {
		headers: getAuthHeaders(),
	})
	
	return {
		balance: data.balance ?? '0.00',
		assets: data.assets ?? [],
	}
}

export const fetchOrders = async (symbol: string): Promise<Order[]> => {
	const { data } = await axios.get(`${API_BASE_URL}/api/orders`, {
		headers: getAuthHeaders(),
		params: { symbol },
	})
	
	return (data.my_orders ?? []) as Order[]
}

export const placeOrder = async (
	symbol: string,
	side: string,
	price: number,
	amount: number,
): Promise<void> => {
	await axios.post(
		`${API_BASE_URL}/api/orders`,
		{ symbol, side, price, amount },
		{ headers: getAuthHeaders() },
	)
}

export const cancelOrder = async (orderId: number): Promise<void> => {
	await axios.post(
		`${API_BASE_URL}/api/orders/${orderId}/cancel`,
		{},
		{ headers: getAuthHeaders() },
	)
}

export const fetchUserId = async (): Promise<number | null> => {
	try {
		const { data } = await axios.get(`${API_BASE_URL}/api/me`, {
			headers: getAuthHeaders(),
		})
		
		const payload = data
		const extractedId =
			payload.id ??
			payload.user?.id ??
			payload.data?.id
		
		return extractedId ?? null
	} catch (e) {
		console.error('[API] Error fetching /api/me', e)
		return null
	}
}

