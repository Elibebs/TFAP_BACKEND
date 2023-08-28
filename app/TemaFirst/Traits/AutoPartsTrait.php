<?php

namespace App\TemaFirst\Traits;

trait AutoPartsTrait
{
	/**************************************************************/


	protected $apiAddAutoPartsBasicInfoParams = [
        "name",
        "subcategory_id",
        "model_id",
        "condition",
        "quantity",
        "unit_price",
        "seller_id",
        "description",
    ];
    
    protected $apiEditAutoPartsParams = [
        "name",
        "subcategory_id",
        "model_id",
        "condition",
        "quantity",
        "unit_price",
        "seller_id",
        "description",
        "auto_part_id"
    ];
    
    protected $apiAddImageParams = [
        "auto_part_id",
        "base64"
    ];
    protected $apiRestockAutoPartsParams = [
        "auto_part_id",
        "quantity"
    ];
    
    protected $apiDeleteImageParams = [
        "image_id"
    ];
    protected $apiEditAutoPartImageParams = [
        "part_image_name",
        "base64"
    ];

    protected $apiDeleteAutoPartParams = [
        "part_id"
    ];

    protected $apiDeleteSpecsParams = [
        "specification_id"
    ];
    protected $apiAddSpecificationParams = [
        "key",
        "value",
        "auto_part_id"
    ];
    protected $apiAutoPartPublishParams = [
        "auto_part_id",
    ];

    protected $apiAutoPartParams = [
        "auto_part_id",
    ];

    protected $apiGeParams = [
		"user_id"
	];

}
