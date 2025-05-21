<template>
  <div class="bg-white">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:max-w-7xl lg:px-8">
      <h2 class="text-2xl font-extrabold text-gray-900 mb-6">Products</h2>
      <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
        <div v-for="product in products" :key="product.id" class="group">
          <div class="w-full aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden xl:aspect-w-7 xl:aspect-h-8">
            <img
              :src="product.image ? `/storage/${product.image}` : 'https://via.placeholder.com/300'"
              :alt="product.name"
              class="w-full h-full object-center object-cover group-hover:opacity-75"
            />
          </div>
          <h3 class="mt-4 text-sm text-gray-700">{{ product.name }}</h3>
          <p class="mt-1 text-lg font-medium text-gray-900">${{ product.price }}</p>
          <div class="mt-4">
            <button
              @click="addToCart(product)"
              class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'ProductList',
  setup() {
    const products = ref([])

    const fetchProducts = async () => {
      try {
        const response = await axios.get('/api/products')
        products.value = response.data
      } catch (error) {
        console.error('Error fetching products:', error)
      }
    }

    const addToCart = async (product) => {
      try {
        await axios.post('/api/cart', {
          product_id: product.id,
          quantity: 1
        })
        alert('Product added to cart!')
      } catch (error) {
        console.error('Error adding to cart:', error)
      }
    }

    onMounted(fetchProducts)

    return {
      products,
      addToCart
    }
  }
}
</script> 