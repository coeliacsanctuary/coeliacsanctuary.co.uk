<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import FormMultiSelect from '@/Components/Forms/FormMultiSelect.vue';
import {
  FormMultiSelectOption,
  FormSelectOption,
} from '@/Components/Forms/Props';
import FormStepper from '@/Components/Forms/FormStepper.vue';
import { StarIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { InertiaForm } from '@/types/Core';

const props = defineProps<{
  id: string;
  invitation: string;
  name: string;
  products: {
    id: number;
    title: string;
    image: string;
    link: string;
  }[];
}>();

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

type FormData = {
  name: string;
  whereHeard: FormMultiSelectOption[];
  products: { id: number; review: string; rating?: 1 | 2 | 3 | 4 | 5 }[];
};

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
) as InertiaForm<FormData>;

const submitForm = () => {
  form
    .transform((data: FormData) => ({
      ...data,
      whereHeard: data.whereHeard.map((whereHeard) => whereHeard.value),
    }))
    .submit();
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading>Review My Order</Heading>

    <p class="prose max-w-none sm:prose-lg">
      Thank you for your recent order, <strong v-text="id" />, I hope you
      received your order quickly, and that it met your expectations!
    </p>

    <p class="prose max-w-none sm:prose-lg">
      I'd really appreciate it if you could take a few moments of your time to
      review any products you ordered, any feedback you provide will be great
      and will help me maintain the high quality of service I try to provide,
      and even improve my products and service.
    </p>

    <p class="prose max-w-none sm:prose-lg">
      Your reviews will also be displayed on the products page so other
      potential buyers can see how other people find our products and their
      experience with them!
    </p>

    <!-- if prods > 1 -->
    <p class="prose max-w-none sm:prose-lg">
      You can leave feedback for as many or as few of the products you ordered,
      when your finished, just hit the
      <strong>"Submit My Review"</strong> button at the bottom!
    </p>

    <p class="prose max-w-none font-semibold sm:prose-lg">
      If you haven't received your order yet, please get in touch quoting your
      order number above, and I'll endeavour to rectify this. Our review
      invitation emails are automatically sent at least 10 days after your order
      was dropped in the postbox (This will vary for orders outside the UK) and
      in 99% of cases you should have received it by now, but things can
      sometimes get delayed with Royal Mail.
    </p>

    <p class="prose max-w-none sm:prose-lg">
      Thank you again, Alison - Coeliac Sanctuary Owner.
    </p>
  </Card>

  <Card class="mt-3 flex flex-col space-y-4">
    <form
      class="flex flex-col space-y-4"
      @submit.prevent="submitForm()"
    >
      <div class="flex-1">
        <FormInput
          v-model="form.name"
          name="name"
          label="Your Name"
          help-text="You can leave this blank if you'd prefer your feedback to be anonymous"
          :error="form.errors.name"
          borders
        />
      </div>

      <div class="flex-1">
        <FormMultiSelect
          v-model="form.whereHeard"
          name="where-heard"
          label="How did you hear about us?"
          :options="whereHeardOptions"
          :error="form.errors.whereHeard"
          borders
          allow-other
        />
      </div>

      <hr />

      <h3 class="text-2xl font-semibold text-primary-dark">Products</h3>

      <div
        v-for="(product, index) in products"
        :key="product.id"
        class="flex-1"
      >
        <div
          class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4"
        >
          <div class="shrink-0 sm:w-1/4">
            <img
              :src="product.image"
              :alt="product.title"
            />
          </div>

          <div class="flex flex-col space-y-2">
            <h2
              class="text-lg font-semibold text-primary-dark lg:max-xl:text-xl xl:text-2xl"
            >
              <a
                :href="product.link"
                target="_blank"
                v-text="product.title"
              />
            </h2>

            <p class="lg:text-lg">
              On a scale of 1-5 (1 being poor, 5 being excellent) How would you
              rate our {{ product.title }}?
            </p>

            <FormStepper
              v-model="form.products[index].rating"
              name="rating"
              :options="stars"
              :icon="StarIcon"
              :unselected-icon="null"
              :has-error="
                form.errors.products
                  ? !!form.errors.products[index].rating
                  : false
              "
            />

            <FormTextarea
              v-model="form.products[index].review"
              label="Please let us know below what you thought about this product, and how useful it was"
              name="review"
              :error="
                form.errors.products
                  ? form.errors.products[index].review
                  : undefined
              "
            />
          </div>
        </div>
      </div>

      <hr />

      <div class="flex justify-center">
        <CoeliacButton
          label="Submit My Review"
          as="button"
          type="submit"
          size="xxl"
          :loading="form.processing"
          :disabled="form.processing"
        />
      </div>
    </form>
  </Card>
</template>
