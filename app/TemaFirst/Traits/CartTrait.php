<?php

namespace App\TemaFirst\Traits;

trait CartTrait
{
	/**************************************************************/

	protected $apiAddCartItemParams = [
		"part_id",
    "quantity",
    ];

    protected $apiViewCartItemsParams = [
		"cart_id",
    ];

    protected $apiGetCustomerParams = [
		"user_id",
    ];

    protected $apiRemoveCartItemParams =[
        "cart_item_id",
    ];

    protected $apiRemoveCartParams =[
      "cart_id",
  ];
    
}
