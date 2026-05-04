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

        // Public channel — post interactions (likes/comments counts)
        publicChannel = window.Echo.channel('posts')

        publicChannel.listen('PostInteraction', (e) => {
            postUpdates.value = {
                postId: e.post_id,
                type: e.type,
                count: e.count,
            }
        })

        // Listen for typing whispers
        privateChannel.listenForWhisper('typing', (e) => {
            if (!typingUsers.value.find(u => u.id === e.userId)) {
                typingUsers.value.push({
                    id: e.userId,
                    name: e.name,
                    conversationId: e.conversationId
                })
            }
            
            // Remove typing indicator after 3 seconds of inactivity
            clearTimeout(typingTimeout)
            typingTimeout = setTimeout(() => {
                typingUsers.value = typingUsers.value.filter(u => u.id !== e.userId)
            }, 3000)
        })

        privateChannel.listenForWhisper('stop-typing', (e) => {
            typingUsers.value = typingUsers.value.filter(u => u.id !== e.userId)
        })
    })

    onUnmounted(() => {
        if (privateChannel && userId()) {
            window.Echo.leave(`user.${userId()}`)
        }
        if (publicChannel) {
            window.Echo.leave('posts')
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
        if (privateChannel && receiverId) {
            privateChannel.whisper('typing', {
                userId: userId(),
                name: page.props.auth?.user?.name,
                conversationId: conversationId
            })
        }
    }

    const stopTyping = (receiverId) => {
        if (privateChannel && receiverId) {
            privateChannel.whisper('stop-typing', {
                userId: userId()
            })
        }
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
    }
}
