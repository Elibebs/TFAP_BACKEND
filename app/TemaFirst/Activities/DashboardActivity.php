<?php

namespace App\TemaFirst\Activities;

use App\TemaFirst\Api\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\TemaFirst\Events\ErrorEvents;
use App\TemaFirst\Utilities\Constants;
use App\TemaFirst\Repos\OrderRepo;
use App\TemaFirst\Repos\UserRepo;
use App\TemaFirst\Repos\AutoPartsRepo;



class DashboardActivity extends BaseActivity
{
	protected $apiResponse;
    protected $orderRepo;
    protected $userRepo;
    protected $autoPartRepo;

	public function __construct(ApiResponse $apiResponse, OrderRepo $orderRepo, UserRepo $userRepo, AutoPartsRepo $autoPartRepo)
    {
		$this->apiResponse = $apiResponse;
        $this->orderRepo = $orderRepo;
        $this->userRepo = $userRepo;
        $this->autoPartRepo = $autoPartRepo;
    }

    public function getTopStatistics($filters)
    {
            $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_DAY;

            $orders = $this->orderRepo->getPaymentMadeOrdersGroup($filters);
            $users = $this->userRepo->getUsersGroup($filters);
            $users_previous_total = $this->userRepo->getUsersGroupPreviousCount($filters);
            $autoparts = $this->autoPartRepo->getNewUploadedAutoPartsGroup($filters);
            
            $data = [];
            
            $sales_revenue_activities = [];
            $sales_revenue_total = 0;
            
            $items_sold_activities = [];
            $items_sold_total = 0;
    
            if(isset($orders) && count($orders) > 0){
                foreach($orders as $items)
                {
                    $sales_revenue = 0;
                    $item_sold = 0;
                    foreach($items as $item)
                    {
                        $sales_revenue += $item->totalAmount();
                        $item_sold += $item->totalItems();
                    }
                    array_push($sales_revenue_activities,$sales_revenue);
                    $sales_revenue_total += $sales_revenue;
                    
                    array_push($items_sold_activities,$item_sold);
                    $items_sold_total += $item_sold;
                }
            }

           /**
            * Get customer activities
            * Calculate customer total
            */
            $customer_activities = [];
            $customer_total = 0;
            if(isset($users) && count($users) > 0)
            {
                foreach($users as $items)
                {
                    $items_count = count($items);
                    array_push($customer_activities,  $items_count);
                    $customer_total += $items_count;
                }
            }


            /**
            * Get auto part activities
            * Calculate autopart total
            */
            $autopart_activities = [];
            $autopart_total = 0;
            $autopart_quantity_sold = 0;
            if(isset($autoparts) && count($autoparts) > 0)
            {
                foreach($autoparts as $items)
                {
                    $auto_part_count = 0;
                    $qty_sold = 0;
                    foreach($items as $item)
                    {
                        $auto_part_count += $item->quantity;
                        $qty_sold += $item->quantitySold();
                    }
                    array_push($autopart_activities,$auto_part_count);
                    $autopart_total += $auto_part_count;
                    $autopart_quantity_sold += $qty_sold;
                }
            }


            Log::info($sales_revenue_activities);

            $data['sales_revenue']['activities'] = $sales_revenue_activities;
            $data['sales_revenue']['total'] = $sales_revenue_total;
            $data['sales_revenue']['average'] = $sales_revenue_total == 0 ? 0  : ($sales_revenue_total / count($sales_revenue_activities));

            $data['items_sold']['activities'] = $items_sold_activities;
            $data['items_sold']['total'] = $items_sold_total;
            $data['items_sold']['average'] = $items_sold_total == 0  ? 0 : ($items_sold_total / count($items_sold_activities));

            $increase = $customer_total - $users_previous_total;
            $data['customer']['activities'] = $customer_activities;
            $data['customer']['total'] = $customer_total;
            $data['customer']['percentage_increase'] = $increase <= 0  ? 0 : (($increase / $users_previous_total) * 100);

            $data['autopart_uploaded']['activities'] = $autopart_activities;
            $data['autopart_uploaded']['total'] = $autopart_total;
            $data['autopart_uploaded']['quantity_sold'] = $autopart_quantity_sold;


            $message = "Dashboard top statistics retrieved successfully.";
            return $this->apiResponse->success($message, ["data" => $data] );

    }

    public function getTopGrossingItems($filters)
    {

        $auto_parts = $this->autoPartRepo->getTopGrossingAutoParts($filters);

        if(isset($auto_parts) && count($auto_parts) > 0)
        {
            foreach($auto_parts as $key => $auto_part)
            {
                $auto_part["revenue_generated"] = $auto_part->amountSold();
                $auto_part["items_sold"] = $auto_part->quantitySold();
                
                unset($auto_parts[$key]["order_items"]);
                unset($auto_part->orderItems);
            }
        }
       
        $message = "Top grossing items results";
        Log::notice($message);
        return $this->apiResponse->success($message, ["data" => $auto_parts]);
        
    }

    public function getRevenueStatistics($filters)
    {
        $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_WEEK;

        $current_orders = $this->orderRepo->getPaymentMadeOrdersByWeekMonth($filters);
        $previous_orders = $this->orderRepo->getPaymentMadeOrdersByWeekMonthPrevious($filters);
        $sales_revenues = [];

        $count = isset($current_orders) ? count($current_orders) : 0;
        if($count > 0)
        {
            for ($i=0; $i < $count; $i++) { 
                
                $current_sales_revenue = 0;
                $previous_sales_revenue = 0;
                if(isset($current_orders[$i])){

                }
                if(isset($previous_orders[$i])){

                }
                array_push($sales_revenues,[
                        'date' => $current_orders[$i]->created_at,
                        'current' => $current_orders[$i]->totalAmount(), 
                        "previous" => isset($previous_orders[$i]) ? $previous_orders[$i]->totalAmount() : 0
                        ]);
            }
        }
   
        $message = "Showing Statistics on Revenue generated";
        Log::notice($message);
        return $this->apiResponse->success($message, ["data" => $sales_revenues]);
    }

    // public function getRevenueStatistics($filters)
    // {
    //     $filter_date = isset($filters['filter_date']) ? $filters['filter_date'] : Constants::FILTER_DATE_WEEK;

    //     $current_orders = $this->convertOrdersData($this->orderRepo->getPaymentMadeOrdersGroupByWeekMonth($filters));
    //     $previous_orders = $this->convertOrdersData($this->orderRepo->getPaymentMadeOrdersGroupByWeekMonthPrevious($filters));

    //     Log::notice('-----------------CURRENT PAYMENT MADE-----------------');
    //     Log::notice($current_orders);
    //     $sales_revenues = [];
    //     if($filter_date == Constants::FILTER_DATE_MONTH){
    //         for ($i=0; $i < 4; $i++) { 

    //             Log::notice('-----------------CURRENT ORDER NUMBER PAYMENT MADE-----------------');
    //             //Log::notice($current_orders[$i]);

    //             $current_sales_revenue = 0;
    //             $previous_sales_revenue = 0;
    //             if(isset($current_orders[$i]) && isset($current_orders[$i]) > 0)
    //             {
    //                 $current_items = $current_orders[$i];
    //                 foreach($current_items as $item){
    //                     $current_sales_revenue += $item->totalAmount();
    //                 }
    //             }
    //             if(isset($previous_orders[$i]) && isset($previous_orders[$i]) > 0)
    //             {
    //                 $previous_items = $previous_orders[$i];
    //                 foreach($previous_items as $item){
    //                     $previous_sales_revenue += $item->totalAmount();
    //                 }
    //             }
                
    //             array_push($sales_revenues,['date' => "week","current" => $current_sales_revenue, "previous" => $previous_sales_revenue]);
                
    //         }
    //     }else{

    //     }


       
    //     $message = "Showing Statistics on Revenue generated";
    //     Log::notice($message);
    //     return $this->apiResponse->success($message, ["data" => $sales_revenues]);
    // }

    // function convertOrdersData($orders)
    // {
    //     $new_data = [];
    //     if(isset($orders) && count($orders) > 0){
    //         foreach($orders as $items)
    //         {
    //             array_push($new_data,$items);
    //         }
    //     }

    //     return $new_data;
    // }


}