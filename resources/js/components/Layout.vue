<template>
  <div>
    <Navigation />
    <main>
      <slot></slot>
    </main>
  </div>
</template>

<script>
import Navigation from './Navigation.vue'

export default {
  name: 'Layout',
  components: {
    Navigation
  }
}
</script> 