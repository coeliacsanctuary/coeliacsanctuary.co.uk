<template>
  <Card class="flex flex-col items-center justify-center">
    <div class="w-full px-3 py-3">
      <div class="itesm-center flex justify-between">
        <h1
          class="text-3xl font-light text-gray-500"
          v-text="card.name"
        />

        <div class="flex items-center space-x-2">
          <SelectControl
            v-if="!loading"
            v-model="selectedDateRange"
            :options="dateRangeOptions"
            @selected="handleDateRangeChange"
          />

          <input
            v-if="selectedDateRange === 'custom'"
            v-model="startDate"
            type="date"
            :max="endDate"
            class="pl-2appearance-none ring-primary-200 h-8 w-full rounded-md focus:bg-white focus:ring focus:outline-none dark:ring-gray-600"
            @change="getChartable"
          />

          <input
            v-if="selectedDateRange === 'custom'"
            v-model="endDate"
            type="date"
            :min="startDate"
            class="pl-2appearance-none ring-primary-200 h-8 w-full rounded-md focus:bg-white focus:ring focus:outline-none dark:ring-gray-600"
            @change="getChartable"
          />
        </div>
      </div>

      <LoadingView :loading="loading">
        <div class="w-full">
          <apex-chart
            width="100%"
            :height="chart.height"
            :type="chart.type"
            :options="chart.options"
            :series="chart.data"
          />

          <span
            v-if="chart.helpText"
            class="text-xs"
            v-html="chart.helpText"
          />
        </div>
      </LoadingView>
    </div>
  </Card>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts';
import dayjs from 'dayjs';

export default {
  components: {
    'apex-chart': VueApexCharts,
  },

  props: {
    card: {
      required: true,
      type: Object,
    },
  },

  data: () => ({
    loading: true,
    dateRanges: [],
    selectedDateRange: undefined,
    chart: undefined,
    startDate: dayjs().subtract(1, 'day').format('YYYY-MM-DD'),
    endDate: dayjs().format('YYYY-MM-DD'),
  }),

  computed: {
    dateRangeOptions() {
      return [
        ...this.dateRanges,
        ...(this.card.customDateRange
          ? [{ label: 'Custom', value: 'custom' }]
          : []),
      ];
    },
  },

  mounted() {
    this.getChartable();
  },

  methods: {
    getChartable() {
      this.loading = true;

      Nova.request()
        .get(this.buildUrl())
        .then((response) => {
          this.dateRanges = response.data.dates;
          this.selectedDateRange = response.data.selectedDateRange;
          this.chart = response.data.chart;

          this.loading = false;
        })
        .catch((error) => {
          Nova.error(error?.response?.data);
        });
    },

    buildUrl() {
      const url = '/nova-vendor/apex-charts';

      const params = new URLSearchParams();

      params.append('chartable', this.card.chartable);

      if (this.card.params) {
        params.append('params', JSON.stringify(this.card.params));
      }

      if (this.selectedDateRange) {
        params.append('selectedDateRange', this.selectedDateRange);

        if (this.selectedDateRange === 'custom') {
          params.append('startDate', this.startDate);
          params.append('endDate', this.endDate);
        }
      }

      return url + '?' + params.toString();
    },

    handleDateRangeChange(e) {
      console.log(e);
      this.getChartable();
    },

    watch: {
      selectedDateRange: function (a, b) {
        console.log(this.selectedDateRange);
        console.log({ a, b });
      },
    },
  },
};
</script>
