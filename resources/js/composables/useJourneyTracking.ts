import { EventType } from '@/types/JourneyTypes';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ShallowRef, watch } from 'vue';
import { useElementVisibility } from '@vueuse/core/index';

export default () => {
  const logWhenVisible = (
    templateRef: Readonly<ShallowRef>,
    type: EventType,
    identifier: string,
    data: object = {},
  ) => {
    const targetIsVisible = useElementVisibility(templateRef);

    watch(targetIsVisible, (isVisible) => {
      if (isVisible) {
        logEvent(type, identifier, data);
      }
    });
  };

  const logEvent = (type: EventType, identifier: string, data: object = {}) => {
    const page = usePage<{
      journey?: { id: string; pageViewId: string };
    }>();

    const journeyId = page.props.journey?.id;
    const pageViewId = page.props.journey?.pageViewId;

    if (!journeyId || !pageViewId) {
      return;
    }

    axios
      .post('/api/journey/event', {
        journey_id: journeyId,
        page_view_id: pageViewId,
        event_type: type,
        event_identifier: identifier,
        data,
      })
      .then(() => {
        //
      })
      .catch(() => {
        //
      });
  };

  return { logWhenVisible, logEvent };
};
