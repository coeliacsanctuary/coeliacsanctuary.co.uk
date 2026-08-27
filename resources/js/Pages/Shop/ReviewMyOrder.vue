<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import SubHeading from '@/Components/SubHeading.vue';
import Info from '@/Components/Info.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import FormMultiSelect from '@/Components/Forms/FormMultiSelect.vue';
import {
  FormMultiSelectOption,
  FormSelectOption,
} from '@/Components/Forms/Props';
import FormStepper from '@/Components/Forms/FormStepper.vue';
import { StarIcon } from '@heroicons/vue/24/solid';
import {
  CheckIcon,
  MinusIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';

const props = defineProps<{
  id: string;
  invitation: string;
  name: string;
  products: {
    id: number;
    title: string;
    variants: string[];
    image: string | null;
    link: string | null;
  }[];
}>();

const characterLimit = 1000;

const whereHeardOptions: FormMultiSelectOption[] = [
  { value: 'facebook', label: 'Facebook' },
  { value: 'instagram', label: 'Instagram' },
  { value: 'word-of-mouth', label: 'Word of Mouth' },
  { value: 'website', label: 'Coeliac Sanctuary Website' },
  { value: 'newsletter', label: 'Coeliac Sanctuary Newsletter' },
  { value: 'google', label: 'Google' },
  { value: 'search', label: 'Another Search Engine' },
  { value: 'blogger', label: 'A Gluten Free Website / Blogger' },
  { value: 'show', label: 'Allergy Show / Food Fair' },
];

const stars = computed((): FormSelectOption[] => [
  { value: 1, label: 'Poor' },
  { value: 2, label: 'Average' },
  { value: 3, label: 'OK' },
  { value: 4, label: 'Good' },
  { value: 5, label: 'Excellent' },
]);

type ProductEntry = {
  id: number;
  review: string;
  rating?: 1 | 2 | 3 | 4 | 5;
};

type FormData = {
  name: string;
  whereHeard: FormMultiSelectOption[];
  products: ProductEntry[];
};

const skipped = ref<boolean[]>(props.products.map(() => false));

const form = useForm<FormData>(
  'post',
  `/shop/review-my-order/${props.invitation}`,
  {
    name: props.name,
    whereHeard: [],
    products: props.products.map((product) => ({
      id: product.id,
      review: '',
      rating: undefined,
    })),
  },
);

const isRated = (index: number): boolean =>
  !skipped.value[index] && form.products[index].rating !== undefined;

const ratedCount = computed(
  (): number =>
    props.products.filter((product, index) => isRated(index)).length,
);

const submittedProducts = (): ProductEntry[] =>
  form.products.filter((product, index) => isRated(index));

const productErrors = computed((): Record<string, Record<string, string>> => {
  const errors = form.errors as unknown as {
    products?: Record<string, Record<string, string>>;
  };

  return errors.products ?? {};
});

const errorFor = (index: number, field: string): string | undefined => {
  const submittedIndex = submittedProducts().findIndex(
    (product) => product.id === props.products[index].id,
  );

  if (submittedIndex === -1) {
    return undefined;
  }

  return productErrors.value[submittedIndex]?.[field];
};

const stringError = (error: unknown): string | undefined =>
  typeof error === 'string' ? error : undefined;

const toggleSkipped = (index: number): void => {
  skipped.value[index] = !skipped.value[index];

  if (skipped.value[index]) {
    form.products[index].rating = undefined;
    form.products[index].review = '';
  }
};

const markerClasses = (index: number): string => {
  if (isRated(index)) {
    return 'border-primary-dark bg-primary-dark text-white';
  }

  return 'border-grey-off text-grey-off';
};

const submitForm = () => {
  form
    .transform((data: FormData) => ({
      ...data,
      whereHeard: data.whereHeard.map((whereHeard) => whereHeard.value),
      products: submittedProducts(),
    }))
    .submit();
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading>Review My Order</Heading>

    <p class="prose max-w-none md:prose-lg">
      Thank you for your recent order, <strong v-text="id" /> — I'd really
      appreciate it if you could take a few moments to tell me what you thought.
      Your feedback goes on the product pages to help other people decide, and
      it helps me improve what I sell.
    </p>

    <Info>
      <p class="text-sm font-semibold text-grey-darkest">
        Haven't received your order yet?
      </p>

      <p class="mt-1 text-sm text-grey-dark">
        Please get in touch quoting your order number above and I'll do my best
        to sort it out. My review invitations are sent at least 10 days after
        your order was dropped in the postbox — longer for orders outside the UK
        — so in almost every case it will have arrived by now, but things can
        occasionally get delayed with Royal Mail.
      </p>
    </Info>
  </Card>

  <form
    class="flex flex-col space-y-4"
    @submit.prevent="submitForm()"
  >
    <Card class="flex flex-col space-y-4">
      <div
        class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1"
      >
        <SubHeading
          as="h2"
          text-size="small"
        >
          Your products
        </SubHeading>

        <p class="text-sm font-semibold text-grey-dark">
          {{ ratedCount }} of {{ products.length }} reviewed
        </p>
      </div>

      <div
        class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
      />

      <p class="text-sm text-grey-dark">
        Review as many or as few as you like — skip anything you'd rather not
        rate.
      </p>

      <div
        v-for="(product, index) in products"
        :key="product.id"
        class="flex flex-col gap-4 rounded-sm border p-4 transition-colors"
        :class="
          isRated(index)
            ? 'border-primary-light/60 bg-primary-lightest/50'
            : 'border-grey-off-light'
        "
      >
        <div class="flex gap-3 sm:gap-4">
          <span
            class="flex size-8 shrink-0 items-center justify-center rounded-full border-2 font-semibold transition-colors"
            :class="markerClasses(index)"
          >
            <CheckIcon
              v-if="isRated(index)"
              class="size-5"
            />

            <MinusIcon
              v-else-if="skipped[index]"
              class="size-5"
            />

            <template v-else>{{ index + 1 }}</template>
          </span>

          <div
            class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-primary-light/60 bg-primary-lightest/60 sm:size-24"
          >
            <img
              v-if="product.image"
              :src="product.image"
              :alt="product.title"
              loading="lazy"
              class="h-full w-full object-cover object-center"
            />

            <ShoppingBagIcon
              v-else
              class="size-8 text-primary"
            />
          </div>

          <div class="flex flex-1 flex-col gap-1">
            <h3 class="text-lg font-semibold lg:text-xl">
              <a
                v-if="product.link"
                :href="product.link"
                target="_blank"
                class="hover:text-primary-dark"
                v-text="product.title"
              />

              <span
                v-else
                v-text="product.title"
              />
            </h3>

            <p
              v-if="product.variants.length"
              class="text-sm text-grey-darker"
              v-text="product.variants.join(', ')"
            />

            <button
              type="button"
              class="mt-auto self-start text-sm font-semibold text-grey-dark underline underline-offset-2 hover:text-primary-dark"
              @click="toggleSkipped(index)"
              v-text="
                skipped[index] ? 'Review this product' : 'Skip this product'
              "
            />
          </div>
        </div>

        <template v-if="!skipped[index]">
          <div class="flex flex-col gap-2">
            <p class="font-semibold text-primary-dark">
              How would you rate it?
            </p>

            <FormStepper
              v-model="form.products[index].rating"
              name="rating"
              :options="stars"
              :icon="StarIcon"
              :unselected-icon="null"
              icon-classes="h-10 w-10"
              :has-error="!!errorFor(index, 'rating')"
            />
          </div>

          <div class="flex flex-col gap-1">
            <FormTextarea
              v-model="form.products[index].review"
              label="Your review — optional"
              help-text="Let me know what you thought of it, and how useful you found it."
              name="review"
              :rows="4"
              :max="characterLimit"
              :error="errorFor(index, 'review')"
              borders
            />

            <p class="self-end text-xs text-grey-dark">
              {{ form.products[index].review.length }} / {{ characterLimit }}
            </p>
          </div>
        </template>
      </div>
    </Card>

    <Card class="flex flex-col space-y-4">
      <SubHeading
        as="h2"
        text-size="small"
      >
        About you
      </SubHeading>

      <div
        class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
      />

      <FormInput
        v-model="form.name"
        name="name"
        label="Your Name"
        help-text="You can leave this blank if you'd prefer your feedback to be anonymous"
        :error="stringError(form.errors.name)"
        borders
      />

      <FormMultiSelect
        v-model="form.whereHeard"
        name="where-heard"
        label="How did you hear about me?"
        :options="whereHeardOptions"
        :error="stringError(form.errors.whereHeard)"
        borders
        allow-other
      />

      <div class="flex flex-col items-center gap-2 pt-2">
        <CoeliacButton
          label="Submit My Review"
          as="button"
          type="submit"
          theme="secondary"
          size="xxl"
          :loading="form.processing"
          :disabled="form.processing || ratedCount === 0"
        />

        <p
          v-if="ratedCount === 0"
          class="text-sm text-grey-dark"
        >
          Rate at least one product to submit your review.
        </p>
      </div>
    </Card>
  </form>
</template>
