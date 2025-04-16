<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import WorkWithUsStory from '@/Components/PageSpecific/WorkWithUs/WorkWithUsStory.vue';
import Heading from '@/Components/Heading.vue';
</script>

<template>
  <div class="mt-5 flex flex-col gap-5">
    <Card class="mx-auto flex max-w-8xl flex-col space-y-3">
      <Heading> Work With Us </Heading>

      <p class="prose prose-lg max-w-none xl:prose-xl">
        Over the last few years since running Coeliac Sanctuary I have had the
        pleasure of working with some fantastic companies. Please don't hesitate
        to contact me if you want to work with me, whether you want me to help
        promote your brand, create an exciting new recipe or something else, I
        love a challenge and will have a go at anything! I am always looking for
        brands who would love to work with me, specifically in the area of
        Coeliac, gluten free living and free from.
      </p>

      <p class="prose prose-lg max-w-none xl:prose-xl">
        Coeliac Sanctuary is forever growing and currently averages over 30,000
        visitors a month and has a social media following of over 35,000 on
        <a
          href="https://www.facebook.com/coeliacsanctuary/"
          target="_blank"
          >Facebook</a
        >
        and nearly 15,000 on
        <a
          href="https://www.instagram.com/coeliacsanctuary"
          target="_blank"
          >Instagram</a
        >.
      </p>

      <h3 class="my-5 text-center text-xl font-semibold xl:text-2xl">
        Here is just a few companies I have had the pleasure of working with in
        the past!
      </h3>
    </Card>

    <WorkWithUsStory
      title="Juvela"
      image="/images/misc/work-with-us/juvela-logo.jpg"
      :links="[
        {
          href: '/recipe/chocolate-orange-bread-and-butter-pudding',
          label: 'Bread and Butter Pudding Recipe',
        },
        {
          href: '/blog/five-super-simple-sandwich-suggestions',
          label: 'Five Simple Sandwich Suggestions',
        },
        {
          href: '/blog/juvela-bread-in-selected-tesco-stores',
          label: 'Juvela Bread in selected Tesco Stores',
        },
      ]"
    >
      I teamed up with Juvela to help promote their bread being released into
      Tesco as well as developing recipes and promoting their taster events
      amongst a selection of other projects.
    </WorkWithUsStory>

    <WorkWithUsStory
      title="Schär"
      image="/images/misc/work-with-us/schar-logo.jpg"
      :links="[
        {
          href: 'https://www.facebook.com/coeliacsanctuary/posts/pfbid0NgfUXBLJ5iFCjM3H8wVakkVLPaiJx7NGA6dtnTtXmLDPhnX2EUqh2vVZDiFMCi18l',
          label: 'Example of a Facebook post showcasing their flatbread range',
        },
      ]"
    >
      When Schär got in touch with me to ask if I could help promote their
      gluten free range, I was more than happy to get involved. I created social
      media content showcasing their products, used their bread in a range of
      gluten free recipes, and helped spread the word about their brand to my
      audience.
    </WorkWithUsStory>

    <WorkWithUsStory
      title="Schwartz"
      image="/images/misc/work-with-us/schwartz-logo.png"
      :links="[
        {
          href: '/blog/official-launch-of-schwartz-gluten-free-flavour-sachets',
          label: 'Official Launch of Schwartz Gluten Free Flavour Sachets',
        },
        {
          href: '/recipe/schwartz-stuffed-peppers',
          label: 'Schwartz Stuffed Peppers',
        },
      ]"
    >
      When Schwartz launched their now discontinued gluten free range in 2016, I
      was invited down to their launch event in Covent Garden to taste test
      their gluten free flavour packets and promote them via a blog and sharing
      their recipes.
    </WorkWithUsStory>

    <WorkWithUsStory
      title="Buttermilk"
      image="/images/misc/work-with-us/buttermilk-logo.png"
      :links="[
        {
          href: 'https://www.facebook.com/coeliacsanctuary/posts/pfbid0ULQNKPHx7x8DUDZd7mBsM5iioFidGHKk2W3igzvRPCdt6dMbE5CRqzRnmQQeCZwal',
          label: 'Facebook post about the new caramel nougat bars',
        },
        {
          href: 'https://www.facebook.com/coeliacsanctuary/posts/pfbid024fJar27QJCYpn9Sropx1YyQxYaaPPTRT5EYWJTFuCLnFPWLHEUYQ6WPR5gjSRLF9l',
          label: 'Facebook post about the new chocolate wafer bars',
        },
      ]"
    >
      When Buttermilk launched their delicious range of gluten free treats, I
      worked with them to help spread the word on social media. I was especially
      excited to share their incredible wafer chocolate bars and indulgent
      caramel nougat bar—firm favourites in our house!
    </WorkWithUsStory>

    <WorkWithUsStory
      title="Morrisons"
      image="/images/misc/work-with-us/morrisons-logo.png"
      :links="[
        {
          href: '/blog/morrisons-behind-the-shelves',
          label: 'Morrisons - Behind the Shelves',
        },
      ]"
    >
      When Morrisons wanted bloggers to taste test their Christmas range in
      August 2016, and also find out more about their Free From range, explore
      their head office, and promote their new products I was happy to help them
      out.
    </WorkWithUsStory>

    <div class="grid gap-5 xmd:grid-cols-2">
      <WorkWithUsStory
        title="Pho"
        image="/images/misc/work-with-us/pho-logo.png"
        :links="[
          {
            href: '/wheretoeat/nationwide/pho',
            label: `Pho's page in my Eating Out guide`,
          },
        ]"
      >
        I worked with Pho in early 2024 to get all of their branches listed in
        my Eating Out guide, previously, I kept the main Eating Out guide for
        listing independent eateries, and nationwide chains listed on their own
        page, this was a major change to begin listing branches of chain
        restaurants, and given the scale of some chain restaurants a lot of
        work, but Pho kindly sent me a list of all of their branches which made
        the task a lot easier and made it easy for those accessing the Eating
        Out guide to find more places to eat.
      </WorkWithUsStory>

      <WorkWithUsStory
        title="The Lounge Group"
        image="/images/misc/work-with-us/lounge-logo.png"
        :links="[
          {
            href: '/wheretoeat/nationwide/cosy-club',
            label: `Cosy Clubs page in my Eating Out guide`,
          },
          {
            href: '/wheretoeat/nationwide/the-lounges',
            label: `The Lounge's page in my Eating Out guide`,
          },
        ]"
      >
        Following the success of working with Pho to get their branches into the
        Eating Out guide, I reached out to the Lounge Group to see if they could
        provide a list of their 200+ Lounge branches, and their Cosy Club
        branches to add to the Eating Out guide, both of which were regularly
        sent through as place recommendations, but always remained on the
        Nationwide page. The Lounge Group kindly sent me a massive list of all
        of their branches to add to the Eating Out guide.
      </WorkWithUsStory>
    </div>

    <Card class="mx-auto prose prose-lg max-w-6xl text-center xl:prose-xl">
      <p>
        Are you listed in our
        <a
          href="/wheretoeat/nationwide"
          target="_blank"
          >Nationwide Eateries</a
        >
        page and want all of your branches listing? If you can provide a CSV of
        your branches please
        <a
          href="/contact"
          target="_blank"
          >get in touch</a
        >
        and I'd love to work with you to get them listed!
      </p>
    </Card>
  </div>
</template>
