import { Application } from 'src/core/shopware';
import  HyperlocalApiService from '../core/services/api/hyperlocal-api.services'

Application.addServiceProvider('HyperlocalApiService', (container) => {
    const initContainer = Application.getContainer('init');
    return new HyperlocalApiService(initContainer.httpClient, container.loginService);
});