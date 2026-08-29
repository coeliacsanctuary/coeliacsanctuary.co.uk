import { EateryBrowseResource } from '@/types/EateryTypes';
import { Feature } from 'ol';
import Supercluster, { PointFeature } from 'supercluster';
import { fromLonLat, toLonLat } from 'ol/proj';
import { Point } from 'ol/geom';
import { ref } from 'vue';
import { Marker, MarkerProps } from '@/types/EatingOutBrowseTypes';
import VectorSource from 'ol/source/Vector';
import { boundingExtent, Extent, getHeight, getWidth } from 'ol/extent';
import { Size } from 'ol/size';
import { Pixel } from 'ol/pixel';
import { FeatureLike } from 'ol/Feature';
import VectorLayer from 'ol/layer/Vector';
import eventBus from '@/eventBus';
import useJourneyTracking from '@/composables/useJourneyTracking';

/** The zoom level at which supercluster stops grouping markers together. */
const maxClusterZoom = 15;

export default () => {
  const rawMarkerSource = ref<VectorSource>(new VectorSource());

  /**
   * None of this is rendered directly, and the supercluster index in particular
   * is far too large to hand to Vue's deep reactivity, so it is all kept out of
   * the reactivity system.
   */
  let markersOnMap: Marker[] = [];
  let visibleMarkerIds: Set<string> = new Set();
  let superCluster: Supercluster<MarkerProps> | undefined;
  let loadedMarkerKey: string | undefined;
  let loadedRadius: number | undefined;

  /**
   * Supercluster measures its radius against a 512px tile extent, whereas the
   * map renders 256px tiles, so these are roughly double their on screen size.
   */
  const clusterRadiusForWidth = (width: number): number => {
    if (width < 640) {
      return 200;
    }

    if (width < 1280) {
      return 150;
    }

    return 110;
  };

  const processMapMarkers = (
    eateries: EateryBrowseResource[],
    zoomLevel: number,
    extent: Extent,
    size?: Size,
  ): void => {
    markersOnMap = createMarkerArrayFromNewMarkers(eateries);

    rawMarkerSource.value.clear();
    rawMarkerSource.value.addFeatures(
      generateMarkerClusters(zoomLevel, extent, size),
    );
  };

  /**
   * Rebuilding the index is expensive, so it only happens when the set of
   * markers has actually changed, or when the map has been resized enough to
   * warrant a different cluster radius.
   */
  const loadSupercluster = (size?: Size): void => {
    const radius = clusterRadiusForWidth(size ? size[0] : 1280);
    const markerKey = markersOnMap
      .map((marker) => marker.id)
      .sort()
      .join('|');

    if (
      superCluster &&
      loadedRadius === radius &&
      loadedMarkerKey === markerKey
    ) {
      return;
    }

    if (!superCluster || loadedRadius !== radius) {
      superCluster = new Supercluster<MarkerProps>({
        radius,
        maxZoom: maxClusterZoom,
      });

      loadedRadius = radius;
    }

    const geoJsonFeatures: PointFeature<MarkerProps>[] = markersOnMap.map(
      (marker) => ({
        type: 'Feature',
        geometry: {
          type: 'Point',
          coordinates: [marker.lng, marker.lat],
        },
        properties: {
          id: marker.id,
          typeId: marker.typeId,
          venueTypeId: marker.venueTypeId,
        },
      }),
    );

    superCluster.load(geoJsonFeatures);

    loadedMarkerKey = markerKey;
  };

  const generateMarkerClusters = (
    zoomLevel: number,
    extent: Extent,
    size?: Size,
  ): Feature[] => {
    loadSupercluster(size);

    const bottomLeft = toLonLat([extent[0], extent[1]]);
    const topRight = toLonLat([extent[2], extent[3]]);

    const clusters =
      superCluster?.getClusters(
        [bottomLeft[0], bottomLeft[1], topRight[0], topRight[1]],
        Math.floor(zoomLevel),
      ) ?? [];

    const nowVisible = new Set<string>();

    const features = clusters.map((c) => {
      const feature = new Feature({
        geometry: new Point(fromLonLat(c.geometry.coordinates)),
      });

      if (!('cluster' in c.properties)) {
        nowVisible.add(c.properties.id);
      }

      feature.setProperties(c.properties);

      return feature;
    });

    logNewlyVisibleMarkers(nowVisible);

    return features;
  };

  /**
   * A marker logs an impression when it appears on the map, and again if it
   * later reappears, but not while it simply stays on screen.
   */
  const logNewlyVisibleMarkers = (nowVisible: Set<string>): void => {
    nowVisible.forEach((id) => {
      if (visibleMarkerIds.has(id)) {
        return;
      }

      useJourneyTracking().logEvent('other', 'WhereToEatMap/Marker', {
        eateryId: id,
      });
    });

    visibleMarkerIds = nowVisible;
  };

  const createMarkerArrayFromNewMarkers = (
    eateries: EateryBrowseResource[],
  ): Marker[] =>
    eateries.map((eatery) => ({
      id: eatery.key,
      lat: eatery.location.lat,
      lng: eatery.location.lng,
      typeId: eatery.typeId,
      venueTypeId: eatery.venueTypeId,
    }));

  /**
   * A cluster clicked just as a new set of markers lands will have been indexed
   * against the previous set, and supercluster throws on an id it no longer
   * knows about.
   */
  const getClusterLeaves = (clusterId: number): PointFeature<MarkerProps>[] => {
    try {
      return superCluster?.getLeaves(clusterId, Infinity) ?? [];
    } catch {
      return [];
    }
  };

  const zoomIntoCluster = ({
    pixel,
    markerLayer,
  }: {
    pixel: Pixel;
    markerLayer: VectorLayer<VectorSource>;
  }) => {
    markerLayer.getFeatures(pixel).then((clickedFeatures: FeatureLike[]) => {
      if (!clickedFeatures.length) {
        return;
      }

      const clickedFeature: FeatureLike = clickedFeatures[0];

      const props: { cluster?: unknown; cluster_id: number } =
        clickedFeature.getProperties() as {
          cluster?: unknown;
          cluster_id: number;
        };

      if (!props.cluster || !superCluster) {
        return;
      }

      const leaves = getClusterLeaves(props.cluster_id);

      if (!leaves.length) {
        return;
      }

      const extent = boundingExtent(
        leaves.map((leaf) => fromLonLat(leaf.geometry.coordinates)),
      );

      /**
       * Places sharing the same coordinates, nationwide branches most often,
       * give a zero sized extent, which would fit to the maximum resolution, so
       * zoom to the point they stop clustering instead.
       */
      if (getWidth(extent) === 0 && getHeight(extent) === 0) {
        const clickedGeometry = clickedFeature.getGeometry();

        if (clickedGeometry instanceof Point) {
          eventBus.$emit('map-animate-to', {
            center: clickedGeometry.getCoordinates(),
            zoom: maxClusterZoom + 1,
            duration: 500,
          });
        }

        return;
      }

      eventBus.$emit('map-fit-extent', extent);
    });
  };

  return { processMapMarkers, zoomIntoCluster, rawMarkerSource };
};
