<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { MetaProps } from '@/types/DefaultProps';

defineProps<{ metas: MetaProps }>();
</script>

<template>
  <Head>
    <title>{{ metas.title }}</title>

    <meta
      name="description"
      :content="metas.description"
    />
    <meta
      name="keywords"
      :content="metas.tags.join(', ')"
    />

    <link
      rel="canonical"
      :href="metas.currentUrl"
    />

    <meta
      property="og:type"
      content="article"
    />
    <meta
      property="og:locale"
      content="en_GB"
    />
    <meta
      property="og:site_name"
      content="Coeliac Sanctuary"
    />
    <meta
      property="og:description"
      :content="metas.description"
    />
    <meta
      property="og:title"
      :content="metas.title"
    />
    <meta
      property="og:image"
      :content="metas.image"
    />
    <meta
      property="og:image:type"
      content="image/jpeg"
    />
    <meta
      property="og:url"
      :content="metas.currentUrl"
    />

    <meta
      name="twitter:card"
      content="summary_large_image"
    />
    <meta
      name="twitter:description"
      :content="metas.description"
    />
    <meta
      name="twitter:title"
      :content="metas.title"
    />
    <meta
      name="twitter:site"
      content="@coeliacsanc"
    />
    <meta
      name="twitter:domain"
      content="Coeliac Sanctuary"
    />
    <meta
      name="twitter:image:src"
      :content="metas.image"
    />
    <meta
      name="twitter:creator"
      content="@coeliacsanc"
    />

    <link
      v-if="metas.feed"
      rel="alternate"
      type="application/rss+xml"
      :href="metas.feed"
    />

    <template v-if="metas.alternateMetas">
      <meta
        v-for="(content, property) in metas.alternateMetas"
        :key="property"
        :property="property"
        :content="content"
      />
    </template>

    <template v-if="metas.schema">
      <template v-for="schema in metas.schema">
        {{ schema }}
      </template>
    </template>

    <meta
      v-if="metas.doNotTrack"
      name="robots"
      content="noindex"
    />
  </Head>
</template>
