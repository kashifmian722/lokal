import ApiService from 'src/core/service/api.service';

class HyperlocalApiService extends ApiService
{
    constructor(httpClient, loginService, apiEndpoint = 'wk.marketplace') {
        super(httpClient, loginService, apiEndpoint);
    }
    addSellerLocation(data) {
        let apiRoute = `${this.getApiBasePath()}/add/seller/location`
        return this.httpClient.post(
            apiRoute, {data:data},
            {
                headers: this.getBasicHeaders()
            }
        ).then(response => {
            return ApiService.handleResponse(response);
        });
    }
}
export default HyperlocalApiService