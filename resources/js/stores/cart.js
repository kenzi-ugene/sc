import { ref, computed } from 'vue'

const cartItems = ref([])
const cartCount = computed(() => {
    return cartItems.value.reduce((total, item) => total + item.quantity, 0)
})

export function useCart() {
    const setCartItems = (items) => {
        cartItems.value = items
    }

    return {
        cartItems,
        cartCount,
        setCartItems
    }
} 