<template>

    <div class="py-3">
        <!-- Status-->
            <div class="row border rounded p-2 mt-3">
              <div class="col">
                  {{trans('Order No')}}. #{{feed.id}}
                <span class="text-warning mx-3">
                  <i :class="statusIcon(feed.status)"></i> {{statusLabel(feed.status)}}
                </span>
              </div>
            </div>
            <div class="row p-2 mt-3">
              <div class="col-12">
                <span class="text-muted">{{trans('DATE')}}</span>
                <br>
                {{convertUTCDateToLocalDate(new Date(feed.created_at))}}
              </div>
              <div class="col-12">
                  <div class="progress" v-if="feed.status==0" style="height: 3px;">
                        <div class="progress-bar progress-bar-striped bg-secondary" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==1" style="height: 3px;" >
                        <div class="progress-bar progress-bar-striped bg-warning" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==91" style="height: 3px;" >
                        <div class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==2" style="height: 3px;" >
                        <div class="progress-bar progress-bar-striped bg-info" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress" v-if="feed.status==21" style="height: 3px;" >
                        <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

              </div>
            </div>
            <!-- PRICE -->
            <div class="row border bg-dark text-muted rounded p-3 mt-3" v-show="feed.total">
              <div class="col-2">
                <i class="fas fa-coins fa-2x"></i>
              </div>
              <div class="col-7">
                <h4>{{tran('TOTAL AMOUNNT')}}</h4>
              </div>
              <div class="col-3">
                <h4>{{feed.total}} {{office.settings.currency}}</h4>
              </div>
            </div>
            <!-- Approve / Reject -->
            <div class="row border text-center rounded p-2" v-if="feed.status==3">
              <div class="col-12">
                  <button type="button" @click="approve()" class="btn btn-success btn-sm  mx-1">{{trans('Approve')}}</button>
                  <button type="button" @click="reject()" class="btn btn-sm btn-danger  mx-1">{{trans('Reject')}}</button>
              </div>

            </div>
             <!-- Path-->
            <div class="row border rounded p-2 mt-3" v-if="feed.status==21">
              <div class="col-2 py-3">
                  <div class="text-center">
                    <i class="far fa-user-circle"></i>
                  </div>
                  <div class="vl"></div>
                  <div class="text-center">
                    <i class="fas fa-car"></i>
                  </div>
              </div>
              <div class="col-10">
                <div class="row">
                  <div class="col-12 border-bottom p-2">
                    <p class="text-muted m-0">{{trans('Driver Details')}}</p>
                    <div class="row">
                      <div class="col-2">
                        <img :src="'/storage/'+feed.driver.avatar" class="img-thumbnail" :alt="feed.driver.name">
                      </div>
                      <div class="col-6">
                        <p class="fw-bold m-0">{{feed.driver.name}}</p>
                        <span class="fa fa-star checked"></span>
                        <span class="fa fa-star checked"></span>
                        <span class="fa fa-star checked"></span>
                        <span class="far fa-star"></span>
                        <span class="far fa-star"></span>
                      </div>
                      <div class="col-4">
                        <a :href="'tel:'+feed.driver.phone" class="btn btn-sm btn-success">
                          <i class="fas fa-phone-alt"></i>
                          {{tran('CALL')}}
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 p-2">
                   <p class="text-muted m-0">{{trans('Car Model')}}</p>
                    <div class="row">
                      <div class="col-8">
                        <p class="fw-bold m-0">{{feed.driver.taxiNo}}</p>
                        <small>{{feed.driver.taxi}}t</small>
                      </div>
                      <div class="col-4">
                        <button type="button" class="btn btn-sm btn-secondary">
                          {{feed.driver.taxiColor}}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- MAP -->
            <div class="row border rounded p-2 mt-3" v-if="feed.status==21">
              <div class="col">
                <tracking-component :parse="parse" :driver="feed.driver"></tracking-component>
              </div>
            </div>
            <!-- Path From/To-->
            <div class="row border rounded p-2 mt-3">
              <div class="col-2 py-3">
                  <div class="text-center">
                    <i class="fas fa-map-marker-alt"></i>
                  </div>
                  <div class="vl"></div>
                  <div class="text-center">
                    <i class="far fa-bookmark"></i>
                  </div>
              </div>
              <div class="col-10">
                <div class="row">
                  <div class="col-12 border-bottom p-2">
                    <p class="text-muted m-0">{{trans('From')}}</p>
                    <div class="row">

                      <div class="col-12" v-show="feed.from_address">
                        <p class="fw-bold m-0">{{feed.from_address}}</p>

                      </div>

                    </div>
                  </div>
                  <div class="col-12 p-2" v-show="feed.to_address">
                   <p class="text-muted m-0">{{trans('To')}}</p>
                    <div class="row">
                      <div class="col-12">
                        <p class="fw-bold m-0">{{feed.to_address}}</p>

                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>

         <div class="row mt-4" v-if="cancelValid(feed.status)">

                <div class="col mt-3 text-center">
                    <button type="button" @click="cancel()" class="btn btn-sm btn-outline-dark">{{trans('Cancel')}}</button>
                </div>
            </div>



    </div>
</template>

<script>
  // Parse Here
//const Parse = require('parse');
//Parse.initialize("REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV", "VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn");
//Parse.serverURL = "https://smartaxi.b4a.io";

/*var Client = new Parse.LiveQueryClient({
    applicationId: 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
    serverURL: 'wss://' + 'smartaxi.b4a.io', // Example: 'wss://livequerytutorial.back4app.io'
    javascriptKey: 'VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn'
});*/

//const query = new Parse.Query("Stream");
//query.equalTo("model", "Order");
//Client.open();
//var subscription = Client.subscribe(query);
 import json from '../../lang/app.json';
    export default {
        name:"Order-Component",
        props:['office','agent','order','lang','parse'],
        data() {
            return {
                feed:null,
                local:json[this.lang]
            }
        },

        created() {
            this.feed=this.order;
            this.listen();
            //console.log('here');

        },
        methods: {

            trans(key){
               if(typeof this.local[key] != 'undefined') {
                   return this.local[key];
               }
               return '-';
            },
            listen(){
                const Parse = require('parse');
                Parse.initialize(this.parse.PARSE_APP_ID, this.parse.PARSE_JS_KEY);
                Parse.serverURL = this.parse.PARSE_SERVER_URL;

                var Client = new Parse.LiveQueryClient({
                    applicationId: this.parse.PARSE_APP_ID,
                    serverURL: 'wss://' + this.parse.PARSE_SERVER_LQ_URL, // Example: 'wss://livequerytutorial.back4app.io'
                    javascriptKey: this.parse.PARSE_JS_KEY
                });

                const query = new Parse.Query("Stream");
                query.equalTo("model", "Order");
                Client.open();
                var subscription = Client.subscribe(query);
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
                 case 22:
                    label=this.trans('Trip Started');
                    break;
                case 3:
                    label=this.trans('Waiting Customer Approve');
                    break;
                case 9:
                    label=this.trans('Done');
                    break;
                case 90:
                    label=this.trans('Trip Failed');
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
                    case 22:
                        icon='far fa-caret-square-right';
                        break;
                    case 3:
                        icon='fas fa-user-clock';
                        break;
                    case 9:
                        icon='fas fa-check-double';
                        break;
                    case 90:
                        icon='fas fa-exclamation-triangle';
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


