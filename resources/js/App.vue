<template>
  <div class="min-h-screen">
    <router-view />
    <CookieConsent ref="cookieConsent" />
  </div>
</template>

<script>
import CookieConsent from './components/CookieConsent.vue';

export default {
  name: 'App',
  components: {
    CookieConsent,
  },
};
</script>
