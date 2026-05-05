import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

export function usePullToRefresh(containerRef, options = {}) {
    const threshold = options.threshold || 60
    const isPulling = ref(false)
    const isRefreshing = ref(false)
    const pullDistance = ref(0)

    let startY = 0
    let currentY = 0

    const onTouchStart = (e) => {
        const el = containerRef.value || document.scrollingElement
        if ((el.scrollTop || 0) > 0) return
        startY = e.touches[0].clientY
    }

    const onTouchMove = (e) => {
        if (!startY) return
        currentY = e.touches[0].clientY
        const diff = currentY - startY

        if (diff > 0) {
            pullDistance.value = Math.min(diff, threshold * 2)
            isPulling.value = diff > threshold
        }
    }

    const onTouchEnd = () => {
        if (isPulling.value && !isRefreshing.value) {
            isRefreshing.value = true
            router.reload({
                preserveScroll: true,
                onFinish: () => {
                    isRefreshing.value = false
                },
            })
        }
        startY = 0
        currentY = 0
        pullDistance.value = 0
        isPulling.value = false
    }

    onMounted(() => {
        document.addEventListener('touchstart', onTouchStart, { passive: true })
        document.addEventListener('touchmove', onTouchMove, { passive: true })
        document.addEventListener('touchend', onTouchEnd)
    })

    onUnmounted(() => {
        document.removeEventListener('touchstart', onTouchStart)
        document.removeEventListener('touchmove', onTouchMove)
        document.removeEventListener('touchend', onTouchEnd)
    })

    return { isPulling, isRefreshing, pullDistance }
}
