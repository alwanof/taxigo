<template>
    <div class="container text-left">

        <div class="row">
            <div class="col-12 text-center">
                <i :class="statusIcon(feed.status)+' fa-2x'"></i> <span class="text-danger lead">{{statusLabel(feed.status)}}</span>
            </div>

            <div class="col-8">
                <div class="text-muted">{{trans('DATE')}}</div>
                {{convertUTCDateToLocalDate(new Date(feed.created_at))}}
            </div>
            <div class="col-4">
                <div class="text-muted">{{trans('Order No')}}</div>
                {{feed.id}}


            </div>

            <div class="col-12 mt-1">
                    <div class="progress" v-if="feed.status==0" style="height:0.5rem">
                        <div class="progress-bar progress-bar-striped bg-secondary" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==1" style="height:0.5rem" >
                        <div class="progress-bar progress-bar-striped bg-warning" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==91" style="height:0.5rem" >
                        <div class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==2" style="height:0.5rem" >
                        <div class="progress-bar progress-bar-striped bg-info" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==21" style="height:0.5rem" >
                        <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

            </div>
        </div>
        <div class="row mt-4 py-2 border border-secondary" v-if="feed.status==21">
                <div class="col-12 text-muted">
                    {{trans('Driver Details')}}
                    <table class="table">
                        <thead class="thead-dark">
                            <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{trans('Name')}}</th>
                            <th scope="col">{{trans('Car Model')}}</th>
                            <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <th scope="row">
                                <img class="img-thumbnail rounded-circle" :src="'/storage/'+feed.driver.avatar" alt="" width="64" >
                            </th>
                            <td>{{feed.driver.name}}</td>
                            <td>
                                {{feed.driver.taxi}} / {{feed.driver.taxiColor}}
                            </td>
                            <td>
                                 <a :href="'tel:'+feed.driver.phone" class="btn btn-lg btn-outline-success float-right">
                                     <i class="fas fa-phone-square-alt"></i>
                                </a>
                            </td>
                            </tr>
                        </tbody>
                    </table>
                </div>


        </div>
        <div class="row mt-3">

            <div class="col-12">
                <div class="text-muted">
                    {{trans('Your Trip')}}

                    <span class="float-right h2 text-danger font-bold" v-show="feed.offer">
                        <div class="spinner-grow text-danger" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        {{feed.offer}} {{office.settings.currency}}
                    </span>
                </div>
            </div>
            <div class="col-12">

                <div class="col-12" v-show="feed.from_address">
                    <div class="text-danger">{{trans('From')}}</div>
                    {{feed.from_address}}
                </div>
                <div class="col-12" v-show="feed.to_address">
                     <div class="text-danger">{{trans('To')}}</div>
                    {{feed.to_address}}
                </div>

            </div>


        </div>


        <div class="row mt-3" v-if="feed.status==3">
            <div class="col-12">
                   <button type="button" @click="approve()" class="btn btn-success  btn-block mb-1">{{trans('Approve')}}</button>

                   <button type="button" @click="reject()" class="btn btn-danger btn-block">{{trans('Reject')}}</button>
                </div>

        </div>
         <div class="row mt-4" v-if="cancelValid(feed.status)">

                <div class="col mt-3 text-center">
                    <button type="button" @click="cancel()" class="btn btn-sm btn-outline-dark">{{trans('Cancel')}}</button>
                </div>
            </div>
            <hr>
            <div v-if="feed.status==21">
                <tracking-component :driver="feed.driver"></tracking-component>

            </div>

    </div>
</template>

<script>
  // Parse Here
const Parse = require('parse');
Parse.initialize("REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV", "VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn");
Parse.serverURL = "https://smartaxi.b4a.io";

var Client = new Parse.LiveQueryClient({
    applicationId: 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
    serverURL: 'wss://' + 'smartaxi.b4a.io', // Example: 'wss://livequerytutorial.back4app.io'
    javascriptKey: 'VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn'
});

const query = new Parse.Query("Stream");
query.equalTo("model", "Order");
Client.open();
var subscription = Client.subscribe(query);
 import json from '../../lang/app.json';
    export default {
        name:"Order-Component",
        props:['office','agent','order','lang'],
        data() {
            return {
                feed:null,
                local:json[this.lang]
            }
        },

        created() {
            this.feed=this.order;
            this.listen();

        },
        methods: {

            trans(key){
               if(typeof this.local[key] != 'undefined') {
                   return this.local[key];
               }
               return '-';
            },
            listen(){
                subscription.on("create", (feedDoc) => {
                    //console.log(feedDoc.attributes);
                    let index = (this.feed.id==feedDoc.attributes.pid);
                    if(index){
                        axios
                        .get( '/api/orders/get/' + feedDoc.attributes.pid)
                        .then((res) => {
                            this.feed=res.data;
                        });

                    }

                });
            },
            approve(){
                axios.get('/api/order/customer/approve/'+this.feed.id).then((res) => {
                    //console.log(res.data);
                });
            },
            reject(){
                axios.get('/api/order/customer/reject/'+this.feed.id).then((res) => {
                    //console.log(res.data);
                    window.location.href = '/taxi/'+this.office.email;
                });
            },
            cancel(){

                axios.get('/api/orders/cancel/'+this.feed.id).then((res) => {
                    window.location.href = '/taxi/'+this.office.email;
                });
            },
            cancelValid(status){
                var valid=[0,1,12,2,3];
                return valid.includes(status);

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
            convertUTCDateToLocalDate(date) {
            var d = new Date();
            var offset = d.getTimezoneOffset() / 60;
            var hours = date.getHours();
            date.setHours(hours - offset);
            return date.toLocaleString();
        }
        },
    }
</script>


