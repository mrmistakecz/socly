import { ref, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useRealtime() {
    const page = usePage()
    const notifications = ref([])
    const incomingMessage = ref(null)
    const postUpdates = ref({})
    const onlineUsers = ref([])
    const typingUsers = ref([])
    let privateChannel = null
    let publicChannel = null
    let presenceChannel = null
    let typingTimeout = null

    const userId = () => page.props.auth?.user?.id

    onMounted(() => {
        if (!window.Echo || !userId()) return

        // Presence channel — online users
        presenceChannel = window.Echo.join('online')
        
        presenceChannel.here((users) => {
            onlineUsers.value = users.map(u => u.id)
        })
        .joining((user) => {
            if (!onlineUsers.value.includes(user.id)) onlineUsers.value.push(user.id)
        })
        .leaving((user) => {
            onlineUsers.value = onlineUsers.value.filter(id => id !== user.id)
            // Update last seen when user leaves
            console.log(`User ${user.name} left at ${new Date().toISOString()}`)
        })

        // Private channel — messages & notifications
        privateChannel = window.Echo.private(`user.${userId()}`)

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission()
        }

        privateChannel.listen('NewMessage', (e) => {
            incomingMessage.value = e
            
            // Show browser notification if tab is not focused
            if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
                const n = new Notification(e.sender_name || 'Nová zpráva', {
                    body: e.body?.substring(0, 100) || 'Máte novou zprávu',
                    icon: e.sender_avatar || '/favicon.svg',
                    tag: `msg-${e.sender_id}`,
                    data: { url: '/?screen=messages&chat=' + e.sender_id },
                })
                n.onclick = () => {
                    window.focus()
                    n.close()
                }
            }
        })

        privateChannel.listen('NewNotification', (e) => {
            notifications.value.unshift({
                ...e,
                id: Date.now(),
                read: false,
            })
            
            // Show browser notification if tab is not focused
            if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
                const n = new Notification('SOCLY', {
                    body: e.message || 'Máte novou notifikaci',
                    icon: '/favicon.svg',
                    tag: `notif-${Date.now()}`,
                })
                n.onclick = () => {
                    window.focus()
                    n.close()
                }
            }
        })

        // Typing indicators via private channel notifications
        privateChannel.listen('.typing', (e) => {
            if (!typingUsers.value.find(u => u.id === e.userId)) {
                typingUsers.value.push({
                    id: e.userId,
                    name: e.name,
                    conversationId: e.conversationId
                })
            }

            clearTimeout(typingTimeout)
            typingTimeout = setTimeout(() => {
                typingUsers.value = typingUsers.value.filter(u => u.id !== e.userId)
            }, 3000)
        })
    })

    const subscribeToPost = (postId) => {
        const ch = window.Echo.channel(`post.${postId}`)
        ch.listen('PostInteraction', (e) => {
            postUpdates.value = { postId: e.post_id, type: e.type, count: e.count }
        })
        return ch
    }

    const unsubscribeFromPost = (postId) => {
        window.Echo.leave(`post.${postId}`)
    }

    onUnmounted(() => {
        if (privateChannel && userId()) {
            window.Echo.leave(`user.${userId()}`)
        }
        if (presenceChannel) {
            window.Echo.leave('online')
        }
    })

    const dismissNotification = (id) => {
        notifications.value = notifications.value.filter(n => n.id !== id)
    }

    const clearNotifications = () => {
        notifications.value = []
    }

    const sendTyping = (conversationId, receiverId) => {
        // Typing is now handled via server-side broadcasting on private channel
    }

    const stopTyping = (receiverId) => {
        // Typing is now handled via server-side broadcasting on private channel
    }

    const isUserTyping = (userId, conversationId) => {
        return typingUsers.value.some(u => u.id === userId && u.conversationId === conversationId)
    }

    return {
        notifications,
        incomingMessage,
        postUpdates,
        onlineUsers,
        typingUsers,
        dismissNotification,
        clearNotifications,
        sendTyping,
        stopTyping,
        isUserTyping,
        subscribeToPost,
        unsubscribeFromPost,
    }
}
