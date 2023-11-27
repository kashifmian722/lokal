const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

import template from './seller-location-list.html.twig';

Component.register('seller-location-list', {
    template,
    inject: [
        'repositoryFactory',
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
        return {
            total: null,
            shippingLocation: null,
            adminId: null
        }
    },
    computed:{
        locationRepository(){
            return this.repositoryFactory.create('marketplace_hyperlocal_shipping_location');
        },
        columns(){
            return [
                {
                    property: 'sellerName',
                    dataIndex: 'sellerName',
                    label: this.$t('wk-mp-hyperlocal.list.columnCollection[0]'),
                    allowResize: true,
                    sortable: false,
                },
                {
                    property: 'location',
                    dataIndex: 'location',
                    label: this.$t('wk-mp-hyperlocal.list.columnCollection[1]'),
                    allowResize: true,
                    sortable: false,
                },
                {
                    property: 'latitude',
                    dataIndex: 'lattitude',
                    label: this.$t('wk-mp-hyperlocal.list.columnCollection[2]'),
                    allowResize: true,
                    sortable: false,
                },
                {
                    property: 'longitude',
                    dataIndex: 'longitude',
                    label: this.$t('wk-mp-hyperlocal.list.columnCollection[3]'),
                    allowResize: true,
                    sortable: false,
                }
            ]
        }
    },
    created(){
        this.getShippingLocation();
        this.getAdminUser()
    },
    methods: {
        getShippingLocation(){
            let criteria = new Criteria();
            criteria.addAssociation('customer')
            this.locationRepository.search(criteria,Shopware.Context.api).then(result =>{
                this.shippingLocation = result;
                console.log(this.shippingLocation)
                this.total = result.total;
            })
        },
        getAdminUser(){
            let criteria = new Criteria();
            criteria.addFilter(Criteria.equals('admin',1));
            criteria.addFilter(Criteria.equals('active',1));
            let userRepository = this.repositoryFactory.create('user');
            userRepository.search(criteria,Shopware.Context.api).then((result)=>{
              this.adminId = result[0].id;
              console.log(this.adminId)
            })
        }
    }
    
})