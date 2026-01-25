<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';

defineProps<{
  expires: string;
  order: {
    number: number;
    date: string;
    name: string;
  };
  items: {
    id: number;
    title: string;
    image: string;
    variant_title: string;
    variant_description?: string;
    add_on_name: string;
    add_on_description: string;
    download_link: string;
  }[];
}>();

const downloadAll = () => {
  document
    .querySelectorAll<HTMLAnchorElement>('.js-download-link')
    .forEach((link) => {
      link.click();
    });
};
</script>

<template>
  <Card class="mx-auto mt-3 flex w-full max-w-4xl flex-col space-y-4">
    <Heading> Download my Products! </Heading>

    <div class="flex flex-col xs:flex-row xs:justify-between">
      <div
        class="flex items-center justify-between xs:justify-normal xs:space-x-2"
      >
        <span class="font-semibold"> Order Number: </span>
        <span v-text="order.number" />
      </div>
      <div
        class="flex items-center justify-between xs:justify-normal xs:space-x-2"
      >
        <span class="font-semibold"> Order Date: </span>
        <span v-text="order.date" />
      </div>
    </div>

    <p class="wax-w-none prose-lg">
      Hi {{ order.name }}, thank you for your order! You can download your
      products below.
    </p>

    <p class="prose-sm max-w-none">
      Please note this link will expire on {{ expires }}. If you need to access
      your products again you can contact us with your order details to get a
      new link.
    </p>
  </Card>

  <Card
    v-if="items.length > 1"
    class="mx-auto flex w-full max-w-4xl !bg-transparent sm:justify-end"
    no-flex
    :shadow="false"
  >
    <div>
      <CoeliacButton
        as="button"
        label="Download All"
        theme="secondary"
        size="xl"
        bold
        @click="downloadAll()"
      />
    </div>
  </Card>

  <Card
    v-for="item in items"
    :key="item.id"
    class="mx-auto flex w-full max-w-4xl flex-col space-y-4 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4"
  >
    <div class="sm:w-1/4 sm:max-w-[200px]">
      <img
        :src="item.image"
        alt=""
      />
    </div>

    <div
      class="flex w-full flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 sm:space-x-4"
    >
      <div class="flex flex-col space-y-2 sm:flex-1">
        <h2 class="text-center text-xl font-semibold sm:text-left md:text-2xl">
          {{ item.title }}
          <template v-if="item.variant_title !== ''">
            - {{ item.variant_title }}
          </template>
        </h2>
        <h3
          v-if="item.variant_description"
          class="text-center font-semibold sm:text-left"
          v-html="item.variant_description"
        />
      </div>

      <div class="flex justify-center">
        <CoeliacButton
          as="a"
          :href="item.download_link"
          label="Download"
          theme="secondary"
          size="xl"
          bold
          classes="js-download-link"
        />
      </div>
    </div>
  </Card>
</template>
