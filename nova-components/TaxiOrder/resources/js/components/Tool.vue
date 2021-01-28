<template>
    <div>
        <heading class="mb-6">Taxi Order</heading>
        <div>
            <button type="button" @click="createOrder" class="btn btn-default btn-primary">create order</button>
            <button type="button" @click="updateOrder" class="btn btn-default btn-primary">update order</button>
            <button type="button" @click="deleteOrder" class="btn btn-default btn-primary">delete order</button>
        </div>

        <card :foo="moo"
            class="flex">
            <table class="table w-full table-default">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Customer</th>
                        <th>From/TO</th>
                        <th>Info</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody v-for="order in orders" :key="order.id">
                    <tr class="text-center">
                        <td>{{order.id}}</td>
                        <td>
                            {{order.name}}<br>
                            {{order.email}} {{order.phone}}
                            </td>
                        <td>
                            {{order.from_address}}<br>
                            {{order.to_address}}<br>
                            {{order.offer}}

                        </td>
                        <td></td>
                        <td></td>


                    </tr>

                </tbody>
            </table>


        </card>
    </div>
</template>

<script>
window.Vue = require('vue');
  // Parse Here
const Parse = require('parse');
Parse.initialize("REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV", "VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn");
Parse.serverURL = "https://smartaxi.b4a.io";

var Client = new Parse.LiveQueryClient({
    applicationId: 'REhnNlzTuS88KmmKaNuqwWZ3D3KNYurvNIoWHdYV',
    serverURL: 'wss://' + 'smartaxi.b4a.io', // Example: 'wss://livequerytutorial.back4app.io'
    javascriptKey: 'VSDqMVaQWg5HDnFM0oAezLdeDRdfMvdZKhgW7THn'
});
export default {
    name: "TaxiOrder",
    props:['card'],

    data() {
        return {
            orders:[],

        }
    },
    created() {
        this.listen("Order");
        //console.log(JSON.stringify(this));

    },
    methods: {
        createOrder(){
            axios.post('/api/orders/create',{title:'some title 3',desc:'some desc 3'}).then((res) => {
                //this.parseBroadcast({pid:res.data.id,model:'Order',action:'C'});
            });


        },
        updateOrder(){
             axios.post('/api/orders/update',{title:'some title 3',desc:'some desc 3'}).then((res) => {
                //this.parseBroadcast({pid:res.data.id,model:'Order',action:'C'});
            });
        },
        deleteOrder(){
             axios.post('/api/orders/delete',{title:'some title 3',desc:'some desc 3'}).then((res) => {
                //this.parseBroadcast({pid:res.data.id,model:'Order',action:'C'});
            });
        },
        getOrders(){
        axios.get('/api/orders/1').then((res) => {
                this.orders=res.data;
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
            const query = new Parse.Query("Stream");
            query.equalTo("model", "Order");
            Client.open();
             var subscription = Client.subscribe(query);
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
                    } else if (feedDoc.attributes.action == "C") {
                        console.log('C',feedDoc.attributes.action);
                        this.orders.unshift(res.data);
                    } else if (feedDoc.attributes.action == "D") {
                        this.orders.splice(index, 1);
                    }
                    });
                //}
            });
        }
    },

    mounted() {
        //const users = Parse.Object.extend("User");
        //const query = new Parse.Query(users);
        this.getOrders();

    },
}
</script>

<style>
/* Scoped Styles */
</style>
