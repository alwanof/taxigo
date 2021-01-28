Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'taxi-order',
      path: '/taxi-order',
      component: require('./components/Tool'),
    },
  ]);

});



