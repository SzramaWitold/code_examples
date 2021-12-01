<template>
  <div id="mapid">
    <pop-up-even-list id="popup" :events="popupEvents"></pop-up-even-list>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import store from '@/store'
import User from '@/store/models/User'
import leaflet from 'leaflet'
import LocalizationInterface from '@/store/models/LocalizationInterface'
import PopUpEvenList from '@/components/lists/PopUpEvenList.vue'

@Options({
  watch: {
    currentLocation () {
      this.locationChange()
    },
    localization () {
      this.locationChange()
    },
    events () {
      this.setMarkers()
    }
  },
  props: {
    localization: Object,
    isNew: false
  },
  components: {
    PopUpEvenList
  }
})
export default class MapComponent extends Vue {
  public mymap: any = null
  public marker: any = null
  public markerGroup: any = null
  public localization: LocalizationInterface | null = null
  public fullscreen = false
  public popupEvents = []
  public html: any = null
  public isNew = false
  public myLocationIcon = new leaflet.Icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
  })

  locationChange () {
    this.markerGroup.clearLayers()
    if (this.localization) {
      this.mymap.setView([
        this.localization.latitude,
        this.localization.longitude
      ])
      this.marker = leaflet.marker([
        this.localization.latitude,
        this.localization.longitude
      ]).addTo(this.markerGroup)
    } else {
      this.mymap.setView([
        this.currentLocation.latitude,
        this.currentLocation.longitude
      ])
      this.marker = leaflet.marker([
        this.currentLocation.latitude,
        this.currentLocation.longitude
      ], {
        icon: this.myLocationIcon,
        draggable: true
      }).addTo(this.markerGroup)
      this.marker.on('dragend', this.onDragEnd)
    }
  }

  setMarkers () {
    if (this.events) {
      this.events.forEach((event: any) => {
        const $marker = leaflet.marker([
          event.localization.latitude,
          event.localization.longitude
        ]).addTo(this.markerGroup)
        $marker.on('click', this.markerClicked)
      })
    }
  }

  markerClicked (e) {
    const popup = leaflet.popup()
    this.popupEvents = store.getters.getEventByLocation(e.latlng.lng, e.latlng.lat)
    if (!this.html) {
      this.html = document.getElementById('popup')
    }
    popup
      .setLatLng(e.latlng)
      .setContent(this.html)
      .openOn(this.mymap)
  }

  get events () {
    return store.state.events
  }

  get user (): User | null {
    return store.state.user
  }

  get currentLocation (): any {
    return store.state.currentLocation
  }

  onDragEnd () {
    store.dispatch('setPlace', {
      latitude: this.marker.getLatLng().lat,
      longitude: this.marker.getLatLng().lng
    })
  }

  mounted () {
    this.mymap = leaflet.map('mapid').setView([51.505, -0.09], 13)
    this.markerGroup = leaflet.layerGroup().addTo(this.mymap)
    leaflet.tileLayer(`https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=${process.env.VUE_APP_MAPBOX_KEY}`, {
      maxZoom: 18,
      id: 'mapbox/streets-v11',
      tileSize: 512,
      zoomOffset: -1,
      accessToken: process.env.VUE_APP_MAPBOX_KEY
    }).addTo(this.mymap)

    this.$nextTick(() => {
      if (this.localization) {
        this.mymap.setView([
          this.localization.latitude,
          this.localization.longitude
        ], 13)
        this.marker = leaflet.marker([
          this.localization.latitude,
          this.localization.longitude
        ]).addTo(this.markerGroup)
      } else if (this.currentLocation) {
        this.mymap.setView(
          new leaflet.LatLng(this.currentLocation.latitude, this.currentLocation.longitude), 13)
        if (!this.isNew) {
          this.setMarkers()
        }
        this.marker = leaflet.marker([
          this.currentLocation.latitude,
          this.currentLocation.longitude
        ], {
          draggable: true
        }).addTo(this.markerGroup)
        this.marker.on('dragend', this.onDragEnd)
      } else {
        navigator.geolocation.getCurrentPosition(position => {
          store.dispatch('setPlace', {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
          })
        }, error => console.log(error))
      }
    })
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
