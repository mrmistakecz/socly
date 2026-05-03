import { ref, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useRealtime() {
    const page = usePage()
    const notifications = ref([])
    const incomingMessage = ref(null)
    const postUpdates = ref({})
    let privateChannel = null
    let publicChannel = null

    const userId = () => page.props.auth?.user?.id

    onMounted(() => {
        if (!window.Echo || !userId()) return

        // Private channel — messages & notifications
        privateChannel = window.Echo.private(`user.${userId()}`)

        privateChannel.listen('NewMessage', (e) => {
            incomingMessage.value = e
        })

        privateChannel.listen('NewNotification', (e) => {
            notifications.value.unshift({
                ...e,
                id: Date.now(),
                read: false,
            })
        })

        // Public channel — post interactions (likes/comments counts)
        publicChannel = window.Echo.channel('posts')

        publicChannel.listen('PostInteraction', (e) => {
            postUpdates.value = {
                postId: e.post_id,
                type: e.type,
                count: e.count,
            }
        })
    })

    onUnmounted(() => {
        if (privateChannel && userId()) {
            window.Echo.leave(`user.${userId()}`)
        }
        if (publicChannel) {
            window.Echo.leave('posts')
        }
    })

    const dismissNotification = (id) => {
        notifications.value = notifications.value.filter(n => n.id !== id)
    }

    const clearNotifications = () => {
        notifications.value = []
    }

    return {
        notifications,
        incomingMessage,
        postUpdates,
        dismissNotification,
        clearNotifications,
    }
}
