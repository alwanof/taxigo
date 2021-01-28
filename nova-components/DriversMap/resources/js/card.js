import * as VueGoogleMaps from "vue2-google-maps";

Nova.booting((Vue, router, store) => {
  Vue.component('drivers-map', require('./components/Card'));
  Vue.config.devtools = true;
  Vue.use(VueGoogleMaps, {
        load: {
            key: "AIzaSyANYVpeOpsNN4DqdKR4AKAyd03IQ3_9PvU",
            libraries: "places,directions"
        }
    });

});


