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
      class="relative flex flex-col p-3"
    >
      <h2
        class="mx-auto text-center font-coeliac text-4xl font-semibold tracking-tight"
      >
        Want more updates? Sign up to my newsletter!
      </h2>

      <form
        class="mt-10 flex w-full flex-col gap-y-4 sm:flex-row sm:gap-x-4 sm:gap-y-0"
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
          class="h-full flex-1"
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
      class="flex items-center justify-center space-x-2"
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
