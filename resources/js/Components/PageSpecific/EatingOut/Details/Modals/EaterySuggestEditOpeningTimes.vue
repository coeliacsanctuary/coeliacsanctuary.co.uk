<script setup lang="ts">
import { Days, EditableEateryData } from '@/types/EateryTypes';
import { computed, ComputedRef, onMounted, Ref, ref } from 'vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';

const props = defineProps<{
  currentOpeningTimes: EditableEateryData['opening_times'];
}>();

type TimePicker = { value: number; label: string };
type Time = [null, null] | [number, number];
type DayDetail = {
  key: Days;
  label: string;
  closed: boolean;
  start: Time;
  end: Time;
};

const emits = defineEmits(['change']);

const openingTimes: Ref<DayDetail[]> = ref([]);

const days: ComputedRef<Days[]> = computed(() => [
  'monday',
  'tuesday',
  'wednesday',
  'thursday',
  'friday',
  'saturday',
  'sunday',
]);

const hours: ComputedRef<TimePicker[]> = computed(() =>
  Array.from({ length: 24 }).map((value, hour) => ({
    value: hour,
    label: (hour < 10 ? '0' : '') + hour.toString(),
  })),
);

const minutes: ComputedRef<TimePicker[]> = computed(() => [
  { value: 0, label: '00' },
  { value: 15, label: '15' },
  { value: 30, label: '30' },
  { value: 45, label: '45' },
]);

const splitTime = (time: string | null): Time => {
  if (!time) {
    return [null, null];
  }

  const split = time.split(':');

  return [parseInt(split[0], 10), parseInt(split[1], 10)];
};

const constructDay = (day: Days, isClosed = null): DayDetail => ({
  key: day,
  label: day.charAt(0).toUpperCase() + day.slice(1),
  closed:
    isClosed === null ? props.currentOpeningTimes[day][0] === null : isClosed,
  start: isClosed ? [null, null] : splitTime(props.currentOpeningTimes[day][0]),
  end: isClosed ? [null, null] : splitTime(props.currentOpeningTimes[day][1]),
});

const emitChange = () => {
  emits('change', openingTimes.value);
};

onMounted(() => {
  openingTimes.value = days.value.map((day) => constructDay(day));
});
</script>

<template>
  <div class="text-sm">
    <ul class="flex flex-col space-y-px divide-y divide-grey-off">
      <li
        v-for="day in openingTimes"
        :key="day.key"
        class="flex w-full flex-col space-y-2 py-1 xs:flex-row xs:items-center xs:justify-between xs:space-y-0 xs:space-x-2"
      >
        <div class="flex justify-between xs:flex-1 xs:items-center">
          <div>{{ day.label }}</div>

          <div class="flex items-center space-x-2">
            <FormCheckbox
              v-model="day.closed"
              label="Closed"
              :name="day.key + '_closed'"
            />
          </div>
        </div>

        <div class="flex items-center justify-between space-x-1">
          <div class="flex space-x-px">
            <FormSelect
              v-model="day.start[0]"
              :name="day.key + '_start_hour'"
              :options="hours"
              :disabled="day.closed"
              label=""
              hide-label
              class="w-[65px]"
              @update:model-value="emitChange()"
            />
            <FormSelect
              v-model="day.start[1]"
              :name="day.key + '_start_minutes'"
              :options="minutes"
              :disabled="day.closed"
              label=""
              hide-label
              class="w-[65px]"
              @update:model-value="emitChange()"
            />
          </div>

          <span class="hidden xs:block">-</span>

          <div class="flex space-x-px">
            <FormSelect
              v-model="day.end[0]"
              :name="day.key + '_end_hour'"
              :options="hours"
              :disabled="day.closed"
              label=""
              hide-label
              class="w-[65px]"
              @update:model-value="emitChange()"
            />
            <FormSelect
              v-model="day.end[1]"
              :name="day.key + '_end_minutes'"
              :options="minutes"
              :disabled="day.closed"
              label=""
              hide-label
              class="w-[65px]"
              @update:model-value="emitChange()"
            />
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
