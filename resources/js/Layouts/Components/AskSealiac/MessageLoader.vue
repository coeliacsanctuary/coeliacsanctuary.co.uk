<script setup lang="ts">
import SealiacSeal from '@/Svg/SealiacSeal.vue';
import Loader from '@/Components/Loader.vue';
</script>

<template>
  <div class="flex space-x-2 px-2">
    <div>
      <div class="size-10 rounded bg-primary/50 p-1">
        <SealiacSeal />
      </div>
    </div>
    <div class="prose prose-sm flex-1 rounded-lg bg-primary/20 p-2">
      <Loader
        display
        color="primary"
        :absolute="false"
      />
    </div>
  </div>
</template>
