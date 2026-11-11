<script setup lang="ts">
import CoeliacTravelCards from '@/Layouts/CoeliacTravelCards.vue';
import Card from '@/Components/Card.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { useForm } from 'laravel-precognition-vue-inertia';

defineOptions({ layout: CoeliacTravelCards });

const form = useForm<{ email: string }>('post', '/login', { email: '' });
</script>

<template>
  <form
    class="mx-auto mt-12 w-[90%] max-w-md"
    action="/login"
    method="post"
    @submit.prevent="() => form.submit()"
  >
    <Card class="flex flex-col space-y-6">
      <p class="prose prose-lg max-w-none text-center">
        To access your digital travel cards, please enter the email address you
        used when placing your order.
      </p>

      <FormInput
        v-model="form.email"
        name="email"
        label=""
        hide-label
        type="email"
        placeholder="Your email address..."
        :error="form.errors.email"
        borders
        size="xl"
        input-classes="text-center"
        required
      />

      <CoeliacButton
        as="button"
        type="submit"
        label="Continue"
        size="xxl"
        bold
        theme="secondary"
        classes="justify-center"
        :loading="form.processing"
      />

      <p class="prose prose-sm max-w-none text-center font-semibold">
        Not got any digital travel cards yet?
        <a href="https://www.coeliacsantuary.co.uk/shop"
          >Purchase them from my shop!</a
        >
      </p>
    </Card>
  </form>
</template>
