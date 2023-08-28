<?php

namespace App\TemaFirst\Traits;

trait ClientTrait
{
	/**************************************************************/

	protected $apiAddDeliveryAddressParams = [
        "name",
		"address",
        "phone_number",
        "region",
        "town",
    ];
    
    protected $apiUpdateDeliveryAddressParams = [
		"address",
		"postal_address",
        "phone_number",
        "address_id"
    ];
    
    protected $apiDeleteAddressParams = [
        "address_id"
    ];
    
    protected $apiGetUserProfileParams = [
        "user_id"
    ];
    protected $apiDefaultAddressParams = [
        "address_id",
        "user_id",
    ];
}
