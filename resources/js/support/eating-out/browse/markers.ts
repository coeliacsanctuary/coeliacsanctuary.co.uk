import { EateryBrowseResource } from '@/types/EateryTypes';
import { Feature } from 'ol';
import Supercluster, { PointFeature } from 'supercluster';
import { fromLonLat, toLonLat } from 'ol/proj';
import { Point } from 'ol/geom';
import { computed, onMounted, ref, watch } from 'vue';
import { Marker, MarkerProps } from '@/types/EatingOutBrowseTypes';
import VectorSource from 'ol/source/Vector';
import { Extent } from 'ol/extent';
import { markerStyle } from '@/support/eating-out/browse/styles';
import { Coordinate } from 'ol/coordinate';
import { Pixel } from 'ol/pixel';
import { FeatureLike } from 'ol/Feature';
import VectorLayer from 'ol/layer/Vector';
import eventBus from '@/eventBus';
import useScreensize from '@/composables/useScreensize';

export default () => {
  const rawMarkerSource = ref<VectorSource>();
  const markersOnMap = ref<Marker[]>([]);

  const superClusterRadius = () => {
    switch (useScreensize().currentBreakpoint()) {
      case 'xxxs':
      case 'xxs':
      case 'xs':
      default:
        return 200;
      case 'sm':
      case 'xmd':
        return 175;
      case 'md':
      case 'lg':
        return 150;
      case 'xl':
        return 125;
      case '2xl':
        return 100;
    }
  };

  const createSupercluster = (): Supercluster<MarkerProps> => {
    return new Supercluster<MarkerProps>({
      radius: superClusterRadius(),
      maxZoom: 15,
    });
  };

  const superCluster = ref<Supercluster<MarkerProps>>(createSupercluster());

  const processMapMarkers = (
    eateries: EateryBrowseResource[],
    zoomLevel: number,
    extent: Extent,
  ): void => {
    const newMarkers = createMarkerArrayFromNewMarkers(eateries);

    const markers = markersOnMap.value.map((marker) => ({
      ...marker,
      loaded: false,
    }));

    newMarkers.forEach((marker) => {
      const existingMarker = markers.find((m) => m.id === marker.id);

      if (existingMarker) {
        const existingMarkerIndex = markers.indexOf(existingMarker);

        markers[existingMarkerIndex].loaded = true;

        return;
      }

      markers.push(marker);
    });

    markers.forEach((marker) => {
      if (!marker.loaded) {
        rawMarkerSource.value?.removeFeature(marker.element as Feature);

        return;
      }

      if (marker.new) {
        rawMarkerSource.value?.addFeature(marker.element as Feature);
      }
    });

    markersOnMap.value = markers.filter((m) => m.loaded);

    rawMarkerSource.value?.clear();
    rawMarkerSource.value?.addFeatures(
      generateMarkerClusters(zoomLevel, extent),
    );
  };

  const generateMarkerClusters = (
    zoomLevel: number,
    extent: Extent,
  ): Feature[] => {
    const geoJsonFeatures: PointFeature<MarkerProps>[] = markersOnMap.value.map(
      (marker) => ({
        type: 'Feature',
        geometry: {
          type: 'Point',
          coordinates: [marker.lng, marker.lat],
        },
        properties: {
          id: marker.id,
          color: marker.color,
        },
      }),
    );

    superCluster.value.load(geoJsonFeatures);

    const bbox = [extent[0], extent[1], extent[2], extent[3]];

    const bottomLeft = toLonLat([bbox[0], bbox[1]]);
    const topRight = toLonLat([bbox[2], bbox[3]]);

    const clusters = superCluster.value.getClusters(
      [bottomLeft[0], bottomLeft[1], topRight[0], topRight[1]],
      zoomLevel,
    );

    return clusters.map((c) => {
      const feature = new Feature({
        geometry: new Point(fromLonLat(c.geometry.coordinates)),
      });

      feature.setProperties(c.properties);

      return feature;
    });
  };

  const createMarkerArrayFromNewMarkers = (
    eateries: EateryBrowseResource[],
  ): Marker[] => {
    return eateries
      .map((eatery) => ({
        id: eatery.key,
        loaded: true,
        new: true,
        lat: eatery.location.lat,
        lng: eatery.location.lng,
        color: eatery.color,
        element: new Feature({
          id: eatery.key,
          geometry: new Point(fromLonLat(getEateryLatLng(eatery))),
          color: eatery.color,
        }),
      }))
      .map((eatery: Marker) => {
        eatery.element.setStyle(
          markerStyle(eatery.element.getProperties().color as string),
        );

        return eatery;
      });
  };

  const getEateryLatLng = (eatery: EateryBrowseResource): Coordinate =>
    [eatery.location.lng, eatery.location.lat] as Coordinate;

  const zoomIntoCluster = ({
    pixel,
    markerLayer,
    currentZoom,
  }: {
    pixel: Pixel;
    markerLayer: VectorLayer<VectorSource>;
    currentZoom: number;
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

      if (props.cluster && superCluster.value) {
        let expansionZoom = superCluster.value.getClusterExpansionZoom(
          props.cluster_id,
        );

        if (expansionZoom === currentZoom) {
          expansionZoom = currentZoom + 1;
        }

        if (expansionZoom > 17) {
          expansionZoom = 17;
        }

        eventBus.$emit('map-animate-to', {
          // eslint-disable-next-line
          center: clickedFeature.getGeometry()?.getCoordinates() as Coordinate,
          zoom: expansionZoom,
          duration: 500,
        });
      }
    });
  };

  onMounted(() => {
    rawMarkerSource.value = new VectorSource();
  });

  return { processMapMarkers, zoomIntoCluster, rawMarkerSource };
};
