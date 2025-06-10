import { defineStore } from 'pinia';
import { SearchParams } from '@/types/Search';

type State = {
  data: SearchParams;
};

type Getters = {
  toForm: (state: State) => State['data'];
};

type Actions = {
  setForm: (state: Partial<State['data']>) => void;
};

const useSearchStore = defineStore<'search-store', State, Getters, Actions>(
  'search-store',
  {
    state: () => ({
      data: {
        q: '',
        blogs: true,
        recipes: true,
        shop: true,
        eateries: false,
      },
    }),
    getters: {
      toForm: (state) => state.data,
    },
    actions: {
      setForm(state) {
        this.data = {
          ...this.data,
          ...state,
        };
      },
    },
  },
);

export default useSearchStore;
