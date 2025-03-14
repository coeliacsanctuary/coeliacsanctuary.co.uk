<script lang="ts" setup>
import { DetailedEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import { computed, ComputedRef, Ref, ref } from 'vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import FormStepper from '@/Components/Forms/FormStepper.vue';
import { CurrencyPoundIcon, StarIcon } from '@heroicons/vue/24/solid';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';
import { FormSelectOption } from '@/Components/Forms/Props';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import UploadReviewImages from '@/Components/PageSpecific/EatingOut/Details/Reviews/UploadReviewImages.vue';
import useUrl from '@/composables/useUrl';
import { InertiaForm } from '@/types/Core';
import FormLookup from '@/Components/Forms/FormLookup.vue';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const { generateUrl } = useUrl();

type ReviewFormPayload = {
  method: 'website';
  name: string;
  email: string;
  rating: 0 | 1 | 2 | 3 | 4 | 5;
  review: string;
  food_rating: string;
  service_rating: string;
  how_expensive: string;
  images: string[];
  branch_name?: string;
  admin_review?: boolean;
};

const isAdmin = (): boolean =>
  // @todo
  false;

const branchName = (): string => {
  if (props.eatery.branch?.name) {
    return props.eatery.branch.name;
  }

  return '';
};

const form = useForm<ReviewFormPayload>('post', generateUrl('reviews'), {
  method: 'website',
  name: '',
  email: '',
  rating: 0,
  review: '',
  food_rating: '',
  service_rating: '',
  how_expensive: '',
  images: [],
  ...(isAdmin() ? { admin_review: false } : null),
  ...(props.eatery.county.id === 1 ? { branch_name: branchName() } : null),
}) as InertiaForm<ReviewFormPayload>;

const characterLimit = 1500;

const hasSubmitted = ref(false);

const stars = computed((): FormSelectOption[] => [
  { value: 1 },
  { value: 2 },
  { value: 3 },
  { value: 4 },
  { value: 5 },
]);

const ratings = computed((): FormSelectOption[] => [
  { value: 'poor', label: 'Poor' },
  { value: 'good', label: 'Good' },
  { value: 'excellent', label: 'Excellent' },
]);

const howExpensiveValues = computed((): FormSelectOption[] => [
  { value: 1, label: 'Cheap and quick' },
  { value: 2, label: 'Great Value' },
  { value: 3, label: 'Average' },
  { value: 4, label: 'A special treat' },
  { value: 5, label: 'Expensive' },
]);

const usedCharacters: ComputedRef<number> = computed(
  () => form.review.length || 0,
);

const submitRating = () => {
  form.submit({
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      hasSubmitted.value = true;
    },
  });
};

const imageUploadError: Ref<string | false> = ref(false);

const imagesUploaded = (images: string[]): void => {
  imageUploadError.value = false;

  if (!form.images) {
    return;
  }

  form.images = images;
};

const imageError = (message: string): void => {
  imageUploadError.value = message;
};
</script>

<template>
  <Card
    class="space-y-3"
    :shadow="false"
  >
    <div
      v-if="hasSubmitted"
      class="py-8"
    >
      <div class="mb-8 flex justify-center text-green">
        <CheckCircleIcon class="h-24 w-24" />
      </div>

      <p class="text-center text-xl">
        Thank you for leaving your
        <strong v-text="form.rating + ' star'" /> rating and review of
        <strong v-text="eatery.name" />! Your review will be checked by an admin
        before it goes live.
      </p>
    </div>

    <template v-else>
      <p class="prose md:max-md:prose-lg lg:max-xl:prose-xl xl:prose-2xl">
        Have you visited <strong v-text="eatery.name" />? Let other people know
        your experience by leaving a review!
      </p>

      <div class="flex flex-col space-y-4">
        <div class="flex-1">
          <FormInput
            v-model="form.name"
            required
            name="name"
            label="Your Name"
            :error="form.errors.name"
            borders
          />
        </div>
        <div class="flex-1">
          <FormInput
            v-model="form.email"
            required
            name="email"
            type="email"
            label="Your Email"
            :error="form.errors.email"
            borders
          />
        </div>
        <div class="flex-1">
          <FormStepper
            v-model="form.rating"
            label="Overall Rating"
            name="rating"
            :options="stars"
            :icon="StarIcon"
            :unselected-icon="null"
            icon-classes="w-10 h-10"
            hide-options-text
          />
          <p
            v-if="form.errors.rating"
            class="mt-2 text-sm text-red"
            v-text="form.errors.rating"
          />
        </div>
        <div class="flex-1">
          <FormStepper
            v-model="form.food_rating"
            name="food"
            label="How would you rate your food?"
            :options="ratings"
          />
        </div>
        <div class="flex-1">
          <FormStepper
            v-model="form.service_rating"
            name="service"
            label="How would you rate the service?"
            :options="ratings"
          />
        </div>
        <div class="flex-1">
          <FormStepper
            v-model="form.how_expensive"
            name="expense"
            label="How expensive is it to eat here?"
            :options="howExpensiveValues"
            :icon="CurrencyPoundIcon"
            :unselected-icon="null"
          />
        </div>
        <div
          v-if="eatery.county.id === 1 && !eatery.branch"
          class="flex-1"
        >
          <input
            v-model="form.branch_name"
            type="hidden"
            name="branchName"
          />

          <FormLookup
            ref="lookup"
            label="What branch did you eat at?"
            :error="form.errors.branch_name"
            name=""
            borders
            :lookup-endpoint="`/api/wheretoeat/${eatery.id}/branches`"
            :preselect-term="form.branch_name"
            :lock="form.branch_name !== ''"
            allow-any
            fallback-key="name"
            @unlock="form.branch_name = ''"
          >
            <template #item="{ name }">
              <div
                class="p-2 border-b border-grey-off transition cursor-pointer hover:bg-grey-lightest"
                @click="form.branch_name = name"
                v-html="name"
              />
            </template>
          </FormLookup>
        </div>
        <div class="flex-1">
          <FormTextarea
            v-model="form.review"
            required
            name="comment"
            :rows="5"
            label="Your Comment"
            v-bind="isAdmin() ? {} : { max: 1500 }"
            :error="form.errors.review"
            borders
          />
          <span
            class="text-right text-xs"
            :class="
              characterLimit === usedCharacters ? 'font-semibold text-red' : ''
            "
          >
            {{ usedCharacters }} / {{ characterLimit }}
          </span>
        </div>

        <div
          v-if="isAdmin()"
          class="flex-1"
        >
          <FormCheckbox
            v-model="form.admin_review"
            name="admin_review"
            label="Post as admin review"
          />
        </div>

        <div>
          <UploadReviewImages
            @images-change="imagesUploaded"
            @error="imageError"
          />

          <div
            v-if="imageUploadError"
            class="font-semibold text-red"
            v-text="imageUploadError"
          />
        </div>

        <div class="flex-1 text-center">
          <CoeliacButton
            theme="primary"
            size="lg"
            as="button"
            label="Submit Review"
            bold
            type="submit"
            :loading="form.processing"
            @click="submitRating()"
          />
        </div>

        <p class="text-sm">
          We require your email in case we need to contact you about your
          comment, your email address is NEVER displayed publicly with your
          comment.
        </p>

        <p class="text-sm">
          All comments need to be approved before they are shown on the website,
          this usually takes no longer than 48 hours.
        </p>
      </div>
    </template>
  </Card>
</template>
