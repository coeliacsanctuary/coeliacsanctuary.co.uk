<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { useForm } from '@inertiajs/vue3';
import { FormSelectOption } from '@/Components/Forms/Props';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { MagnifyingGlassIcon, MapPinIcon } from '@heroicons/vue/20/solid';
import useScreensize from '@/composables/useScreensize';
import useGeolocation from '@/composables/useGeolocation';

type Range = 1 | 2 | 5 | 10 | 20;

const props = withDefaults(defineProps<{ term?: string; range?: Range }>(), {
  term: '',
  range: 2,
});

const form = useForm<{ term: string; latlng: string; range: Range }>({
  term: props.term,
  latlng: '',
  range: props.range,
});

const { isSupported, isLocating, locate, errorMessage } = useGeolocation();

const rangeOptions: FormSelectOption[] = [
  { label: 'within 1 mile', value: 1 },
  { label: 'within 2 miles', value: 2 },
  { label: 'within 5 miles', value: 5 },
  { label: 'within 10 miles', value: 10 },
  { label: 'within 20 miles', value: 20 },
];

const postSearch = () => {
  form.clearErrors('term');

  form.post('/wheretoeat/search', {
    preserveState: false,
    preserveScroll: true,
  });
};

const submitSearch = () => {
  if (form.term.length < 3) {
    form.setError('term', 'Please enter at least 3 characters...');

    return;
  }

  form.latlng = '';

  postSearch();
};

const searchFromCurrentLocation = async () => {
  const coords = await locate();

  if (!coords) {
    form.setError('term', errorMessage() as string);

    return;
  }

  form.term = '';
  form.latlng = `${coords.lat},${coords.lng}`;

  postSearch();
};
</script>

<template>
  <Card class="flex flex-col space-y-3 bg-primary-light/50!">
    <p
      class="font-weight-bold prose-xl max-w-none text-center font-semibold md:prose-2xl"
    >
      Looking for somewhere specific? Search by postcode or town below to get
      places to eat near you!
    </p>

    <CoeliacButton
      v-if="isSupported"
      as="button"
      type="button"
      :icon="MapPinIcon"
      :loading="isLocating"
      label="Use my current location"
      size="sm"
      theme="secondary"
      classes="self-center"
      @click="searchFromCurrentLocation()"
    />

    <form
      class="flex flex-col gap-2 sm:flex-row"
      @submit.prevent="submitSearch()"
    >
      <FormInput
        v-model="form.term"
        type="search"
        label=""
        placeholder="Search..."
        name="term"
        hide-label
        class="flex-1"
        size="large"
        input-classes="p-2!"
        :error="form.errors.term"
        error-classes="absolute font-semibold"
      />

      <FormSelect
        v-model="form.range"
        name="range"
        :options="rangeOptions"
        class="flex-1"
        size="large"
        input-classes="p-2!"
      />

      <CoeliacButton
        type="submit"
        as="button"
        :icon="MagnifyingGlassIcon"
        :loading="form.processing"
        :icon-only="useScreensize().screenIsGreaterThanOrEqualTo('sm')"
        label="Search"
        size="lg"
        classes="text-2xl!"
        icon-position="center"
        icon-classes="size-7!"
      />
    </form>
  </Card>
</template>
