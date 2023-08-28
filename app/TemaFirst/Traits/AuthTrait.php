<?php

namespace App\TemaFirst\Traits;

trait AuthTrait
{
	/**************************************************************/

	protected $apiSystemUserLoginParams = ["email", "password"];
	protected $apiUserRegisterParams = [
		"phone_number",
		"email",
		"name",
		"account_type",
	];
	protected $apiSystemUserChangePasswordParams = [
		"new_password",
		"confirm_password",
		"email",
		"token"
    ];
    protected $apiSystemUserResetPasswordParams = [
		"new_password",
		"confirm_password"
	];

	/**************************************************************/

	protected $apiAddSystemUserParams = [
		"name",
		"email",
		"role_id",
	];
	protected $apiUpdateSystemUserParams = [
		"name",
		"email",
		"role_id",
		"system_user_id",
	];

	/**************************************************************/

	protected $apiUserLoginParams = [
		"phone_number"
	];

	/*******************************************************************/ 
	protected $apiAddCategoryParams = [
		"name",
	];

	protected $apiEditCategoryParams = [
		"name",
		"category_id"
	];
	protected $apiDeleteCategoryParams = [
		"category_id"
	];
	protected $apiDeleteCategoryImageParams = [
		"category_id"
	];

	/******************************************************************/ 
	
	protected $apiAddSubCategoryParams = [
		"name",
		"category_id"
	];

	protected $apiEditSubCategoryParams = [
		"name",
		"sub_category_id",
	];
	protected $apiDeleteSubCategoryParams = [
		"sub_category_id"
	];


	protected $apiAddSystemUserRoleParams = [
		"name",
		"permissions"
	];

	protected $apiDeleteSystemUserRoleParams = [
		"role_id"
	];

	protected $apiAddSystemUserDisableParams = [
		"system_user_id",
	];
	protected $apiChangeCustomerStatusParams = [
		"user_id",
	];

	protected $apiUpdateSystemUserRoleParams = [
		"role_id",
		"name",
		"permissions"
	];

	protected $apiResetPasswordParams = [
		"system_user_id"
	];

	/************************************/
	protected $apiAddCarMakeParams = [
		"title",
		"description"
	];
	protected $apiEditCarMakeParams = [
		"title",
		"description",
		"make_id"
	];

	protected $apiAddCarModelParams = [
		"name",
		"make_id",
		"year_id"
	];
	protected $apiEditCarModelParams = [
		"name",
		"make_id",
		"year_id",
		"car_model_id"
	];
	protected $apiDeleteCarModelParams = [
		"model_id"
	];

	protected $apiDeleteCarMakeParams = [
		"make_id"
	];


	protected $apiGetUserProfileParams = [
		"user_id"
	];

	protected $apiAddCategoryImageParams = [
		"category_id",
		"base64"
	];

	protected $apiAddSubCategoryImageParams = [
		"subcategory_id",
		"base64"
	];

	protected $apiVerificationPinParams = ["pin"];
}
