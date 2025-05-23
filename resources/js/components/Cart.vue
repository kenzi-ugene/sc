<template>
  <div class="bg-white">
    <div class="max-w-2xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:max-w-7xl lg:px-8">
      <h2 class="text-2xl font-extrabold text-gray-900 mb-6">Shopping Cart</h2>
      <div v-if="cartItems.length === 0" class="text-center py-12">
        <p class="text-gray-500">Your cart is empty</p>
      </div>
      <div v-else class="space-y-8">
        <div v-for="item in cartItems" :key="item.id" class="flex items-center py-6 border-b">
          <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
            <img
              :src="item.product.image ? `/storage/${item.product.image}` : 'https://via.placeholder.com/300'"
              :alt="item.product.name"
              class="h-full w-full object-cover object-center"
            />
          </div>
          <div class="ml-4 flex flex-1 flex-col">
            <div>
              <div class="flex justify-between text-base font-medium text-gray-900">
                <h3>{{ item.product.name }}</h3>
                <p class="ml-4">${{ item.product.price * item.quantity }}</p>
              </div>
              <p class="mt-1 text-sm text-gray-500">{{ item.product.description }}</p>
            </div>
            <div class="flex flex-1 items-end justify-between text-sm">
              <div class="flex items-center">
                <label for="quantity" class="mr-2">Qty</label>
                <select
                  id="quantity"
                  v-model="item.quantity"
                  @change="updateQuantity(item)"
                  class="rounded-md border-gray-300"
                >
                  <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
                </select>
              </div>
              <button
                type="button"
                @click="removeItem(item)"
                class="font-medium text-indigo-600 hover:text-indigo-500"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
        <div class="border-t border-gray-200 py-6">
          <div class="flex justify-between text-base font-medium text-gray-900">
            <p>Subtotal</p>
            <p>${{ subtotal }}</p>
          </div>
          <p class="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
          <div class="mt-6">
            <button
              @click="checkout"
              class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Checkout
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'Cart',
  setup() {
    const cartItems = ref([])

    const fetchCart = async () => {
      try {
        const response = await axios.get('/api/cart')
        cartItems.value = response.data
      } catch (error) {
        console.error('Error fetching cart:', error)
      }
    }

    const updateQuantity = async (item) => {
      try {
        await axios.put(`/api/cart/${item.id}`, {
          quantity: item.quantity
        })
      } catch (error) {
        console.error('Error updating cart:', error)
        fetchCart() // Refresh cart on error
      }
    }

    const removeItem = async (item) => {
      try {
        await axios.delete(`/api/cart/${item.id}`)
        cartItems.value = cartItems.value.filter(i => i.id !== item.id)
      } catch (error) {
        console.error('Error removing item:', error)
      }
    }

    const subtotal = computed(() => {
      return cartItems.value.reduce((total, item) => {
        return total + (item.product.price * item.quantity)
      }, 0)
    })

    const checkout = async () => {
      try {
        const response = await axios.post('/api/cart/checkout')
        alert(response.data.message)
        cartItems.value = [] // Clear the cart
      } catch (error) {
        console.error('Error during checkout:', error)
        alert(error.response?.data?.message || 'An error occurred during checkout')
      }
    }

    onMounted(fetchCart)

    return {
      cartItems,
      subtotal,
      updateQuantity,
      removeItem,
      checkout
    }
  }
}
</script> 