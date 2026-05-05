import { ref } from 'vue'

export function useAction(fn) {
    const loading = ref(false)
    const error = ref(null)

    const execute = async (...args) => {
        if (loading.value) return
        loading.value = true
        error.value = null
        try {
            return await fn(...args)
        } catch (e) {
            error.value = e
            throw e
        } finally {
            loading.value = false
        }
    }

    return { loading, error, execute }
}
