<template>
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <router-link
                            to="/"
                            class="text-xl font-bold text-indigo-600"
                            >Shop</router-link
                        >
                    </div>
                    <div v-if="isAuthenticated" class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <router-link
                            to="/products"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            active-class="border-indigo-500 text-gray-900"
                        >
                            Products
                        </router-link>
                        <router-link
                            to="/cart"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            active-class="border-indigo-500 text-gray-900"
                        >
                            Cart
                            <span v-if="cartCount > 0" class="ml-1 text-red-500">
                                ({{ cartCount }})
                            </span>
                        </router-link>
                        <router-link
                            to="/orders"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            active-class="border-indigo-500 text-gray-900"
                        >
                            Order History
                        </router-link>
                    </div>
                </div>
                <div class="flex items-center">
                    <template v-if="isAuthenticated">
                        <span class="text-gray-700 mr-4">{{ userName }}</span>
                        <button
                            @click="logout"
                            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Logout
                        </button>
                    </template>
                    <template v-else>
                        <router-link
                            to="/login"
                            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Login
                        </router-link>
                        <router-link
                            to="/register"
                            class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Register
                        </router-link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
import { useRouter } from "vue-router";
import axios from "axios";
import { onMounted, ref } from "vue";
import { useCart } from '../stores/cart';

export default {
    name: "Navigation",
    setup() {
        const router = useRouter();
        const { cartCount } = useCart();
        const isAuthenticated = ref(false);
        const userName = ref('');

        const setupAuth = async () => {
            const token = localStorage.getItem("token");
            if (token) {
                axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
                isAuthenticated.value = true;
                try {
                    const response = await axios.get("/api/user");
                    userName.value = response.data.name;
                } catch (error) {
                    console.error("Error fetching user data:", error);
                }
            } else {
                delete axios.defaults.headers.common["Authorization"];
                isAuthenticated.value = false;
                userName.value = '';
            }
        };

        const logout = async () => {
            try {
                await axios.post("/api/logout");
                localStorage.removeItem("token");
                delete axios.defaults.headers.common["Authorization"];
                isAuthenticated.value = false;
                userName.value = '';
                router.push({ name: 'Login' });
            } catch (error) {
                console.error("Error logging out:", error);
            }
        };

        onMounted(() => {
            setupAuth();
        });

        return {
            logout,
            cartCount,
            isAuthenticated,
            userName
        };
    },
};
</script>
