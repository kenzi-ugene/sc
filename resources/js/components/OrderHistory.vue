<template>
    <div class="bg-white">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-extrabold text-gray-900 mb-6">
                Order History
            </h2>
            <div v-if="orders.length === 0" class="text-center py-12">
                <p class="text-gray-500">No orders found</p>
            </div>
            <div v-else class="space-y-8">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="border rounded-lg p-6"
                >
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Order #{{ order.id }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{
                                    new Date(
                                        order.created_at
                                    ).toLocaleDateString()
                                }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-medium text-gray-900">
                                ${{ order.total_amount }}
                            </p>
                            <p class="text-sm text-gray-500 capitalize">
                                {{ order.status }}
                            </p>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center py-4"
                        >
                            <div
                                class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200"
                            >
                                <img
                                    :src="
                                        item.product.image
                                            ? `/storage/${item.product.image}`
                                            : 'https://via.placeholder.com/300'
                                    "
                                    :alt="item.product.name"
                                    class="h-full w-full object-cover object-center"
                                />
                            </div>
                            <div class="ml-4 flex flex-1 flex-col">
                                <div>
                                    <div
                                        class="flex justify-between text-base font-medium text-gray-900"
                                    >
                                        <h4>{{ item.product.name }}</h4>
                                        <p class="ml-4">
                                            ${{ item.price * item.quantity }}
                                        </p>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Quantity: {{ item.quantity }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import axios from "axios";

export default {
    name: "OrderHistory",
    setup() {
        const orders = ref([]);

        const fetchOrders = async () => {
            try {
                const response = await axios.get("/api/orders");
                orders.value = response.data;
            } catch (error) {
                console.error("Error fetching orders:", error);
            }
        };

        onMounted(fetchOrders);

        return {
            orders,
        };
    },
};
</script>
