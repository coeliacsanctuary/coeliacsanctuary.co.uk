<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <Card class="mx-auto mt-3 flex max-w-3xl flex-col space-y-4">
    <Heading
      :back-link="{
        label: 'Back to shop',
        href: '/shop',
        position: 'bottom',
        direction: 'center',
      }"
    >
      Sorry, your link has expired
    </Heading>

    <p class="prose-lg max-w-none text-center font-semibold md:prose-xl">
      Sorry, your download link has expired. Please
      <Link
        href="/contact"
        class="text-primary-dark transition hover:text-black"
      >
        get in touch
      </Link>

      with your order details and we'll send you a new link.
    </p>
  </Card>
</template>
