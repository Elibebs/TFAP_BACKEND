<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//Backoffice
Route::post('/systemuser/login', 'SystemUsersController@login')->name('systemuser.login');
Route::get('/list/website/auto_parts', 'AutoPartsController@listWebAutoParts')->name("list.web.auto_parts");
Route::get('/system/autopart/image/{name}','ImageController@mShowPartImage')->name("show.system.autopart.image");
//Rename this route to the specific image been loaded
Route::get('/image/website/{id}', 'ImageController@showImage')->name("show.image");
Route::get('/category_image/website/{name}', 'ImageController@showCategoryImage')->name("show.category.image");

//systemuser
Route::get('/systemuser/image/{id}', 'ImageController@showSystemUserImage')->name("show.systemuser.image");


Route::get('/years', 'YearController@index')->name('years');


// web
  //categories
  // Route::post('/create/categories', 'CategoriesController@addCategory')->name("category.create");
  // Route::get('/list/categories', 'CategoriesController@listCategory')->name("category.list");

  //subcategories
  Route::get('/list/subcategories', 'SubCategoriesController@listSubCategory')->name("subcategory.list");
  Route::get('/categories/group', 'SubCategoriesController@getPartsGroup')->name("parts.list");

  Route::get('/category/{uid}/subcategories', 'SubCategoriesController@getCategorySubcats')->name("category.subcategory");
  Route::get('web/category/subcategories', 'CategoriesController@getCategorySubcategoriesData')->name("category.subcategory");


//MOBILE APP APIs
Route::post('/mobile/user/login', 'Auth\LoginController@loginUser')->name('user.login');
Route::post('/mobile/add/cart', 'CartController@addCart')->name("add.cart_item");
Route::get('/mobile/view/cart/', 'CartController@mViewCart')->name("view.cart_item");
Route::get('/mobile/view/{id}/delivery_address', 'DeliveryAddressController@viewDeliveryAddress')->name("view.delivery_address");
Route::get('/mobile/remove/cart/item', 'CartController@removeCartItem')->name("remove.cart_item");
Route::post('/mobile/user/signup', 'Authcontroller@userRegister')->name('user.signup');
Route::get('/mobile/categories', 'CategoriesController@mlistCategories')->name("m.categories");
Route::get('/mobile/player_id', 'DeviceController@getPlayerId')->name("device.player_id");
Route::get('/mobile/subcategory/{uid}/autoparts', 'AutoPartsController@getAutoSubcats')->name("mobile.category.subcategory");
Route::get('/mobile/category/{uid}/subcategories/grouped','SubCategoriesController@mlistCategorySubcatories')->name("mobile.category.subcategories.grouped");
Route::get('/mobile/autopart/image/{name}', 'ImageController@mShowPartImage')->name("mobile.show.part.image");
Route::get('/mobile/home/more/autoparts/grouped','AutoPartsController@mGetHomeMoreAutoPartsGrouped')->name('home.more.autoparts.grouped');
Route::get('/mobile/home/mostpopular/autoparts/grouped','AutoPartsController@mGetHomeMostPopularAutoPartsGrouped')->name('home.mostpopular.autoparts.grouped');
Route::get('/mobile/home/newestarrivals/autoparts/grouped','AutoPartsController@mGetHomeNewestArrivalsAutoPartsGrouped')->name('home.newstarrivals.autoparts.grouped');
Route::get('/mobile/search/suggestions','SearchController@getSuggestions')->name('search.suggestions');
Route::get('/mobile/search','SearchController@index')->name('search');
Route::get('/mobile/search/recent/list','SearchController@getRecentSearchList')->name('search.recent.list');
Route::get('/mobile/autopart/{id}/related/autopart/list','AutoPartsController@mGetRelatedAutopartList')->name('mobile.related.autopart');

Route::middleware(
  ['user.access_token', 'user.session_id']
)->group(function () {
  Route::post('/mobile/user/verify', 'Authcontroller@verifyUserPin')->name('user.verify_pin');
  Route::get('/mobile/user/profile', 'Authcontroller@userProfile')->name('user.profile');
  Route::post('/mobile/user/profile/update', 'Authcontroller@updateUserProfile')->name('user.profile_update');
  Route::get('/mobile/view/user/profile', 'DeliveryAddressController@viewProfile')->name("view.profile");

  Route::post('/mobile/add/delivery_address', 'DeliveryAddressController@addDeliveryAddress')->name("add.delivery_address");
  Route::post('/mobile/edit/{id}/delivery_address', 'DeliveryAddressController@editDeliveryAddress')->name("edit.delivery_address");
  Route::get('/mobile/view/{id}/delivery_address', 'DeliveryAddressController@viewDeliveryAddress')->name("view.delivery_address");
  Route::get('/mobile/list/delivery_address', 'DeliveryAddressController@listDeliveryAddress')->name("list.delivery_address");
  Route::get('/mobile/remove/delivery_address', 'DeliveryAddressController@removeDeliveryAddress')->name("remove.delivery_address");

  Route::get('/mobile/change/delivery_address/status', 'DeliveryAddressController@setDefaultDeliveryAddress')->name("delivery_address.default");
  Route::get('/mobile/user/signout', 'Authcontroller@logoutUser')->name('user.logout');

  //customer.order
  Route::post('/mobile/add/order', 'OrderController@addOrder')->name("add.order");
  Route::post('/mobile/add/payment_info', 'TransactionsController@addShippingInfo')->name("add.payment_info");
});


//BACKOFFICE APIs
Route::post('/systemuser/password/change', 'SystemUsersController@changeSystemUserPassword')->name('systemuser.password.change');
Route::middleware(
    ['system_user.access_token', 'system_user.session_id','checkpermissions']
)->group(function () {
    //System.SystemUser endpoints
Route::post('/systemuser/create', 'SystemUsersController@addSystemUser')->name("systemusers.create");
Route::post('/systemuser/{id}/edit', 'SystemUsersController@editSystemUser')->name("systemusers.edit");
Route::get('/systemuser/disable', 'SystemUsersController@disableSystemUser')->name("systemusers.disable");
Route::post('/systemuser/search', 'SystemUsersController@searchSystemUser')->name("systemusers.search");
Route::post('/systemuser/export', 'SystemUsersController@SystemUserExport')->name("systemusers.export");
Route::get('/systemuser/list', 'SystemUsersController@listSystemUsers')->name("systemusers.list");
Route::get('/systemuser/{id}/delete','SystemUsersController@delete')->name("sytemusers.delete");
Route::post('/systemuser/password/reset', 'SystemUsersController@systemUserPasswordReset')->name('systemuser.password.reset');
Route::get('/systemuser/signout', 'SystemUsersController@logout')->name('systemuser.logout');



//System.Role endpoints
Route::get('/systemuser/permission/group', 'RoleController@getAllPermissionsGroup')->name('systemuser.permission.group');
Route::get('/systemuser/roles','RoleController@getAllRoles')->name('systemuser.role.group');
Route::post('/systemuser/create/role', 'RoleController@addSystemUserRole')->name("systemusers.create.role");
Route::get('/role/{id}/view/details', 'RoleController@viewRoleDetails')->name("view.role.details");
Route::post('/systemuser/edit/role', 'RoleController@editSystemUserRole')->name("systemusers.edit.role");
Route::get('/systemuser/delete/{id}/role', 'RoleController@deleteSystemUserRole')->name("systemusers.delete.role");
Route::post('/systemuser/search/role', 'RoleController@searchSystemUserRole')->name("systemusers.search.role");
Route::post('/systemuser/export/roles', 'RoleController@SystemUserExportRoles')->name("systemusers.export.roles");


//Sytsem.AuditTrail endpoints
Route::post('/systemuser/search/audit', 'AuditTrailController@searchSystemUserAudit')->name("search.audit");
Route::post('/systemuser/export/audit', 'AuditTrailController@SystemUserExportAudit')->name("export.audit");
Route::get('/systemuser/audit','AuditTrailController@getAllAudit')->name('audit.list');


//Auto Parts endpoints
  //Parts.category
Route::post('/create/category', 'CategoriesController@addCategory')->name("category.create");
Route::get('/list/category', 'CategoriesController@listCategory')->name("category.list");
Route::post('/edit/category', 'CategoriesController@editCategory')->name("category.edit");
Route::post('/delete/category', 'CategoriesController@deleteCategory')->name("category.delete");
Route::post('/add/category/image', 'CategoriesController@addCategoryImage')->name("add.categroy.image");
Route::post('/delete/category/image', 'CategoriesController@deleteCategoryImage')->name("image.delete");
Route::post('/search/category', 'CategoriesController@searchCategory')->name("search.category");



 //Parts.Subcategory
 Route::post('/create/subcategory', 'SubCategoriesController@addSubCategory')->name("subcategory.create");
 Route::get('/list/subcategory', 'SubCategoriesController@listSubCategory')->name("subcategory.list");
 Route::post('/edit/subcategory', 'SubCategoriesController@editSubCategory')->name("subcategory.edit");
 Route::post('/delete/subcategory', 'SubCategoriesController@deleteSubCategory')->name("subcategory.delete");
 Route::post('/add/subcategory/image', 'SubCategoriesController@addSubCategoryImage')->name("add.subcategroy.image");
 Route::get('/list/subcategory/{uid}/autopart/details', 'SubCategoriesController@subCategoryAutoPartDetails')->name("subcateory.autopart.details");

 
  //Parts.carMake
  Route::post('/create/carmake', 'CarMakeController@addCarMake')->name("carmake.create");
  Route::get('/list/carmake', 'CarMakeController@listCarMake')->name("carmake.list");
  Route::get('/list/make/models','CarMakeController@listMakeWithModels')->name("make.model.list");
  Route::post('/edit/carmake', 'CarMakeController@editCarMake')->name("carmake.edit");
  Route::post('/delete/carmake', 'CarMakeController@deleteCarMake')->name("carmake.delete");

  //Parts.carModel
    Route::post('/create/car_model', 'CarModelController@addCarModel')->name("car_model.create");
    Route::get('/list/car_model', 'CarModelController@listCarModel')->name("car_model.list");
    Route::get('/search/make/model/year', 'CarModelController@searchMakeModelYear')->name("make.model.year.search");
    Route::post('/edit/car_model', 'CarModelController@editCarModel')->name("car_model.edit");
    Route::post('/delete/car_model', 'CarModelController@deleteCarModel')->name("car_model.delete");

    Route::post('/add/car_year', 'YearController@addCarYear')->name("car_years.add");
    Route::post('/delete/car_year', 'YearController@deleteCarYear')->name("car_year.delete");
    Route::get('/list/car_years', 'YearController@listCarYears')->name("car_years.list");

    //Customer Address
    Route::get('/view/{id}/delivery_address', 'DeliveryAddressController@viewDeliveryAddress')->name("view.delivery_address");
    Route::post('/change/delivery_address/status', 'DeliveryAddressController@setDefaultDeliveryAddress')->name("delivery_address.default");
  

      //Parts.Auto_Parts
      Route::post('/create/auto_parts', 'AutoPartsController@addBasicAutoPartInfo')->name("basic_auto_parts.create");
      Route::get('/list/auto_parts', 'AutoPartsController@listAutoParts')->name("auto_parts.list");
      Route::post('/edit/auto_parts', 'AutoPartsController@editBasicAutoPartInfo')->name("auto_parts.edit");
      Route::post('/delete/auto_parts', 'AutoPartsController@deleteAutoParts')->name("auto_parts.delete");
      Route::get('/view/autopart', 'AutoPartsController@viewAutoPart')->name("view.auto_part");
      Route::post('/auto_part/restock', 'AutoPartsController@reStockPart')->name("part.restock");

      Route::post('/add/auto_parts/image', 'AutoPartsController@addAutoPartImage')->name("add.auto_parts.image");
      Route::post('/update/auto_parts/image', 'AutoPartsController@updateAutoPartImage')->name("update.auto_parts.image");
      Route::post('/delete/auto_parts/image', 'AutoPartsController@deleteImage')->name("image.delete");

      Route::post('/create/auto_parts/specifications', 'AutoPartsController@addSpecifications')->name("create.auto_parts.specifications");
      Route::post('/delete/auto_parts/specs', 'AutoPartsController@deleteSpecs')->name("specs.delete");
      
      Route::post('/auto_parts/search', 'AutoPartsController@searchAutoParts')->name("auto_parts.search");
      Route::get('/auto_parts/publish', 'AutoPartsController@publishAutoParts')->name("auto_parts.publish");

      //CLIENTS.CUSTOMERS
      Route::get('/list/users', 'SystemUsersController@listCustomers')->name("customers.list");
      Route::get('/disable/user', 'SystemUsersController@disableCustomer')->name("customer.disable");
      Route::get('/view/user/details', 'SystemUsersController@viewUserDetails')->name("view.user.details");
   

      //CARTS
      Route::get('/list/cart', 'CartController@listCartItem')->name("cart.index");
      Route::get('/view/customer/cart/items', 'CartController@viewUserCartItems')->name("cart.view");
      Route::post('/search/cart', 'CartController@searchCarts')->name("cart.search");
      Route::post('/delete/cart', 'CartController@deleteCart')->name("cart.delete");      



      Route::get('/list/orders', 'OrderController@listOrders')->name("orders.index");
      Route::get('/view/customer/order/items', 'OrderController@viewOrders')->name("customer.order.view");
      Route::post('/search/order', 'OrderController@searchOrders')->name("order.search"); 
      
      //dashboard
      Route::get('/dashboard/list/category/items', 'CategoriesController@listCategoryItems')->name("dashboard.category.items.list");
      Route::get('/dashboard/list/top_grossing/items', 'DashboardController@getTopGrossingItems')->name("dashboard.top_grossing.items.list");
      Route::get('/dashboard/customer/segregation', 'SystemUsersController@customerType')->name("dashboard.customer.type");
      Route::get('/dashboard/revenue/statistics', 'DashboardController@getRevenueStatistics')->name("dashboard.revenue.statistics");
      Route::get('/dashboard/top/statistics','DashboardController@getTopStatistics')->name('dashboard.top.statistics');
    

});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


