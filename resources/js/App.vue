<template>
    <Layout>
        <router-view></router-view>
    </Layout>
</template>

<script>
import Layout from './components/Layout.vue'

export default {
    name: 'App',
    components: {
        Layout
    }
}
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
