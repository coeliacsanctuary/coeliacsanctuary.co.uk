<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import ArticleImage from '@/JitComponents/ArticleImage.vue';
import { Link } from '@inertiajs/vue3';
import SubHeading from '@/Components/SubHeading.vue';
</script>

<template>
  <div class="mt-5 flex flex-col gap-5">
    <Card
      class="prose prose-lg block! max-w-none lg:max-xl:prose-xl xl:prose-2xl"
    >
      <Heading :border="false">About Coeliac Sanctuary</Heading>

      <div class="flex">
        <div>
          <p>
            Coeliac Sanctuary started life in June 2014, initially it was going
            to be just a kind of document for me, Alison, Coeliac Sanctuary's
            founder, to add places to eat and keep note of places eaten at as a
            kind of diary to remember what restaurants had been like, but being
            a web designer and struggling with the job I was in at the time I
            decided to make a website to both increase my knowledge and make a
            resource to refer to when eating out.
          </p>
          <p>
            At the time I came up with the idea for the site, I didn't know what
            it was going to be called, I was playing around with Coeliac Asylum
            to start with. The idea revolved around the fact I thought I was
            going crazy when doctors couldn't find any cause for the abdominal
            pains and anxiety, I had the idea that the website would take a
            black, grey and orange colour scheme to play on the dark side of
            being undiagnosed.
          </p>
          <p>
            It was only when toying with other ideas that the name Coeliac
            Sanctuary came to me, and it made me think of a Seal Sanctuary,
            hence why the logo and mascot of Coeliac Sanctuary is Sealiac the
            Seal, it took a few days but having some training in graphic design
            I came up a logo and decided to theme the website around a Seal
            Sanctuary; blue for pools, grey for the seal and, to tie in with
            Coeliac, yellow for wheat. The original website used a solid blue
            background with wheat styled headers and footers and a curved block
            design, this original version of the website launched in August
            2014.
          </p>

          <p>
            Even though it wasn't built to be anything more than a hobby and
            personal resource, the site took off and over a year it grew
            massively in popularity, often getting 100s of visits a day on the
            site and thousands of Facebook followers and a fair few hundred
            followers spread over other social media such as Twitter and
            Pinterest. It's popularity also lead to a newsletter being developed
            in February 2015
          </p>
          <p>
            As the website continued to grow it reached a point where it needed
            to be redesigned to fit in with current web trends, and it need to
            be mobile friendly, keeping the same colours and logo but a whole
            new look, Coeliac Sanctuary 2.0 began development in April 2015,
            finally being released in September 2015 to coincide with the 1 year
            anniversary of the site.
          </p>
          <p>
            Since then, the website has had a couple of other redesigns, and
            lots of new features added such as our improved
            <Link href="/eating-out">Eating Out guide</Link> and
            <Link href="/wheretoeat/coeliac-sanctuary-on-the-go"
              >Coeliac Sanctuary On The Go</Link
            >
            app, and the shop added in 2017 with my very popular travel cards.
          </p>
          <p>
            Coeliac Sanctuary's journey so far has been incredible and continues
            to grow, for something that was built just as a personal reference
            it bloomed into something beyond anyone's expectations, receiving
            thousands of unique visitors a week and 10s of thousands of page
            views... 10s of thousands of followers on Social Media,
            <Link href="/work-with-us">working with some big brands</Link> in
            our gluten free world... Who knows where it will go next!
          </p>
        </div>

        <div class="ml-4 hidden w-60 shrink-0 flex-col space-y-2 md:flex">
          <ArticleImage
            src="/images/misc/coeliac-v1.jpg"
            title="First version of Coeliac Sanctuary, August 2014"
            position="right"
            class="max-w-60!"
          />

          <ArticleImage
            src="/images/misc/coeliac-v2.jpg"
            title="Second version of Coeliac Sanctuary, September 2015"
            position="right"
            class="max-w-60!"
          />

          <ArticleImage
            src="/images/misc/coeliac-v3.jpg"
            title="Third version of Coeliac Sanctuary, August 2018"
            position="right"
            class="max-w-60!"
          />

          <ArticleImage
            src="/images/misc/coeliac-v4.jpg"
            title="Fourth version of Coeliac Sanctuary, November 2020"
            position="right"
            class="max-w-60!"
          />
        </div>
      </div>
    </Card>

    <Card
      class="prose prose-lg block! max-w-none lg:max-xl:prose-xl xl:prose-2xl"
    >
      <SubHeading>Alison - Founder/Owner</SubHeading>

      <ArticleImage
        title="Alison - Coeliac Sanctuary founder"
        src="/images/misc/alison.png"
        position="right"
        class="max-w-60!"
      />
      <p>
        In June 2014 Alison developed anxiety about her job and had been
        diagnosed as intolerant to gluten and some dairy products, the
        intolerances were only discovered by a consultant after 5 months off
        work with agonising stomach pains and 100s of tests all revealing
        nothing. Coeliac tests also came back clear but removing gluten from her
        diet resolved the pains causing GPs and consultants to deem her
        intolerant. However she considers herself Coeliac and sticks to a strict
        gluten free diet as the smallest crumb can leave her in agonising pain
        for days. It was because of this intolerance Coeliac Sanctuary came to
        life.
      </p>
      <p>
        In January 2015 Alison was also diagnosed with Fibromyalgia, she found
        it difficult to concentrate, and simple tasks took her 10x the amount of
        time they used to. Everything on Coeliac Sanctuary, from the building
        and design to the maintenance and updating was originally done by
        Alison, but Jamie took over the technical side, but she still controls
        everything which goes on.
      </p>
      <p>
        In 2021 Alison and Jamie got married, and in 2024 celebrated the birth
        of their first child.
      </p>
    </Card>

    <Card
      class="prose prose-lg block! max-w-none lg:max-xl:prose-xl xl:prose-2xl"
    >
      <SubHeading>
        Jamie - The sidekick, the brains, developer and tech guru
      </SubHeading>

      <ArticleImage
        title="Jamie - Coeliac Sanctuary developer and tech guru"
        src="/images/misc/jamie.png"
        position="right"
        class="max-w-60!"
      />
      <p>
        The sidekick has always been part of the site helping out when he can,
        but because of Alison's Fibromyalgia he found himself having to help out
        more especially when it comes to the building side, he is basically the
        extra brains of the party. Jamie works more of the back end development
        of the site, but will pop up now and then doing other things, usually
        recipes.
      </p>
      <p>
        These days Jamie is solely responsible for keeping the website ticking,
        from looking after the servers to maintaining the actual code that
        powers the website without him the website would probably have been in
        deep doo-doo ages ago, with his know how he keeps all the pesky gremlins
        at bay so the website can keep on rolling and handle whatever is thrown
        at it. The benefits of being married to a web applications developer!
      </p>
    </Card>
  </div>
</template>
