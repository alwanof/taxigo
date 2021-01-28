<template>
    <card class="flex flex-col items-center justify-center">

        <GmapMap
              ref="map"
              :center="center"
              :zoom="10"
              :draggable="true"
              style="width: 100%; height: 400px" >
              <GmapMarker
                :clickable="true"
                v-for="marker in markers"
                :key="marker.id"
                :name="marker.name"
                :icon="marker.icon"
                :position="marker.position" >
                <gmap-info-window :position="marker.position" :opened="true">
                  {{marker.name}}
                </gmap-info-window>

              </GmapMarker>
            </GmapMap>
    </card>
</template>

<script>
window.Vue = require('vue');
  // Parse Here


export default {
     name: "DriverMap",
    props: [
        'card'

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],
    data() {
        return {
            //drivers:[],
            title:'foo',
            center: {
                lat: 41.1374382,
                lng: 28.7547977,
            },
            markers: []
        }
    },

    created() {

        this.listen();
        this.getDrivers();


    },
    methods: {
        getDrivers(){
            var lat=(parseFloat(this.card.authUser.settings['coordinate_lat'])!=0)?parseFloat(this.card.authUser.settings['coordinate_lat']):41.1374382;
            var lng=(parseFloat(this.card.authUser.settings['coordinate_lng'])!=0)?parseFloat(this.card.authUser.settings['coordinate_lng']):28.7547977;
           this.center.lat=lat;
           this.center.lng=lng;

            axios.get('/api/drivers/'+this.card.authUser.id)
            .then(res => {
                //this.drivers=res.data;
                res.data.forEach(item=>{
                    var element={}
                    if(item.lat){
                        element.position={lat:item.lat,lng:item.lng}
                        element.icon=(item.busy==1)?'/images/car-busy.png':'/images/car-active.png';
                        element.id=item.id;
                        element.name=item.name;
                        this.markers.push(element);

                    }

                });
            });
        },
        listen(){
            const Parse = require('parse');
            Parse.initialize("REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV", "VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn");
            Parse.serverURL = "https://smartaxi.b4a.io";

            var Client = new Parse.LiveQueryClient({
                applicationId: 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
                serverURL: 'wss://' + 'smartaxi.b4a.io', // Example: 'wss://livequerytutorial.back4app.io'
                javascriptKey: 'VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn'
            });

                const query = new Parse.Query("Stream");
                query.equalTo("model", "Driver");
                switch (this.card.authUser.level) {
                    case 1:
                        query.equalTo("meta.agent", this.card.authUser.id);
                        break;
                    case 2:
                        query.equalTo("meta.office", this.card.authUser.id);
                        break;
                    default:
                        break;
                }

                Client.open();
                var subscription = Client.subscribe(query);
                subscription.on("create", (feedDoc) => {
                    let index = this.markers.findIndex(
                    (o) => o.id === feedDoc.attributes.pid
                    );
                    axios.get('/api/fetch/drivers/'+feedDoc.attributes.pid)
                        .then((res) => {
                            if(res.data){
                                 var element={}
                                element.position={lat:res.data.lat,lng:res.data.lng}
                                element.icon=(res.data.busy==1)?'/images/car-busy.png':'/images/car-active.png';
                                element.id=res.data.id;
                                element.name=res.data.name;
                                if(res.data.lat){
                                    if(index==-1){
                                        if(res.data.busy==2){
                                            this.markers.push(element);
                                        }

                                    }else{
                                        if(res.data.busy==0){
                                            this.markers.splice(index,1);
                                        }else{
                                            Vue.set(this.markers, index, element);
                                        }

                                    }


                                }

                            }

                        });



                });


        },
    },

    mounted() {
        //
    },
}
</script>
