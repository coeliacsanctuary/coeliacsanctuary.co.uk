<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import ArticleImage from '@/JitComponents/ArticleImage.vue';
import Heading from '@/Components/Heading.vue';

defineProps<{ image: string }>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{ label: 'Back to eating out guide.', href: '/eating-out' }"
    >
      Coeliac Sanctuary - On the Go
    </Heading>

    <img
      class="mx-auto mb-4"
      :src="image"
      alt="Coeliac Sanctuary - On the Go"
    />

    <p class="prose prose-lg max-w-none md:prose-xl">
      Coeliac Sanctuary - On the Go connects to the eating out guide on the
      Coeliac Sanctuary website to display the locations of gluten free places
      to eat around your location. You can also search for locations around the
      UK and Ireland to plan your next trip to the seaside or to the city!
    </p>

    <p class="prose prose-lg max-w-none md:prose-xl">
      All places can either be displayed on a map or as a list and can be
      filtered to easily find the type of places you'd like to eat out at.
    </p>

    <p class="prose prose-lg max-w-none md:prose-xl">
      All of the places on Coeliac Sanctuary are recommended to us by other
      people and are checked before they are added to the app, but if you have a
      bad experience at a location on our app, or if it no longer exists you can
      easily report it to us through the app.
    </p>

    <p class="prose prose-lg max-w-none md:prose-xl">
      Coeliac Sanctuary - On the Go list places in the UK and Ireland only.
    </p>

    <div
      class="mx-auto flex max-w-[900px] items-center justify-center space-x-2 text-center"
    >
      <ArticleImage
        position="fullwidth"
        src="/images/misc/app/1.png"
      />
      <ArticleImage
        position="fullwidth"
        src="/images/misc/app/2.png"
      />
      <ArticleImage
        position="fullwidth"
        src="/images/misc/app/3.png"
      />
    </div>

    <div
      class="mx-auto flex max-w-[900px] flex-col space-y-4 sm:mt-4 sm:flex-row sm:justify-between sm:space-y-0 sm:space-x-4"
    >
      <a
        title="Google Play Store"
        href="https://play.google.com/store/apps/details?id=com.coeliacsanctuary.onthego"
        target="_blank"
        class="flex flex-col items-center space-y-2 rounded-sm bg-linear-to-br from-primary/30 to-primary-light/30 p-2 sm:h-full sm:max-w-1/2 lg:p-4"
      >
        <img
          style="width: 250px"
          src="https://play.google.com/intl/en_us/badges/images/generic/en_badge_web_generic.png"
          alt="Get it on Google Play"
        />
        <span class="text-center font-semibold">
          Download the App now from the Google Play store
        </span>
      </a>

      <a
        title="Apple App Store"
        href="https://apps.apple.com/us/app/coeliac-sanctuary-on-the-go/id1608694621"
        target="_blank"
        class="flex flex-col items-center space-y-2 rounded-sm bg-linear-to-br from-primary/30 to-primary-light/30 p-2 sm:h-full sm:max-w-1/2 lg:p-4"
      >
        <img
          style="width: 220px"
          class="py-[11px]"
          src="https://tools.applemediaservices.com/api/badges/download-on-the-app-store/black/en-us?size=250x83&amp;releaseDate=1644192000&h=d94315b3908f3483bc41a28cdbda8bf7"
          alt="Download on the App Store"
        />
        <span class="text-center font-semibold">
          Download the App now from the Apple App store
        </span>
      </a>
    </div>
  </Card>
</template>
