<script lang="ts" setup>
import FacebookIcon from '@/Icons/FacebookIcon.vue';
import TwitterIcon from '@/Icons/TwitterIcon.vue';
import InstagramIcon from '@/Icons/InstagramIcon.vue';
import { Link } from '@inertiajs/vue3';
import BlueskyIcon from '@/Icons/BlueskyIcon.vue';

const year = new Date().getFullYear();

const navigation: { links: { label: string; url: string }[] } = {
  links: [
    { label: 'Contact', url: '/contact' },
    { label: 'Terms', url: '/terms-of-use' },
    { label: 'Privacy', url: '/privacy-policy' },
    { label: 'Work with Us', url: '/work-with-us' },
  ],
};
</script>

<template>
  <footer class="bg-primary">
    <div
      class="flex w-full flex-col items-center justify-center px-4 sm:flex-row sm:justify-between"
    >
      <!-- Links -->
      <ul class="flex flex-wrap gap-3 text-sm">
        <li
          v-for="item in navigation.links"
          :key="item.label"
        >
          <Link
            :href="item.url"
            class="hover:text-gray-900"
          >
            {{ item.label }}
          </Link>
        </li>
      </ul>

      <p class="mx-auto my-2 text-center text-xs leading-5">
        Copyright &copy; 2014 - {{ year }} Coeliac Sanctuary.
      </p>

      <div class="flex items-center justify-center space-x-3">
        <a
          href="https://www.facebook.com/coeliacsanctuary/"
          target="_blank"
        >
          <FacebookIcon />
        </a>
        <a
          href="https://www.instagram.com/coeliacsanctuary"
          target="_blank"
        >
          <InstagramIcon />
        </a>
        <a
          href="https://bsky.app/profile/coeliacsanctuary.bsky.social"
          target="_blank"
        >
          <BlueskyIcon />
        </a>
        <a
          href="https://twitter.com/coeliacsanc"
          target="_blank"
        >
          <TwitterIcon />
        </a>
      </div>
    </div>
  </footer>
</template>
