<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import { InertiaForm } from '@/types/Core';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormLookup from '@/Components/Forms/FormLookup.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';

const form = useForm('get', '/gluten-free-travel-translation-cards', {
  term: '',
}) as InertiaForm<{ term: string }>;
</script>

<template>
  <Card class="mt-3 flex flex-col items-center gap-4">
    <SubHeading>Not sure which card you need?</SubHeading>

    <p class="prose prose-xl">
      Enter a country or language below and we'll try and find the best travel
      card for you!
    </p>

    <FormInput
      label=""
      name="term"
      placeholder="Search for country or language"
      size="large"
      hide-label
      borders
      class="w-full"
      input-classes="text-2xl!  p-4! text-center"
      v-model="form.term"
      @keyup.enter="form.submit()"
    />

    <CoeliacButton
      label="Search"
      size="xxl"
      @click="form.submit()"
    />
  </Card>
</template>
