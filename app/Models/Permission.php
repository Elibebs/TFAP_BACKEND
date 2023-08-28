<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Permission extends \Spatie\Permission\Models\Permission
{

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public static function defaultPermissions()
    {
        return [
             //Systemusers
            'view:system_user',
            'create:system_user',
            'edit:system_user',
            'reset_password:system_user',
            'change_password:system_user',
            'disable:system_user',
            'search:system_user',
            'delete:system_user',

            //Roles
            'view:roles',
            'add:roles',
            'edit:roles',
            'delete:roles',
            'search:roles',
            'group_permissions:roles',
            'group:roles',
            'get_details:roles',

            //Audit Trail
            'list:audit',
            'export:audit',
            'search:audit',


            //category perms
            'list:category',
            'create:category',
            'edit:category',
            'delete:category',
            'add_image:category',
            'remove_img:category',
            'search:category',

            //Subcategory perms
            'list:subcategory',
            'create:subcategory',
            'edit:subcategory',
            'delete:subcategory',
            'add_image:subcategory',
            'view_autopart_details:subcategory',

            //carMake perms
            'list:carmake',
            'create:carmake',
            'edit:carmake',
            'delete:carmake',

            //carmodel perms
            'view_model_list:car_model',
            'create:car_model',
            'edit:car_model',
            'delete:car_model',
            
            //car_years
            'add:year',
            'delete:year',
            'list:year',

            //customer address
           'view_list:delivery_address',
           'change_status:delivery_address',

           //customers
           'view_list:customers',
           'enable_disable:customers',
           'details:customers',

            //Auto Parts
            'list:auto_parts',
            'create:auto_parts',
            'edit:auto_parts',
            'delete:auto_parts',
            'search:auto_parts',

            'add_image:auto_parts',
            'delete_image:auto_parts',

            'create_auto_part_specs:auto_parts',
            'delete_auto_part:auto_parts',

            'search_auto_part_specs:auto_parts',
            'publish_auto_part:auto_parts',

            //carts
            'view_list:carts',
            'items:carts',
            'search:carts',
            'delete:carts',

            //orders
            'view:orders',
            'items:orders',
            'search:orders',

            //dashboard
            'view_category_items:dashboard',
            'top_grossing:dashboard',
            'customer_segregation:dashboard',
            'revenue_statistics:dashboard',
            'top_statistics:dashboard'

        ];
    }

    public static function routePermissionsMap(){
        return [
            //Systemusers 
            'systemusers.list'=>'view:system_user',
            'systemusers.create'=>'create:system_user',
            'systemusers.edit'=>'edit:system_user',
            'systemusers.disable'=>'disable:system_user',
            'systemuser.password.reset'=>'reset_password:system_user',
            'systemuser.password.change'=>'change_password:system_user',
            'systemusers.search'=>'search:system_user',
            'sytemusers.delete'=>'delete:system_user',

             //Roles
             'roles.index'=>'view:roles',
             'roles.create'=>'add:roles',
             'roles.edit'=>'edit:roles',
             'roles.delete'=>'delete:roles',
             'roles.search'=>'search:roles',
             'systemuser.permission.group'=>'group_permissions:roles',
             'systemuser.role.group'=>'group:roles',
             'systemusers.edit.role'=>'get_details:roles',

             //Audit Trail
            'roles.index'=>'list:audit',
            'export.audit'=>'export:audit',
            'search.audit'=>'search:audit',

                
            //category perms
            'category.list'=>'list:category',
            'category.create'=>'create:category',
            'category.edit'=>'edit:category',
            'category.delete'=>'delete:category',
            'add.categroy.image'=>'add_image:category',
            'image.delete'=>'remove_img:category',
            'search.category'=>'search:category',

                
            //Subcategory perms
            'subcategory.list'=>'list:subcategory',
            'subcategory.create'=>'create:subcategory',
            'subcategory.edit'=>'edit:subcategory',
            'subcategory.delete'=>'delete:subcategory',
            'add.subcategroy.image'=>'add_image:subcategory',
            'subcateory.autopart.details'=>'view_autopart_details:subcategory',
                
            //carMake perms
            'carmake.list'=>'list:carmake',
            'carmake.create'=>'create:carmake',
            'carmake.edit'=>'edit:carmake',
            'carmake.delete'=>'delete:carmake',
                
             //carmodel perms
            'car_model.list'=>'list:car_model',
            'car_model.create'=>'create:car_model',
            'car_model.edit'=>'edit:car_model',
            'car_model.delete'=>'delete:car_model',
                
             //Auto Parts
            'auto_parts.list'=>'list:auto_parts',
            'basic_auto_parts.create'=>'create:auto_parts',
            'auto_parts.edit'=>'edit:auto_parts',
            'auto_parts.delete'=>'delete:auto_parts',
            'auto_parts.search'=>'search:auto_parts',
            
            'add.auto_parts.image'=>'add_image:auto_parts',
            'image.delete'=>'delete_image:auto_parts',
            
            'create.auto_parts.specifications'=>'create_auto_part_specs:auto_parts',
            'specs.delete'=>'delete_auto_part:auto_parts',
            
            'auto_parts.search'=>'search_auto_part_specs:auto_parts',
            'auto_parts.publish'=>'publish_auto_part:auto_parts',


            //carts
            'cart.list'=>'view_list:carts',
            'cart.view'=>'items:carts',
            'cart.search'=>'search:carts',
            'cart.delete'=>'delete:carts',

            //orders
            'orders.list'=>'view:orders',
            'customer.order.view'=>'items:orders',
            'order.search'=>'search:orders',

            //dashboard
            'dashboard.category.items.list'=>'view_category_items:dashboard',
            'dashboard.top_grossing.items.list'=>'top_grossing:dashboard',
            'dashboard.customer.type'=>'customer_segregation:dashboard',
            'dashboard.revenue.statistics'=>'revenue_statistics:dashboard',
            'dashboard.top.statistics'=>'top_statistics:dashboard',
        ];
    }
}
