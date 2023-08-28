<?php

namespace App\TemaFirst\Traits;

trait OrderTrait
{
	/**************************************************************/

	protected $apiAddOrderItemParams = [

        "shipping_id",
        "customer_id",
        "device_id",
    ];

    protected $apiGetCustomerParams = [
		"customer_id",
    ];
    
}
