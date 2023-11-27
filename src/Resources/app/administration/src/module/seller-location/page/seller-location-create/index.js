const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
import template from './seller-location-create.html.twig'
import './seller-location.scss'


Component.register('seller-location-create', {
    template,
    inject: [
      'repositoryFactory',
      'HyperlocalApiService'
  ],
    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },
    data(){
        return{
            shippingLocation: null,
            sellerCollection: [],
            lat: null,
            lng: null,
            adminId: null
        }
    },
    computed: {
        shippingLocationRepository(){
            return this.repositoryFactory.create('marketplace_hyperlocal_shipping_location')
        },
        sellerRepository() {
            return this.repositoryFactory.create('marketplace_seller')
        }
    },
    created(){
        this.getShippingLocationEntity()
        this.getSellers()
        this.getAdminUser()
        setTimeout(() => this.initAutocomplete(), 2000);
    },
    methods: {
        getAdminUser(){
          let criteria = new Criteria();
          criteria.addFilter(Criteria.equals('admin',1));
          criteria.addFilter(Criteria.equals('active',1));
          let userRepository = this.repositoryFactory.create('user');
          userRepository.search(criteria,Shopware.Context.api).then((result)=>{
            this.adminId = result[0].id;
            console.log(this.adminId)
          })
        },
        getShippingLocationEntity(){
            this.shippingLocation = this.shippingLocationRepository.create(Shopware.Context.api)
        },
        getSellers() {
            const sellerSearchCriteria = new Criteria();
            sellerSearchCriteria.addAssociation('customer');
            this.sellerRepository.search(sellerSearchCriteria, Shopware.Context.api)
            .then(result => {
                this.sellerData = result;
                this.sellerData.forEach(element => {
                   this.sellerCollection.push({'id':element.customer.id, 'sellerName':element.customer.firstName +' '+ element.customer.lastName});
                });
            })

        },
        onClickSave() {
            
            if(this.shippingLocation.customerId == undefined) {
                this.createNotificationInfo({
                    title: 'warning',
                    message: 'select a seller'
                })
                return
            }
            if(this.shippingLocation.location == undefined) {
                this.createNotificationInfo({
                    title: 'warning',
                    message: 'enter the location'
                })
                return
            }
            this.HyperlocalApiService.addSellerLocation(this.shippingLocation).then(result=>{
              if(result == true){
                this.createNotificationSuccess({
                  title: 'success',
                  message: 'shipping added successfully'
                })
                this.$router.push({ name: 'seller.location.list' });
              } else{
                this.createNotificationInfo({
                  title: 'info',
                  message: 'Location Not Found'
                })
              }
            })
        },
        initAutocomplete() {
         const map = new google.maps.Map(document.getElementById("map"), {
              center: {
                lat: -33.8688,
                lng: 151.2195
              },
              zoom: 13,
              mapTypeId: "roadmap"
            }); // Create the search box and link it to the UI element.
            
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input); // Bias the SearchBox results towards current map's viewport.
          
            map.addListener("bounds_changed", () => {
              searchBox.setBounds(map.getBounds());
            });
            let markers = []; // Listen for the event fired when the user selects a prediction and retrieve
            // more details for that place.
          
            searchBox.addListener("places_changed", () => {
              const places = searchBox.getPlaces();
          
              if (places.length == 0) {
                return;
              } // Clear out the old markers.
          
              markers.forEach(marker => {
                marker.setMap(null);
              });
              markers = []; // For each place, get the icon, name and location.
          
              const bounds = new google.maps.LatLngBounds();
              places.forEach(place => {
                if (!place.geometry) {
                  console.log("Returned place contains no geometry");
                  return;
                }
                // get lat
                this.lat = place.geometry.location.lat();
                // get lng
                this.lng = place.geometry.location.lng();
          
                const icon = {
                  url: place.icon,
                  size: new google.maps.Size(71, 71),
                  origin: new google.maps.Point(0, 0),
                  anchor: new google.maps.Point(17, 34),
                  scaledSize: new google.maps.Size(25, 25)
                }; // Create a marker for each place.
          
                markers.push(
                  new google.maps.Marker({
                    map,
                    icon,
                    title: place.name,
                    position: place.geometry.location
                  })
                );
          
                if (place.geometry.viewport) {
                  // Only geocodes have viewport.
                  bounds.union(place.geometry.viewport);
                } else {
                  bounds.extend(place.geometry.location);
                }
              });
              map.fitBounds(bounds);
            });
        }
         
    }
})