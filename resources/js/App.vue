<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <router-link
                                to="/"
                                class="text-xl font-bold text-indigo-600"
                            >
                                Shop
                            </router-link>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <router-link
                                to="/products"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Products
                            </router-link>
                            <router-link
                                to="/cart"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Cart
                            </router-link>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <template v-if="isAuthenticated">
                            <span class="text-gray-500 mr-4">{{
                                user.name
                            }}</span>
                            <button
                                @click="logout"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
                            >
                                Logout
                            </button>
                        </template>
                        <template v-else>
                            <router-link
                                to="/login"
                                class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                            >
                                Login
                            </router-link>
                            <router-link
                                to="/register"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
                            >
                                Register
                            </router-link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <router-view></router-view>
        </main>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

export default {
    name: "App",
    setup() {
        const router = useRouter();
        const isAuthenticated = ref(false);
        const user = ref(null);

        const checkAuth = async () => {
            const token = localStorage.getItem("token");
            if (token) {
                axios.defaults.headers.common[
                    "Authorization"
                ] = `Bearer ${token}`;
                try {
                    const response = await axios.get("/api/user");
                    user.value = response.data;
                    isAuthenticated.value = true;
                } catch (error) {
                    localStorage.removeItem("token");
                    delete axios.defaults.headers.common["Authorization"];
                }
            }
        };

        const logout = async () => {
            try {
                await axios.post("/api/logout");
            } catch (error) {
                console.error("Logout error:", error);
            } finally {
                localStorage.removeItem("token");
                delete axios.defaults.headers.common["Authorization"];
                isAuthenticated.value = false;
                user.value = null;
                router.push("/login");
            }
        };

        onMounted(checkAuth);

        return {
            isAuthenticated,
            user,
            logout,
        };
    },
};
</script>

<style>
.navbar {
    background-color: #2c3e50;
    padding: 1rem 0;
}
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}
.nav-link {
    color: white;
    text-decoration: none;
    margin-right: 1rem;
}
.nav-link:hover {
    color: #42b983;
}
</style>
