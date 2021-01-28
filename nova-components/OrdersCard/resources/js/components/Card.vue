<template>
<div>

          <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <h1 class="text-center text-3xl text-80 font-light">
                <i class="fas fa-cog fa-spin text-danger" v-show="loading"></i> {{__('Current Orders')}}
                <button type="button" v-show="card.authUser.settings.auto_fwd_order==1"  class="btn btn-default btn-danger">{{__("Auto-Forward")}}</button>
            </h1>

        </div>


            <table class="table w-full table-default">
                <thead>
                    <tr>
                        <th>{{__("ID")}}</th>
                        <th>{{__("Customer")}}</th>
                        <th>{{__("From/TO")}}</th>
                        <th>{{__("Details")}}</th>
                        <th width="35%">{{__("Action")}}</th>
                    </tr>
                </thead>
                <tbody v-for="order in orders" :key="order.id">
                    <tr class="text-left">
                        <td>
                            <i :class="statusIcon(order.status)+' text-danger'"></i> {{order.id}}
                            </td>
                        <td>
                            <span style="white-space: nowrap;"><i class="fas fa-user-alt"></i> {{order.name}}</span><br>
                            <span style="white-space: nowrap;"><i class="far fa-envelope"></i> {{order.email}}</span><br>
                            <span style="white-space: nowrap;"><i class="fas fa-phone-alt"></i> {{order.phone}}</span>
                            </td>
                        <td>
                            <i class="fas fa-location-arrow"></i><span style="white-space: nowrap;" :title="order.from_address"> {{order.from_address.substring(0, 32)}}</span><br>

                            <span v-if="order.to_lat>0 && order.to_lng>0 && order.from_lat>0 && order.from_lng>0">
                                <i class="fas fa-map-marker-alt"></i><span style="white-space: nowrap;" :title="order.to_address"> {{order.to_address.substring(0, 32)}}</span><br>
                                <i class="fas fa-map-marked-alt"></i> {{distance(order.from_lat,order.from_lng,order.to_lat,order.to_lng,'K')}}KM
                            </span><br>
                            <span v-show="order.offer">
                                <i class="fas fa-comments-dollar"></i> {{order.offer}} {{card.authUser.settings['currency']}}
                            </span>



                        </td>
                        <td>
                             <i class="far fa-clock"></i> {{convertUTCDateToLocalDate(new Date(order.created_at))}}

                            <span v-show="order.driver">
                                <i class="fas fa-taxi"></i> {{(order.driver)?order.driver.name:''}}
                            </span>
                            <br>
                           <span style="font-weight:bold">{{statusLabel(order.status)}}</span>



                        </td>
                        <td >

                            <span class="m-2" v-if="order.status==0 || order.status==1">
                                <button class="btn btn-default" @click="reject(order)"><i class="far fa-window-close"></i> </button>
                            </span>
                            <span class="m-2" v-if="order.status==0 && card.authUser.settings.offer_enabled==0">

                                <button class="btn btn-default" @click="approve(order)"><i class="far fa-check-circle"></i></button>
                            </span>
                            <span class="m-2" v-if="order.status==0 && card.authUser.settings.offer_enabled==1 && order.to_address!=null">
                                <input type="number" placeholder="Ex. 50" v-model="offer" class="w-50 form-control form-input form-input-bordered">
                                <button class="btn btn-default btn-primary mr-4" @click="sendOffer(order)">{{__("Send")}}</button>
                            </span>

                            <span class="m-2" v-if="order.status==1">
                                <select name="driver" v-model="driver" class="w-50 form-control form-input form-input-bordered">
                                    <option value="0" selected disabled>Select Driver</option>
                                    <option v-for="driver in order.drivers" :key="driver.id" :value="driver.id">{{driver.name}}({{driver.distance}}km)</option>

                                </select>
                                <button class="btn btn-default btn-primary mr-4" @click="selectDriver(order)">{{__("Apply")}}</button>
                            </span>
                            <span class="m-2" v-if="order.status==2">

                                <button class="btn btn-default btn-sm btn-primary" @click="undo(order)">
                                    <i class="fas fa-undo"></i> {{__("UNDO")}}
                                </button>
                            </span>

                        </td>


                    </tr>

                </tbody>
            </table>



    </card>

</div>

</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
                    if (!Notification) {
                        alert('Desktop notifications not available in your browser. Try Chromium.');
                        return;
                    }

                    if (Notification.permission !== 'granted')
                        Notification.requestPermission();
                });
window.Vue = require('vue');
import VueNativeNotification from 'vue-native-notification'

Vue.use(VueNativeNotification, {
  // Automatic permission request before
  // showing notification (default: true)
  requestOnNotify: true
});



export default {
    name: "TaxiOrderCard",
    props: [
        'card',

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],
 data() {
        return {
            orders:[],
            offer:0,
            driver:0,
            url:window.location.hostname,
            loading:false

        }
    },
    created() {
        this.listen("Order");
    },
    methods: {
        Notify(title,body){
            var notification = new Notification(title, {
                        icon: 'https://www.kindpng.com/picc/m/169-1699400_svg-png-icon-free-android-notification-icon-png.png',
                        body: body,
                    });
        },

        trans(key){
            if(Nova.config.translations[key]!== undefined){
                return Nova.config.translations[key];
            }
            return '-';

        },

        undo(order){
            this.loading=true;
            axios.get('/api/order/office/undo/'+order.id).then((res) => {
                this.loading=false;
            });

        },
        reject(order){
            this.loading=true;
            axios.get('/api/order/office/reject/'+order.id).then((res) => {
                this.loading=false;
            });

        },
        approve(order){
            this.loading=true;
            axios.get('/api/order/office/approve/'+order.id).then((res) => {
                this.loading=false;
            });

        },
        sendOffer(order){
            this.loading=true;
             axios.get('/api/order/office/send/'+this.offer+'/to/'+order.id).then((res) => {
                this.loading=false;
            });

        },
        selectDriver(order){
            this.loading=true;
            axios.get('/api/order/office/select/'+this.driver+'/to/'+order.id).then((res) => {
                this.loading=false;
            });

        },

        getOrders(){
            this.loading=true;
            axios.get('/api/orders/'+this.card.authUser.id).then((res) => {
                this.orders=res.data;
                this.loading=false;
            });
        },
        parseBroadcast({pid:pid,model:model,action:action},meta=null){
            const Stream = Parse.Object.extend('Stream');
            const myNewObject = new Stream();

            myNewObject.set('pid', pid);
            myNewObject.set('model', model);
            myNewObject.set('action', action);
            myNewObject.set('meta', meta);

            myNewObject.save().then(
                (result) => {
                    console.log('Success Broadcast')
                },
                (error) => {
                    if (typeof document !== 'undefined') document.write(`Error while creating Stream: ${JSON.stringify(error)}`);
                    console.error('Error while creating Stream: ', error);
                }
            );

        },
        listen(){
             // Parse Here
            const Parse = require('parse');
            Parse.initialize("REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV", "VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn");
            Parse.serverURL = "https://smartaxi.b4a.io";

            var Client = new Parse.LiveQueryClient({
                applicationId: 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
                serverURL: 'wss://' + 'smartaxi.b4a.io', // Example: 'wss://livequerytutorial.back4app.io'
                javascriptKey: 'VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn'
            });
            const streamQuery = new Parse.Query("Stream");
            streamQuery.equalTo("model", "Order");
            streamQuery.equalTo("meta.office", this.card.authUser.id);
            Client.open();
            var subscription = Client.subscribe(streamQuery);
                subscription.on("create", (feedDoc) => {
                    let index = this.orders.findIndex(
                    (o) => o.id === feedDoc.attributes.pid
                    );
                    //if (index > -1) {
                    axios
                        .get( '/api/orders/get/' + feedDoc.attributes.pid)
                        .then((res) => {
                        if (feedDoc.attributes.action == "U") {
                            Vue.set(this.orders, index, res.data);
                            this.Notify('From/من'+res.data.name,'Order Updated !/تحديث طلبية');
                        } else if (feedDoc.attributes.action == "C") {
                            //console.log('C',feedDoc.attributes.action);
                            this.orders.unshift(res.data);
                             this.Notify('From/من'+res.data.name,'New order/طلبية جديدة');
                        } else if (feedDoc.attributes.action == "D") {
                            this.orders.splice(index, 1);
                        }
                        });

                });


        },
        makeid(length) {
            var result           = '';
            var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for ( var i = 0; i < length; i++ ) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        },
        makeint(length) {
            var result           = '';
            var characters       = '0123456789';
            var charactersLength = characters.length;
            for ( var i = 0; i < length; i++ ) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        },
        statusLabel(status){
            var label='-';
            switch (status) {
                case 0:
                    label=this.trans('New');
                    break;
                case 1:
                    label=this.trans('Accepted');
                    break;
                case 2:
                    label=this.trans('Waiting Driver Approve');
                    break;
                case 21:
                    label=this.trans('On the way');
                    break;
                case 3:
                    label=this.trans('Waiting Customer Approve');
                    break;
                case 9:
                    label=this.trans('Done');
                    break;
                case 91:
                    label=this.trans('Office Rejected');
                    break;
                case 92:
                    label=this.trans('Customer Rejected');
                    break;
                case 93:
                    $label=this.trans('No-Res. from Driver');
                    break;
                case 94:
                    label=this.trans('No-Res. from Customer');
                    break;
                 case 95:
                    label=this.trans('Canceled');
                    break;
                case 99:
                    label=this.trans('Canceled from Customer');
                    break;

                default:
                    break;
            }
            return label;
        },
        statusIcon(status){
                var icon='far fa-circle';
                switch (status) {
                    case 0:
                        icon='fas fa-circle';
                        break;
                    case 1:
                        icon='fas fa-check-circle';
                        break;
                    case 2:
                        icon='far fa-pause-circle';
                        break;
                    case 21:
                        icon='fas fa-car';
                        break;
                    case 3:
                        icon='fas fa-user-clock';
                        break;
                    case 9:
                        icon='fas fa-check-double';
                        break;
                    case 91:
                        icon='fas fa-minus-circle';
                        break;
                    case 92:
                        icon='far fa-times-circle';
                        break;
                    case 93:
                        $icon='far fa-times-circle';
                        break;
                    case 94:
                        icon='fab fa-gg-circle';
                        break;
                    case 95:
                        icon='fab fa-gg-circle';
                        break;
                    case 99:
                        icon='far fa-times-circle';
                        break;

                    default:
                        break;
                }
                return icon;
            },
        distance(lat1, lon1, lat2, lon2, unit) {
            if ((lat1 == lat2) && (lon1 == lon2)) {
                return 0;
            }
            else {
                var radlat1 = Math.PI * lat1/180;
                var radlat2 = Math.PI * lat2/180;
                var theta = lon1-lon2;
                var radtheta = Math.PI * theta/180;
                var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                if (dist > 1) {
                    dist = 1;
                }
                dist = Math.acos(dist);
                dist = dist * 180/Math.PI;
                dist = dist * 60 * 1.1515;
                if (unit=="K") { dist = dist * 1.609344 }
                if (unit=="N") { dist = dist * 0.8684 }
                console.log( Math.round(dist*100)/100);
                return Math.round(dist*100)/100;
            }
        },
        convertUTCDateToLocalDate(date) {
            var d = new Date();
            var offset = d.getTimezoneOffset() / 60;
            var hours = date.getHours();
            date.setHours(hours - offset);
            return date.toLocaleString();
        }
    },

    mounted() {
        //const users = Parse.Object.extend("User");
        //const query = new Parse.Query(users);
        this.getOrders();

    },
}
</script>

