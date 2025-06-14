<script setup lang="ts">
import Modal from '@/Components/Overlays/Modal.vue';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import { ref } from 'vue';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';
import useUrl from '@/composables/useUrl';
import { InertiaForm } from '@/types/Core';

const props = defineProps<{
  eateryName: string;
  eateryId: number;
  branchId?: number;
  show: boolean;
}>();

const hasSubmitted = ref(false);

const emits = defineEmits(['close']);

const { generateUrl } = useUrl();

const form = useForm('post', generateUrl('report'), {
  details: '',
  branch_id: props.branchId,
}) as InertiaForm<{ details: string; branch_id?: number }>;

const close = () => {
  emits('close');

  hasSubmitted.value = false;
  form.details = '';
};

const submitForm = () => {
  form.submit({
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      hasSubmitted.value = true;
    },
  });
};
</script>

<template>
  <Modal
    :open="show"
    size="small"
    @close="close()"
  >
    <div
      class="border-grey-mid relative border-b bg-grey-light p-3 pr-[34px] text-center text-sm font-semibold"
    >
      Report {{ eateryName }}
    </div>
    <div class="p-3">
      <template v-if="hasSubmitted">
        <div class="flex items-center justify-center text-center text-green">
          <CheckCircleIcon class="h-24 w-24" />
        </div>

        <p class="md:prose-md prose mb-2 max-w-none text-center">
          Thank you for your report about <strong v-text="eateryName" />, we'll
          check it out, and if the eatery no longer qualifies, we'll remove it
          from our website!
        </p>

        <div class="mt-4 flex-1 text-center">
          <CoeliacButton
            as="button"
            type="button"
            size="xxl"
            label="Close"
            @click="close()"
          />
        </div>
      </template>

      <template v-else>
        <p class="md:prose-md prose mb-2 max-w-none">
          Has {{ eateryName }} shut down? Is it no longer offering gluten free
          options, or have there been changes in the gluten free menu?
        </p>

        <p class="mb-2">
          Whatever it is, let us know below, and we'll check it out!
        </p>

        <div class="flex flex-col">
          <div class="flex flex-col">
            <form
              class="mb-5 flex-1"
              @submit.prevent="submitForm()"
            >
              <FormTextarea
                v-model="form.details"
                label=""
                hide-label
                required
                name="details"
                :rows="5"
                :error="form.errors.details"
                borders
              />
            </form>
            <div class="flex-1 text-center">
              <CoeliacButton
                as="button"
                type="submit"
                size="lg"
                label="Report Eatery"
                :loading="form.processing"
                @click="submitForm()"
              />
            </div>
          </div>
        </div>
      </template>
    </div>
  </Modal>
</template>
