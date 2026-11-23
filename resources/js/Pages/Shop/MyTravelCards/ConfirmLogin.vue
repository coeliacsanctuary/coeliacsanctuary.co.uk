<script setup lang="ts">
import CoeliacTravelCards from '@/Layouts/CoeliacTravelCards.vue';
import Card from '@/Components/Card.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import FormOneTimeInput from '@/Components/Forms/FormOneTimeInput.vue';

defineProps<{ email: string }>();

defineOptions({ layout: CoeliacTravelCards });

const form = useForm<{ code: string }>('post', '/login/confirm', { code: '' });
</script>

<template>
  <form
    class="mx-auto mt-12 w-[90%] max-w-md"
    action="/login/confirm"
    method="post"
    @submit.prevent="form.submit()"
  >
    <Card class="flex flex-col space-y-6">
      <p class="prose prose-lg max-w-none text-center">
        I've sent a one time access code to
        <span
          class="font-semibold"
          v-text="email"
        />. Please enter it below to continue.
      </p>

      <FormOneTimeInput
        v-model="form.code"
        :length="6"
        name="code"
        label=""
        hide-label
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
        Please note, your code is valid for 15 minutes.
      </p>
    </Card>
  </form>
</template>
