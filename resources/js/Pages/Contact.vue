<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import Warning from '@/Components/Warning.vue';
import { Link } from '@inertiajs/vue3';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import { InertiaForm } from '@/types/Core';
import { ref } from 'vue';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';

type ContactForm = {
  name: string;
  email: string;
  subject: string;
  message: string;
};

const form = useForm<ContactForm>('post', '/contact', {
  name: '',
  email: '',
  subject: '',
  message: '',
}) as InertiaForm<ContactForm>;

const hasSubmittedForm = ref(false);

const submitMessage = () => {
  form.submit({
    onSuccess: () => {
      hasSubmittedForm.value = true;
    },
  });
};
</script>

<template>
  <div class="mt-5 flex flex-col gap-5">
    <Card class="mx-auto prose flex w-full max-w-6xl flex-col space-y-4">
      <Heading>Contact Coeliac Sanctuary</Heading>

      <div v-if="hasSubmittedForm">
        <div class="mb-8 flex justify-center text-green">
          <CheckCircleIcon class="h-24 w-24" />
        </div>

        <p class="text-center text-xl">
          Thanks for your message, I will aim to reply as soon as possible!
        </p>
      </div>

      <template v-else>
        <p class="prose prose-lg max-w-none text-center lg:prose-xl">
          Need to get in touch with the Coeliac Sanctuary team? Complete the
          form below and we'll get back to you as soon as we can!
        </p>

        <Warning no-icon>
          <p
            class="m-0 prose prose-lg max-w-none text-center font-semibold lg:prose-xl"
          >
            Are you suggesting a location to add to our Eating Out guide? Please
            use our
            <Link href="/wheretoeat/recommend-a-place">recommend a place</Link>
            form instead.
          </p>
        </Warning>

        <form
          class="mt-10! flex flex-col gap-5"
          method="post"
          action="/contact"
          @submit.prevent="submitMessage()"
        >
          <FormInput
            v-model="form.name"
            label="Your name"
            name="name"
            autocomplete="name"
            :error="form.errors.name"
            borders
            required
            @change="form.validate('name')"
          />

          <FormInput
            v-model="form.email"
            label="Your email address"
            type="email"
            name="email"
            autocomplete="email"
            :error="form.errors.email"
            borders
            required
            @change="form.validate('email')"
          ></FormInput>

          <FormInput
            v-model="form.subject"
            label="Subject"
            name="subject"
            :error="form.errors.subject"
            borders
            required
            @change="form.validate('subject')"
          />

          <FormTextarea
            v-model="form.message"
            label="Your message"
            name="message"
            :rows="15"
            :error="form.errors.message"
            :has-error="!!form.errors.message"
            borders
            required
            @change="form.validate('message')"
          />

          <div class="text-center">
            <CoeliacButton
              as="button"
              type="submit"
              label="Send Message"
              size="xl"
              theme="secondary"
              :loading="form.processing"
              @click.prevent="submitMessage()"
            />
          </div>
        </form>
      </template>
    </Card>
  </div>
</template>
