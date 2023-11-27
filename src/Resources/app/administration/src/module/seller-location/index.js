const { Module } = Shopware;

import './page/seller-location-list'
import './page/seller-location-create'

import enGB from './snippet/en-GB';
import deDE from './snippet/de-DE';

Module.register('seller-location', {
    type: 'plugin',
    title: 'wk-mp-hyperlocal.seller-location.titleLabel',
    description: 'wk-mp-hyperlocal.seller-location.description',
    snippets: {
        'en-GB': enGB,
        'de-De': deDE
    },
    routes: {
        'list':{
            component: 'seller-location-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index'
            },
        },
        'create': {
            component: 'seller-location-create',
            path: 'create',
            meta: {
                parentPath: 'seller.location.list'
            }
        }
    },
    settingsItem: [
        {
            name: 'mp-hyperlocal-seller-location',
            label: 'wk-mp-hyperlocal.seller-location.menuItemLabel',
            to: 'seller.location.list',
            group: 'plugins',
            icon: 'default-object-marketing'
        }
    ]
})