<template>
    <div class="bg-white">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:max-w-7xl lg:px-8">
            <h2 class="text-2xl font-extrabold text-gray-900 mb-6">Products</h2>
            <div class="mb-6">
                <button
                    @click="showForm = !showForm"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                >
                    Add Product
                </button>
            </div>
            <div v-if="showForm" class="mb-6 bg-gray-100 p-4 rounded">
                <form @submit.prevent="addProduct">
                    <input
                        v-model="newProduct.name"
                        type="text"
                        placeholder="Name"
                        class="mb-2 w-full border p-2"
                        required
                    />
                    <textarea
                        v-model="newProduct.description"
                        placeholder="Description"
                        class="mb-2 w-full border p-2"
                        required
                    ></textarea>
                    <input
                        v-model.number="newProduct.price"
                        type="number"
                        placeholder="Price"
                        class="mb-2 w-full border p-2"
                        required
                    />
                    <input
                        v-model.number="newProduct.stock"
                        type="number"
                        placeholder="Stock"
                        class="mb-2 w-full border p-2"
                        required
                    />
                    <input
                        @change="onFileChange"
                        type="file"
                        class="mb-2 w-full"
                    />
                    <button
                        type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded"
                    >
                        Add
                    </button>
                </form>
            </div>
            <div v-if="editingProduct" class="mb-6 bg-gray-100 p-4 rounded">
                <h3 class="text-lg font-bold mb-2">Edit Product</h3>
                <form @submit.prevent="updateProduct">
                    <input
                        v-model="editForm.name"
                        type="text"
                        placeholder="Name"
                        class="mb-2 w-full border p-2"
                        required
                    />
                    <textarea
                        v-model="editForm.description"
                        placeholder="Description"
                        class="mb-2 w-full border p-2"
                        required
                    ></textarea>
                    <input
                        v-model.number="editForm.price"
                        type="number"
                        placeholder="Price"
                        class="mb-2 w-full border p-2"
                        required
                    />
                    <input
                        v-model.number="editForm.stock"
                        type="number"
                        placeholder="Stock"
                        class="mb-2 w-full border p-2"
                        required
                    />
                    <input
                        @change="onEditFileChange"
                        type="file"
                        class="mb-2 w-full"
                    />
                    <button
                        type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded"
                    >
                        Update
                    </button>
                    <button
                        type="button"
                        @click="cancelEdit"
                        class="ml-2 bg-gray-400 text-white px-4 py-2 rounded"
                    >
                        Cancel
                    </button>
                </form>
            </div>
            <div
                class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="group flex flex-col h-[500px]"
                >
                    <div
                        class="w-full h-64 bg-gray-200 rounded-lg overflow-hidden"
                    >
                        <img
                            :src="
                                product.image
                                    ? `/storage/${product.image}`
                                    : 'https://via.placeholder.com/300'
                            "
                            :alt="product.name"
                            class="w-full h-full object-center object-cover group-hover:opacity-75"
                        />
                    </div>
                    <div class="flex flex-col flex-grow mt-4">
                        <h3 class="text-sm text-gray-700 line-clamp-2">
                            {{ product.name }}
                        </h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">
                            ${{ product.price }}
                        </p>
                        <div class="mt-auto space-y-2">
                            <button
                                @click="addToCart(product)"
                                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Add to Cart
                            </button>
                            <button
                                @click="startEdit(product)"
                                class="w-full bg-yellow-500 text-white py-2 px-4 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteProduct(product.id)"
                                class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Delete
                            </button>
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
    name: "ProductList",
    setup() {
        const products = ref([]);
        const showForm = ref(false);
        const newProduct = ref({
            name: "",
            description: "",
            price: "",
            stock: "",
            image: null,
        });
        const editingProduct = ref(null);
        const editForm = ref({
            name: "",
            description: "",
            price: "",
            stock: "",
            image: null,
        });

        const fetchProducts = async () => {
            try {
                const response = await axios.get("/api/products");
                products.value = response.data;
            } catch (error) {
                console.error("Error fetching products:", error);
            }
        };

        const addToCart = async (product) => {
            try {
                await axios.post("/api/cart", {
                    product_id: product.id,
                    quantity: 1,
                });
                alert("Product added to cart!");
            } catch (error) {
                console.error("Error adding to cart:", error);
            }
        };

        const onFileChange = (e) => {
            newProduct.value.image = e.target.files[0];
        };

        const addProduct = async () => {
            const formData = new FormData();
            for (const key in newProduct.value) {
                if (newProduct.value[key] !== null) {
                    formData.append(key, newProduct.value[key]);
                }
            }
            try {
                await axios.post("/api/products", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                alert("Product added!");
                showForm.value = false;
                fetchProducts();
                // Optionally, reset form fields here
            } catch (error) {
                alert("Error adding product");
            }
        };

        const deleteProduct = async (id) => {
            if (!confirm("Are you sure you want to delete this product?"))
                return;
            try {
                await axios.delete(`/api/products/${id}`);
                alert("Product deleted!");
                fetchProducts();
            } catch (error) {
                alert("Error deleting product");
            }
        };

        const startEdit = (product) => {
            editingProduct.value = product;
            editForm.value = {
                name: product.name,
                description: product.description,
                price: product.price,
                stock: product.stock,
                image: null,
            };
        };

        const onEditFileChange = (e) => {
            editForm.value.image = e.target.files[0];
        };

        const updateProduct = async () => {
            const formData = new FormData();
            for (const key in editForm.value) {
                if (editForm.value[key] !== null) {
                    formData.append(key, editForm.value[key]);
                }
            }
            formData.append("_method", "PUT");
            try {
                await axios.post(
                    `/api/products/${editingProduct.value.id}`,
                    formData,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    }
                );
                alert("Product updated!");
                editingProduct.value = null;
                fetchProducts();
            } catch (error) {
                alert("Error updating product");
            }
        };

        const cancelEdit = () => {
            editingProduct.value = null;
        };

        onMounted(fetchProducts);

        return {
            products,
            addToCart,
            showForm,
            newProduct,
            addProduct,
            onFileChange,
            deleteProduct,
            editingProduct,
            editForm,
            startEdit,
            onEditFileChange,
            updateProduct,
            cancelEdit,
        };
    },
};
</script>
