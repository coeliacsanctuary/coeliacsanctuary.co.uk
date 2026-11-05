<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import Modal from '@/Components/Overlays/Modal.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import FormInput from '@/Components/Forms/FormInput.vue';

const props = defineProps<{
  open: boolean;
  tripKey: string;
  title: string;
  canEdit: boolean;
}>();

defineEmits(['close']);

const url = computed(
  (): string =>
    `${typeof window !== 'undefined' ? window.location.origin : ''}/wheretoeat/trip-planner/${props.tripKey}`,
);

const canShare = ref(false);
const copied = ref(false);
const sent = ref(false);

onMounted(() => {
  canShare.value = typeof navigator.share === 'function';
});

const share = () => {
  void navigator.share({ title: props.title, url: url.value }).catch(() => {});
};

const copyLink = () => {
  void navigator.clipboard.writeText(url.value).then(() => {
    copied.value = true;

    setTimeout(() => {
      copied.value = false;
    }, 2000);
  });
};

const form = useForm<{ email: string }>(
  'post',
  `/wheretoeat/trip-planner/${props.tripKey}/email`,
  { email: '' },
);

const sendEmail = () => {
  sent.value = false;

  form.submit({
    preserveScroll: true,
    preserveState: true,
    only: ['canEdit'],
    onSuccess: () => {
      sent.value = true;
      form.reset();
    },
  });
};
</script>

<template>
  <Modal
    :open="open"
    size="medium"
    width="w-full"
    no-padding
    @close="$emit('close')"
  >
    <template #header>
      <h3 class="pl-3 font-semibold lg:text-lg">Share your trip</h3>
    </template>

    <div class="flex flex-col space-y-4 p-4">
      <CoeliacButton
        v-if="canShare"
        as="button"
        type="button"
        theme="primary"
        label="Share…"
        classes="justify-center"
        @click="share()"
      />

      <div class="flex gap-2">
        <input
          :value="url"
          readonly
          class="min-w-0 flex-1 rounded-md border border-grey-off px-3 py-2 text-sm"
          @focus="($event.target as HTMLInputElement).select()"
        />

        <CoeliacButton
          as="button"
          type="button"
          theme="light"
          :label="copied ? 'Copied!' : 'Copy link'"
          @click="copyLink()"
        />
      </div>

      <form
        class="flex flex-col space-y-2 border-t border-grey-off pt-4"
        @submit.prevent="sendEmail()"
      >
        <FormInput
          v-model="form.email"
          type="email"
          label="Email it to me"
          name="email"
          autocomplete="email"
          :error="form.errors.email"
          borders
          required
        />

        <p
          v-if="canEdit"
          class="text-sm text-grey-dark"
        >
          As this is your trip, the email will also include a private link so
          you can edit it from any device.
        </p>

        <CoeliacButton
          as="button"
          type="submit"
          theme="secondary"
          label="Send"
          :loading="form.processing"
          classes="justify-center"
        />

        <p
          v-if="sent"
          class="text-sm font-semibold text-primary-dark"
        >
          Sent! Check your inbox.
        </p>
      </form>
    </div>
  </Modal>
</template>
