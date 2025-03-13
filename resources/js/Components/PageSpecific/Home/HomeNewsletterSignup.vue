<script setup lang="ts">
import Card from '@/Components/Card.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import useNewsletter from '@/composables/useNewsletter';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ref } from 'vue';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';

const { subscribeForm } = useNewsletter();
const hasSignedUpToNewsletter = ref(false);
</script>

<template>
  <Card
    theme="primary-light"
    faded
    class="mx-4 rounded-xl shadow-lg"
  >
    <div
      v-if="!hasSignedUpToNewsletter"
      class="relative p-3 flex flex-col"
    >
      <h2
        class="font-coeliac mx-auto text-center text-4xl font-semibold tracking-tight"
      >
        Want more updates? Sign up to my newsletter!
      </h2>

      <form
        class="w-full mt-10 flex flex-col gap-y-4 sm:flex-row sm:gap-y-0 sm:gap-x-4"
        @submit.prevent="
          subscribeForm.submit({
            preserveScroll: true,
            onSuccess: () => (hasSignedUpToNewsletter = true),
          })
        "
      >
        <FormInput
          id="email-address"
          v-model="subscribeForm.email"
          label=""
          hide-label
          autocomplete="email"
          name="email-address"
          placeholder="Enter your email address..."
          class="flex-1 h-full"
          :error="subscribeForm.errors?.email"
          borders
          size="large"
          type="email"
          required
          wrapper-classes="h-full"
          input-classes="h-full p-4!"
        />

        <CoeliacButton
          as="button"
          classes="w-auto justify-center"
          label="Subscribe"
          theme="secondary"
          type="submit"
          size="xl"
          :loading="subscribeForm.processing"
        />
      </form>
    </div>

    <div
      v-else
      class="flex space-x-2 items-center justify-center"
    >
      <div class="text-secondary">
        <CheckCircleIcon class="h-12 w-12" />
      </div>

      <p class="text-center text-xl sm:text-2xl">
        Thank you for signing up to my newsletter!
      </p>
    </div>
  </Card>
</template>
