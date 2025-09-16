<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import useLocalStorage from '@/composables/useLocalStorage';
import { Link } from '@inertiajs/vue3';
import { OrderCompleteProps } from '@/types/Shop';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import Heading from '@/Components/Heading.vue';
import useScreensize from '@/composables/useScreensize';
import useGoogleEvents from '@/composables/useGoogleEvents';
import SubHeading from '@/Components/SubHeading.vue';

const props = defineProps<{ order: OrderCompleteProps }>();

const { removeFromLocalStorage } = useLocalStorage();

removeFromLocalStorage('checkout-form');
removeFromLocalStorage('checkout-steps');
removeFromLocalStorage('checkout-active-section');

if (typeof document !== 'undefined') {
  document.cookie =
    'basket_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}

useGoogleEvents().googleEvent('event', 'purchase', props.order.event);
</script>

<template>
  <Card
    theme="transparent"
    :no-padding="useScreensize().screenIsLessThan('md')"
    :shadow="false"
  >
    <div class="mx-auto flex max-w-3xl flex-col space-y-4 bg-white p-4">
      <div class="flex flex-col space-y-4">
        <div class="flex flex-col space-y-2">
          <Heading :border="false"> Thanks for ordering! </Heading>

          <SubHeading
            text-size="xs"
            classes="!text-center mt-2"
          >
            Your payment was successful.
          </SubHeading>

          <p class="prose prose-lg mt-4 max-w-none text-base">
            Your Order has been completed, you will receive an email
            confirmation shortly. If you don't receive a confirmation email
            please check your Spam or Junk folders. If you still haven't
            received it please get in touch.
          </p>

          <p
            v-if="order.has_digital_products"
            class="prose prose-lg mt-4 max-w-none text-center text-xl font-semibold"
          >
            You will receive a separate email with a link to download your
            digital products.
          </p>
        </div>

        <dl class="flex space-x-2 font-semibold">
          <dt class="text-gray-900">Order Number</dt>
          <dd
            class="text-primary-dark"
            v-text="order.id"
          />
        </dl>
      </div>

      <ul
        role="list"
        class="divide-y divide-gray-200 border-t border-secondary"
      >
        <li
          v-for="product in order.products"
          :key="product.id"
          class="flex space-x-6 py-6 last:pb-0"
        >
          <img
            :src="product.image"
            :alt="product.title"
            class="h-24 w-24 flex-none rounded-md object-cover object-center"
          />
          <div class="flex-auto space-y-1">
            <h3
              class="font-semibold text-primary-dark md:max-lg:text-lg lg:text-xl"
            >
              <Link
                :href="product.link"
                target="_blank"
              >
                {{ product.title }}

                <template v-if="product.variant">
                  - {{ product.variant }}
                </template>
              </Link>
            </h3>

            <p
              v-if="product.description"
              class="text-grey-darker"
              v-text="product.description"
            />

            <p
              v-if="product.variant_type !== 'digital'"
              class="text-grey-darker"
            >
              Quantity: {{ product.quantity }}
            </p>
          </div>
          <p
            class="flex-none font-semibold"
            v-text="product.line_price"
          />
        </li>
      </ul>

      <dl
        v-if="
          !order.is_digital_only || (order.is_digital_only && order.discount)
        "
        class="space-y-2 border-t border-secondary pt-3"
      >
        <div class="flex justify-between">
          <dt class="font-semibold">Subtotal</dt>
          <dd v-text="order.subtotal" />
        </div>

        <div
          v-if="order.discount"
          class="flex justify-between"
        >
          <dt class="font-semibold">
            Discount<br />
            <em
              class="font-normal"
              v-text="order.discount.name"
            />
          </dt>
          <dd v-text="`-${order.discount.amount}`" />
        </div>

        <div
          v-if="!order.is_digital_only"
          class="flex justify-between"
        >
          <dt class="font-semibold">Postage</dt>
          <dd v-text="order.postage" />
        </div>

        <div
          class="flex items-center justify-between border-t border-secondary pt-4 text-xl font-semibold"
        >
          <dt>Total</dt>
          <dd v-text="order.total" />
        </div>
      </dl>

      <dl
        class="grid gap-4 border-t border-secondary pt-4 xs:grid-cols-2"
        :class="order.is_digital_only ? 'xs:grid-cols-4' : ''"
      >
        <div v-if="!order.is_digital_only">
          <dt class="text-lg font-semibold text-primary-dark">
            Shipping Address
          </dt>
          <dd class="mt-2">
            <address class="not-italic">
              <span
                v-for="line in order.shipping"
                :key="line"
                class="block leading-relaxed"
                v-text="line"
              />
            </address>
          </dd>
        </div>

        <div
          :class="order.is_digital_only ? 'xs:col-span-2 xs:col-start-2' : ''"
        >
          <dt class="text-lg font-semibold text-primary-dark">
            Payment Information
          </dt>
          <dd class="space-y-1 sm:flex sm:space-y-0 sm:space-x-4">
            <div class="mt-2 grid flex-auto grid-cols-3 gap-2">
              <template v-if="order.payment.type !== 'PayPal'">
                <div class="col-span-2 font-semibold">Payment Method</div>
                <div
                  class="text-right"
                  v-text="order.payment.type"
                />

                <template v-if="order.payment.lastDigits">
                  <div class="col-span-2 font-semibold">Ending With</div>
                  <div
                    class="text-right"
                    v-text="order.payment.lastDigits"
                  />
                </template>

                <template v-if="order.payment.expiry">
                  <div class="col-span-2 font-semibold">Expires</div>
                  <div
                    class="text-right"
                    v-text="order.payment.expiry"
                  />
                </template>
              </template>

              <template v-else-if="order.payment.type === 'PayPal'">
                <div class="col-span-2 font-semibold">Payment Method</div>
                <div class="text-right">PayPal</div>

                <template v-if="order.payment.paypalAccount">
                  <div class="font-semibold">Payment Account</div>
                  <div
                    class="col-span-2 text-right"
                    v-text="order.payment.paypalAccount"
                  />
                </template>
              </template>
            </div>
          </dd>
        </div>
      </dl>

      <div class="border-t border-secondary py-4 text-center">
        <CoeliacButton
          :as="Link"
          href="/"
          theme="secondary"
          size="xxl"
          label="Back Home"
        />
      </div>
    </div>
  </Card>
</template>
