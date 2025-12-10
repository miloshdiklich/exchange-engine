import { ref, onBeforeUnmount } from 'vue'
import createEcho from '../echo'
import { fetchUserId } from '../utils/api'

export function useRealtime(onOrderMatched: () => Promise<void>) {
	const userId = ref<number | null>(null)
	const channelName = ref<string | null>(null)
	const echoInstance = ref<ReturnType<typeof createEcho> | null>(null)

	const setupRealtime = async () => {
		const token = localStorage.getItem('auth_token')
		if (!token) {
			console.warn('[Realtime] No token, aborting realtime setup')
			return
		}

		console.log('[Realtime] Starting setupRealtime…')

		const extractedId = await fetchUserId()
		userId.value = extractedId

		if (!userId.value) {
			console.warn('[Realtime] No userId, aborting realtime setup')
			return
		}

		console.log('[Realtime] /api/me OK, user id =', userId.value)

		echoInstance.value = createEcho(token)

		const name = `user.${userId.value}`
		channelName.value = name

		console.log('[Realtime] Subscribing to channel:', name)

		const channel = echoInstance.value.private(name)

		// @ts-ignore
		channel.subscribed(() => {
			console.log('[Realtime] SUBSCRIBED to', name)
		})

		// @ts-ignore
		channel.error((err: any) => {
			console.error('[Realtime] Channel error on', name, err)
		})

		channel.listen('.OrderMatched', async (payload: any) => {
			console.log('[Realtime] OrderMatched EVENT RECEIVED:', payload)
			await onOrderMatched()
		})
	}

	const cleanup = () => {
		if (echoInstance.value && channelName.value) {
			echoInstance.value.leave(channelName.value)
		}
	}

	onBeforeUnmount(() => {
		cleanup()
	})

	return {
		setupRealtime,
		cleanup,
	}
}

