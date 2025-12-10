export type AssetBalance = {
	symbol: string
	amount: string
}

export type OrderStatus = number | string

export type Order = {
	id: number
	symbol: string
	side: string
	price: string
	amount: string
	status: OrderStatus
}

export type Profile = {
	balance: string
	assets: AssetBalance[]
}

