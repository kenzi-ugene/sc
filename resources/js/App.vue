<template>
    <div id="app">
        <nav class="navbar">
            <div class="container">
                <router-link to="/" class="nav-link">Home</router-link>
                <router-link to="/about" class="nav-link">About</router-link>
            </div>
        </nav>
        <div class="container">
            <router-view />
        </div>
    </div>
</template>

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
