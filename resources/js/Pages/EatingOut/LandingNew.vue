<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Icon from '@/Components/Icon.vue';
import LocationSearch from '@/Components/PageSpecific/EatingOut/LocationSearch.vue';
import Heading from '@/Components/Heading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import SubHeading from '@/Components/SubHeading.vue';
</script>

<template>
  <Card class="flex flex-col space-y-4">
    <Heading :border="false"> Gluten Free Places to Eat and Visit </Heading>

    <p class="prose prose-lg max-w-none lg:prose-xl">
      Finding safe gluten free places to eat can be a challenge when you have
      coeliac disease, especially when cross contamination is just as important
      as the ingredients themselves. While more venues now offer gluten free
      options, not all of them provide the same level of understanding,
      procedures, or choice.
    </p>
    <p class="prose prose-lg max-w-none lg:prose-xl">
      Whether you’re looking for somewhere nearby or planning ahead, we’ve made
      it easier to discover trusted eateries across the UK and Ireland. Search
      by town or postcode to find places near you, explore our interactive map,
      or browse our structured guide and curated collections to find somewhere
      you can eat with confidence.
    </p>
  </Card>

  <LocationSearch />

  <div class="px-4 xl:px-0">
    <Card class="relative overflow-hidden">
      <div
        class="absolute top-0 left-0 h-full w-full bg-secondary/20 xmd:bg-gradient-to-bl xmd:from-white xmd:to-secondary/5"
      >
        <img
          src="/images/misc/eating-out-map-colour.png"
          alt=""
          class="h-full w-full object-cover object-center opacity-20"
        />
      </div>

      <div class="z-10 flex flex-1 space-x-4">
        <Icon
          class="mx-auto size-12 text-secondary xs:size-14 xmd:size-16 lg:size-20 xl:size-24"
          name="map"
        />

        <div class="flex flex-col space-y-3 xs:space-y-5">
          <h2 class="text-xl font-semibold xs:text-2xl xmd:text-3xl">
            Find a place to eat near you
          </h2>

          <h3
            class="prose text-base sm:prose-lg sm:text-lg lg:prose-xl lg:text-xl xl:text-2xl 2xl:prose-2xl"
          >
            Find gluten free places to eat near you using our interactive map,
            built for people with coeliac disease. View nearby eateries across
            the UK and Ireland, explore what’s around you in real time, and
            quickly discover safe options wherever you are.
          </h3>

          <div class="flex xmd:flex-1 xmd:items-end xmd:justify-end">
            <CoeliacButton
              theme="secondary"
              href="/wheretoeat/browse"
              size="xl"
              label="View the Map"
              bold
              icon-position="right"
              :icon="ArrowRightIcon"
              classes="justify-center"
            />
          </div>
        </div>

        <div
          class="hidden h-auto w-64 rounded-lg border-8 border-white shadow xmd:block lg:w-72 xl:w-96"
        >
          <img
            src="/images/misc/eating-out-map-colour.png"
            alt=""
            class="h-full w-full object-cover object-center"
          />
        </div>
      </div>
    </Card>

    <div class="my-8">
      <SubHeading text-size="large">Browse places to eat</SubHeading>
    </div>

    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3 lg:gap-5">
      <Card
        theme="primary"
        faded
      >
        <div class="flex flex-1 space-x-4">
          <Icon
            class="mx-auto size-8 text-secondary xs:size-12 xmd:size-14"
            name="eatery"
          />

          <div class="flex flex-1 flex-col space-y-3 xs:space-y-5">
            <h2 class="text-xl font-semibold xs:text-2xl">Eating Out Guide</h2>

            <h3 class="prose text-base lg:prose-lg lg:text-lg">
              Prefer to browse instead of search? Our Eating Out Guide lets you
              explore gluten free venues across the UK by county and town,
              making it easy to navigate and discover places to eat in a more
              structured, step by step way.
            </h3>

            <div class="mt-auto">
              <CoeliacButton
                theme="primary"
                href="/wheretoeat"
                size="lg"
                label="View Guide"
                bold
                classes="justify-center"
              />
            </div>
          </div>
        </div>
      </Card>

      <Card
        theme="primary"
        faded
      >
        <div class="flex flex-1 space-x-4">
          <Icon
            class="mx-auto size-8 text-secondary xs:size-12 xmd:size-14"
            name="reviews"
          />

          <div class="flex flex-1 flex-col space-y-3 xs:space-y-5">
            <h2 class="text-xl font-semibold xs:text-2xl">
              Curated Collections
            </h2>

            <h3 class="prose text-base lg:prose-lg lg:text-lg">
              Explore our hand picked collections of gluten free eateries,
              bringing together some of the best places to eat for coeliacs in
              one place. From city guides to themed selections, these
              collections make it easy to discover new favourites and plan where
              to eat with confidence.
            </h3>

            <div class="mt-auto">
              <CoeliacButton
                theme="primary"
                href="/eating-out/collections"
                size="lg"
                label="View Collections"
                bold
                classes="justify-center"
              />
            </div>
          </div>
        </div>
      </Card>

      <Card
        theme="primary"
        faded
      >
        <div class="flex flex-1 space-x-4">
          <Icon
            class="mx-auto size-8 text-secondary xs:size-12 xmd:size-14"
            name="dinner"
          />

          <div class="flex flex-1 flex-col space-y-3 xs:space-y-5">
            <h2 class="text-xl font-semibold xs:text-2xl">
              GF Nationwide Chains
            </h2>

            <h3 class="prose text-base lg:prose-lg lg:text-lg">
              Looking for reliable gluten free options you can trust wherever
              you are? Browse our list of nationwide chains across the UK that
              offer gluten free menus, allergen guidance, and procedures
              designed to cater for coeliac requirements.
            </h3>

            <div class="mt-auto">
              <CoeliacButton
                theme="primary"
                href="/wheretoeat/nationwide"
                size="lg"
                label="View Chains"
                bold
                classes="justify-center"
              />
            </div>
          </div>
        </div>
      </Card>
    </div>

    <div class="my-8">
      <SubHeading text-size="large">Looking for something else?</SubHeading>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:gap-5">
      <Card
        theme="primary-light"
        faded
      >
        <div class="flex flex-1 space-x-4">
          <Icon
            class="mx-auto size-8 text-secondary xs:size-12 xmd:size-14"
            name="app"
          />

          <div class="flex flex-1 flex-col space-y-3 xs:space-y-5">
            <h2 class="text-xl font-semibold xs:text-2xl">
              Coeliac Sanctuary App
            </h2>

            <h3 class="prose text-base lg:prose-lg lg:text-lg">
              Take our gluten free eating out guide with you wherever you go
              using the Coeliac Sanctuary app. Quickly search for nearby places,
              browse trusted venues, and access everything you need to eat out
              safely, all from your pocket.
            </h3>

            <div class="mt-auto">
              <CoeliacButton
                theme="primary"
                href="/wheretoeat/coeliac-sanctuary-on-the-go"
                size="lg"
                label="Get the App"
                bold
                classes="justify-center"
              />
            </div>
          </div>
        </div>
      </Card>

      <Card
        theme="primary-light"
        faded
      >
        <div class="flex flex-1 space-x-4">
          <Icon
            class="mx-auto size-8 text-secondary xs:size-12 xmd:size-14"
            name="reviews"
          />

          <div class="flex flex-1 flex-col space-y-3 xs:space-y-5">
            <h2 class="text-xl font-semibold xs:text-2xl">Recommend a Place</h2>

            <h3 class="prose text-base lg:prose-lg lg:text-lg">
              Know a great place that caters well for coeliac or gluten free
              diets? Let us know and help grow the community. Your
              recommendations help us expand the guide and support others in
              finding safe and trusted places to eat.
            </h3>

            <div class="mt-auto">
              <CoeliacButton
                theme="primary"
                href="/wheretoeat/recommend-a-place"
                size="lg"
                label="Recommend a place"
                bold
                classes="justify-center"
              />
            </div>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>
