<template>
  <div
    class="-mx-6 flex flex-col space-y-2 px-6 py-2 md:flex-row md:space-y-0 md:py-0 @sm/peekable:flex-row @sm/peekable:space-y-0 @sm/peekable:py-0 @md/modal:flex-row @md/modal:space-y-0 @md/modal:py-0"
  >
    <div
      class="md:w-1/4 md:py-3 @sm/peekable:w-1/4 @sm/peekable:py-3 @md/modal:w-1/4 @md/modal:py-3"
    >
      <h4 class="font-normal @sm/peekable:break-all"><span>Shipped</span></h4>
    </div>
    <div
      class="md/modal:py-3 break-all md:w-3/4 md:py-3 lg:break-words @sm/peekable:w-3/4 @sm/peekable:py-3 @md/modal:w-3/4 @md/peekable:break-words @lg/modal:break-words"
    >
      <div class="flex flex-col space-y-6">
        <template v-if="currentState !== states.CANCELLED">
          <span v-if="currentState === states.PAID"
            >No, order not printed yet</span
          >

          <Button
            v-else-if="currentState === states.READY"
            class="w-[160px] bg-green-500 hover:bg-green-700"
            size="small"
            @click.stop="handleShipOrder()"
          >
            Mark As Shipped
          </Button>

          <span v-if="currentState === states.SHIPPED">
            Yes, {{ fieldValue.shipped_at }}
          </span>

          <Button
            v-if="currentState !== states.SHIPPED"
            class="w-[160px] bg-red-500 hover:bg-red-700"
            size="small"
            @click.stop="confirmCancelOrder()"
          >
            Cancel and Refund Order
          </Button>
        </template>

        <span
          v-else
          class="font-semibold text-red-500"
        >
          Order Cancelled
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },

  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  data: () => ({
    working: false,
  }),

  computed: {
    fieldValue() {
      return this.field.displayedAs || this.field.value;
    },

    currentState() {
      return this.fieldValue.state_id;
    },

    states() {
      return {
        PAID: 3,
        READY: 4,
        SHIPPED: 5,
        REFUNDED: 6,
        CANCELLED: 7,
      };
    },
  },

  methods: {
    confirmCancelOrder() {
      document
        .querySelector(
          `button[dusk="${this.fieldValue.parent_id}-control-selector"]`,
        )
        .click();

      setTimeout(() => {
        document.querySelector(`button[data-action-id="refund-order"]`).click();

        setTimeout(() => {
          document.querySelector('#cancel-default-boolean-field').click();
        }, 100);
      }, 100);
    },

    handleShipOrder() {
      Nova.$progress.start();

      const data = new FormData();

      data.append('resources', this.fieldValue.parent_id);

      Nova.request({
        method: 'post',
        url: '/nova-api/orders/action',
        params: this.actionQueryString('ship-order'),
        data,
        responseType: 'json',
      })
        .then(async (response) => {
          this.showCancelModal = false;
          this.$emit('actionExecuted');
          Nova.$emit('refresh-resources');
        })
        .finally(() => {
          Nova.$progress.done();
        });
    },

    actionQueryString(action) {
      return {
        action,
        pivotAction: false,
        search: '',
        filters: {},
        trashed: '',
      };
    },
  },
};
</script>

<style scoped>
.bg-green-500 {
  background-color: #22c55e;
}

.hover\:bg-green-700:hover {
  background-color: #15803d;
}

.hover\:bg-red-700:hover {
  background-color: #b91c1c;
}

.w-\[160px\] {
  width: 160px;
}
</style>
