<template>
  <div>
    <Head title="Order Dispatch Slip" />

    <Heading class="mb-6">Order Dispatch Slip</Heading>

    <Card class="flex flex-col space-y-4 p-8">
      <p class="text-lg font-semibold text-red-500">
        Do NOT use any normal print methods, use the big button below to print
        the dispatch slips!! Do NOT press print until the red box below
        disappears!!
      </p>

      <Button
        class="h-16 text-3xl"
        style="font-size: 1.5rem !important; height: 3rem !important"
        @click.prevent.stop="print()"
      >
        Print
      </Button>

      <iframe
        id="iFramePdf"
        :src="frameSrc"
        class="w-full border border-black bg-red-500"
        style="height: 800px"
      ></iframe>
    </Card>
  </div>
</template>

<script>
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },
  props: {
    orders: {
      type: Array,
      required: true,
    },
    id: {
      required: true,
      type: String,
    },
  },

  computed: {
    frameSrc() {
      return '/cs-adm/order-dispatch-slip/render/' + this.id;
    },
  },

  methods: {
    print() {
      Nova.request()
        .post('/nova-vendor/order-dispatch-slip/print', {
          ids: this.id,
        })
        .then(() => {
          const elem = document.getElementById('iFramePdf');

          elem.focus();
          elem.contentWindow.print();
        });
    },
  },
};
</script>
