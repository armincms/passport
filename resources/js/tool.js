Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'passport',
      path: '/passport',
      component: require('./components/Tool'),
    },
  ])
})
