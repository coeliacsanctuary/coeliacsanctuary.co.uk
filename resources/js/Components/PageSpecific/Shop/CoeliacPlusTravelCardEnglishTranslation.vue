<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/outline';
import Card from '@/Components/Card.vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';

withDefaults(defineProps<{ product: string; canNotEat?: string }>(), {
  canNotEat: '',
});
</script>

<template>
  <div class="relative -mt-1 flex flex-col space-y-3 px-3">
    <Card>
      <p class="prose prose-lg max-w-none">
        If you’re wondering what exactly our <strong v-text="product" /> card
        says in English, you’re in the right place. We’ve included the full
        English translation of the card here so you can see exactly what
        information it shares. It’s especially useful if you’re giving this to
        someone else or want to double-check what’s being communicated before
        you travel.
      </p>
    </Card>
  </div>

  <div class="mx-auto max-w-2xl px-3">
    <TabGroup>
      <TabList class="my-3 flex justify-center space-x-5">
        <Tab
          v-slot="{ selected }"
          as="template"
        >
          <CoeliacButton
            as="button"
            label="Front"
            classes="px-10 outline-none text-white !text-lg"
            :theme="selected ? 'primary' : 'light'"
            bold
          />
        </Tab>

        <Tab
          v-slot="{ selected }"
          as="template"
        >
          <CoeliacButton
            as="button"
            label="Back"
            classes="px-10 outline-none text-white !text-lg"
            :theme="selected ? 'primary' : 'light'"
            bold
          />
        </Tab>
      </TabList>

      <TabPanels class="mt-2">
        <TabPanel>
          <div class="mx-auto my-3 max-w-2xl shadow">
            <div class="bg-secondary py-4 text-center text-2xl">
              Gluten Free Travel Card
            </div>
            <div
              class="flex justify-between bg-secondary-light px-4 py-[2px] text-xs"
            >
              <span>English</span>
              <span>www.coeliacsanctuary.co.uk</span>
            </div>
            <div class="flex flex-col space-y-3 bg-white px-4 py-2">
              <p class="prose-md prose max-w-none">
                I have Coeliac Disease and cannot eat food containing gluten or
                food that has been cooked with gluten, even small amounts make
                me ill.
              </p>

              <div class="flex">
                <div class="w-8">
                  <div
                    class="size-6 rounded-full border-2 border-red bg-gradient-to-br from-red/80 to-red/40 text-white"
                  >
                    <XMarkIcon />
                  </div>
                </div>

                <div>
                  <p class="prose-md prose max-w-none">
                    <span class="font-semibold text-red">I can not eat</span>
                    Wheat, durum, semolina, couscous, bulgur, rye, barley,
                    oats{{ canNotEat ? `, ${canNotEat}` : '' }}
                  </p>
                </div>
              </div>

              <p class="prose-md prose max-w-none">
                I also have other dietary requirements, please see the back of
                this card to ensure my food is free from anything else I cannot
                eat.
              </p>

              <p class="prose-md prose max-w-none">
                Thank you for helping me eat safely.
              </p>
            </div>
          </div>
        </TabPanel>

        <TabPanel>
          <div class="mx-auto my-3 max-w-2xl shadow">
            <div class="flex flex-col space-y-3 bg-white px-4 py-2">
              <p class="prose-md prose max-w-none">
                All boxes marked with an "x" I CANNOT eat due to allergies.
              </p>

              <div class="grid grid-cols-2 xs:grid-cols-3">
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Celery</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Crustaceans</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Dairy</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Eggs</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Fish</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Lupin</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Molluscs</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Mustard</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Nuts</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Peanuts</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Sesame</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Soya</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Sulphites</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Wheat*</span>
                </div>
              </div>

              <p class="prose-md prose max-w-none">
                *Wheat allergy as well as Coeliac Disease, no gluten free wheat
                starch
              </p>

              <p class="prose-md prose max-w-none">
                I also have these dietary requirements
              </p>

              <div class="grid grid-cols-2 xs:grid-cols-3">
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Diabetic Friendly</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Vegan</span>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="size-4 rounded-md border-2 border-primary" />
                  <span>Vegetarian</span>
                </div>
              </div>
            </div>
          </div>
        </TabPanel>
      </TabPanels>
    </TabGroup>
  </div>
</template>
