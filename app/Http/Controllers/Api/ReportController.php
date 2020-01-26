<?php

namespace App\Http\Controllers;

use App\Order;
use App\Region;
use App\Age;
use App\Client;
use App\Report;
use App\SaleCenter;
use App\Department;
use App\SaleChannel;
use App\Referrer;
use App\Agent;
use App\Time;
use function foo\func;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
    public function getAgeCategoryChartData(Request $request){
        ini_set('max_execution_time', 900);
//        $labels = ['младше 20', '20-25', '26-34', '35-44', '45-54', '55-64', 'старше 64'];
        $labels = [];
        $counts = [];
        $sums = [];
        $avgs = [];
        $payout_sums = [];
        $payout_counts = [];
        $cross_sums = [];
//
//        foreach ($labels as $key => $label) {
//            $request->age_category = $label;
//            $orders = self::getFilteredOrdersQuery($request)->get();
//            array_push($counts, count($orders));
//            array_push($sums, $orders->sum('vts_overall_sum'));
//            array_push($avgs, $orders->sum('avg_sum'));
//            array_push($payout_counts, $orders->sum('vts_lost_count'));
//            array_push($payout_sums, $orders->sum('payout_sum'));
//            array_push($cross_sums, $orders->sum('vts_cross_result'));
//        }

        $request->age_category = null;
        $orders = self::getFilteredOrdersQuery($request)
            ->with('client')
            ->get()
            ->groupBy('client.age_category');

        foreach ($orders as $key => $ordersgroup) {
            array_push($labels, $key);
            array_push($counts, count($ordersgroup));
            array_push($sums, $ordersgroup->sum('vts_overall_sum'));
            array_push($avgs, $ordersgroup->sum('avg_sum'));
            array_push($payout_counts, $ordersgroup->sum('vts_lost_count'));
            array_push($payout_sums, $ordersgroup->sum('payout_sum'));
            array_push($cross_sums, $ordersgroup->sum('vts_cross_result'));
        }

        return response()->json([
            'labels' => $labels,
            'counts' => $counts,
            'sums' => $sums,
            'avgs' => $avgs,
            'payout_sums' => $payout_sums,
            'payout_counts' => $payout_counts,
            'cross_cums' => $cross_sums
        ]);
    }

    /*public function getRegionsReport(Request $request) {
        ini_set('max_execution_time', 900);
        if($request->region_id != null && $request->region_id != 'все'){
            $regions[0] = Region::findOrFail($request->region_id);
        } else {
            $regions = Region::all();
        }

        $labels = [];
        $counts = [];
        $ages = [];
        $sums = [];
        $avgs = [];
        $payout_sums = [];
        $payout_counts = [];
        $cross_sums = [];
        $regionss = [];
        $property = '';

        foreach ($regions as $region) {
            $request->region_id = $region->id;
            $orders = self::getFilteredOrdersQuery($request)
                ->with('client')
                ->get()
                ->groupBy('client.age_category');

//            $orders->with(['client' => function($q){
//                $q->groupBy('age_category');
//            }]);
            foreach($orders as $key => $ordersgroup) {
                  $sums[$key] =  self::numberFormat($ordersgroup->sum('vts_overall_sum'));
//                array_push($labels, $region->name);
//                array_push($counts, count($orders));
//                array_push($sums, self::numberFormat($orders->sum('vts_overall_sum')));
//                array_push($avgs, self::numberFormat($orders->sum('avg_sum')));
//                array_push($payout_counts, self::numberFormat($orders->sum('vts_lost_count')));
//                array_push($payout_sums, self::numberFormat($orders->sum('payout_sum')));
//                array_push($cross_sums, self::numberFormat($orders->sum('vts_cross_result')));
                if(!in_array($key,$labels)) {
                    array_push($labels, $key);
                }
            }
            $regionss[$region->name] = array(
                'sums' => $sums
            );
        }
//        $orders = self::getFilteredOrdersQuery($request);

//        $orders->with(['client' => function($q){
//            $q->groupBy('age_category');
//        }]); //->groupBy('age_category');
        $property = 'Город';
        return response()->json([
            'property' => $property,
            'data' => $regionss,
            'labels' => $labels,
            //'ages' => $ages,
            'counts' => $counts,
            'sums' => $sums,
            'avgs' => $avgs,
            'payout_sums' => $payout_sums,
            'payout_counts' => $payout_counts,
            'cross_cums' => $cross_sums
        ]);

    }*/


    public function getRegionsReport(Request $request) {
        ini_set('max_execution_time', 900);
        if($request->region_id != null && $request->region_id != 'все'){
            $regions[0] = Region::findOrFail($request->region_id);
        } else {
            $regions = Region::all();
        }
        $data = [];
        $labels = [];
        $sums = [];
        $regionss = [];
        $property = '';
        foreach ($regions as $region) {
            $request->region_id = $region->id;
            $orders = self::getFilteredOrdersQuery($request)
                //->with('client')//->where('id',$request->age_category)
//                ->whereHas('client', function ($query) use ($request){
//                    if($request->age_category != 2 && $request->age_category != 'все' && $request->age_category != null) {
//                        $query->where('age_category', '=', $request->age_category);
//                    }
//                })
                ->get()
                ->groupBy('client.age_category');
            foreach($orders as $key => $ordersgroup) {
                if(isset($request->type) && $request->type == 1){
                    $data[$key] =  self::numberFormat($ordersgroup->sum('vts_overall_sum'));
                } else {    // kolichestvo
                    $data[$key] =  self::numberFormat(count($orders));
                }

                if(!in_array($key,$labels)) {
                    array_push($labels, $key);
                }
            }
            $regionss[$region->name] = array(
                'data' => $data
            );
        }

        print '<pre>'; print_r($regionss); print '</pre>';

        $property = 'Город';
        return response()->json([
            'property' => $property,
            'data' => $regionss,
            'labels' => $labels,
            'sums' => $sums
        ]);

    }

    public function getAgesReport(Request $request) {
        ini_set('max_execution_time', 15000);
        if($request->region_id != null && $request->region_id != 'все'){
            $regions[0] = Region::findOrFail($request->region_id);
        } else {
            $regions = Region::all();
        }
        $data = [];
        $labels = [];
        $sums = [];
        $regionss = [];
        $property = '';
        foreach ($regions as $region) {
            $request->region_id = $region->id;
            $orders = self::getFilteredOrdersQuery($request)
                //->with('client')//->where('id',$request->age_category)
                ->whereHas('client', function ($query) use ($request){
                    if($request->age_category != 2 && $request->age_category != 'все' && $request->age_category != null) {
                        $query->where('age_category', '=', $request->age_category);
                    }
                    $query->whereHas('region', function ($query) use ($request){
                        if($request->region_id != 'все' && $request->region_id != null) {
                            $query->where('region_id', '=', $request->region_id);
                        }
                    });
                })
                ->get()
                ->groupBy('client.age_category');
            foreach($orders as $key => $ordersgroup) {
                if(isset($request->type) && $request->type == 1){
                    $data[$region->name] =  self::numberFormat($ordersgroup->sum('vts_overall_sum'));
                } else {    // kolichestvo
                    $data[$region->name] =  self::numberFormat(count($ordersgroup));
                }

                $regionss[$key] = array(
                    'data' => $data
                );
            }
            if(!in_array($region->name,$labels)) {
                array_push($labels, $region->name);
            }
        }

        print '<pre>';print_r($regionss);print '</pre>'; exit();

        $property = 'Возраст';
        return response()->json([
            'property' => $property,
            'data' => $regionss,
            'labels' => $labels,
            'sums' => $data
        ]);

    }




































    public function getTest(Request $request) {
        print 'test';exit();
        ini_set('max_execution_time', 900);
        if($request->region_id != null && $request->region_id != 'все'){
            $regions[0] = Region::findOrFail($request->region_id);
        } else {
            $regions = Region::all();
        }
        $data = [];
        $labels = [];
        $sums = [];
        $regionss = [];
        $property = '';

//        $users = DB::table('orders')
//            ->LeftJoin('times', 'times.id', '=', 'orders.time_id')->whereBetween('times.date',['2018-04-20', '2018-04-20'])
//            ->LeftJoin('clients', 'clients.id', '=', 'orders.client_id')//->groupBy('clients.age_category')
//            ->LeftJoin('regions', 'regions.id', '=', 'clients.region_id')
//            //->select(DB::raw('count(*) as vts_overall_sums'))
//            //->where('status', '<>', 1)
//            //->whereBetween('times.date', ['2018-04-20', '2018-04-20'])
//            //->select('SELECT SUM(orders.vts_overall_sum)')
//               // ->sum('orders.vts_overall_sum')
//            //->groupBy('status')
////            ->selectRaw('
////                SUM(orders.vts_overall_sum) AS type_a,
////                COUNT(orders.id) AS count
////            ')
//            //->select(DB::raw('count(*) as count'))
//            ->get()
//            ->groupBy('clients.age_category');
//
//        foreach($users as $u){
////            $u = array()$u;
////            print $u;
////            exit();
//            print_r($u);print '<br><br><br>';
//        }
//        //print_r($users);
//        exit();



//        $request->from_date = '2019-04-20';
//        $request->to_date = '2019-04-20';
//        $request->from = '2019-04-20';
//        $request->to = '2019-04-20';
        $orders = self::getFilteredOrdersQuery($request)
//            ->whereHas('client',function ($query) use ($data){
//                //...
//                //$query->groupBy('clients.region_id');
//                //$query->whereHas('region')->groupBy('clients.region_id');
//            })
//            ->whereHas('region',function ($query) use ($data){
//                //...
//                //$query->groupBy('clients.region_id');
//                //$query->whereHas('region')->groupBy('clients.region_id');
//                //$query->groupBy('regions.name');
//            })
                //->select('orders.ogpo_vts_result')
//            ->selectRaw('
//                SUM(vts_overall_sum) AS sumssssssss,
//                COUNT(id) AS countccccccc
//            ')

            //->get();
            ->groupBy(['region_name','age_category'])->sum('vts_overall_sum');

            //->groupBy(['region.name','client.age_category']);  //,'client.region_id'
        // 2 varianta: 1) summirovat ili ne podklyachat region i age_category - no togda nado budet

            //->groupBy(['region_id','age_category']);

//            ->groupBy('age_category')
//            ->selectRaw('sum(vts_overall_sum) as sum, age_category')
//            //->groupBy(['region_id','age_category'])
//            ->pluck('sum','age_category');

            //->get()->groupBy(['age_category','region_name']);

//            ->groupBy('region_name')
//            ->selectRaw('*, sum(vts_overall_sum) as sum')
//            ->get();




        //->groupBy(['client_id','region_id']);
        //dd($orders);
        //print '<pre>'; print_r($orders); print '</pre>'; exit();
        //$query->Join('regions','regions.id','=','clients.region_id'); //->groupBy('clients.region_id');.
//        $i = 0;
//        foreach($orders as $order){
//            print '<pre>';print_r($order);print '</pre>';
//            //exit();
//            //$or = Order::find($order->id);
//            //print 'Обновление записи №'.$i.'- region_id = '.$order->client->region_id.'<br>, Пожалуйста подождите ...';
//            //$or->region_id = $order->client->region_id;
//            //$or->update();
//            //print 'Запись обновлена №'.$i.'- region_id = '.$order->client->region_id.'<br>,';
//            //$i++;
//        }


//exit();
//
//        print '<pre>'; print_r($orders); print '</pre>'; exit();
        $property = 'Возраст';
        return response()->json([
            'property' => $property,
            'data' => $orders,
            //'labels' => $labels,
            //'sums' => $data
        ]);

        /*foreach($orders as $key => $order){
            print $key.'===<br>';
            foreach($order as $k => $o){
                print_r($o);
            }
            print '<br><br><br>';
        }

        exit();
       //print '<pre>'; print_r($orders); print '</pre>'; exit();

        foreach ($regions as $region) {
            $request->region_id = $region->id;
            $orders = self::getFilteredOrdersQuery($request)
                //->with('client')//->where('id',$request->age_category)
                ->whereHas('client', function ($query) use ($request){
                    if($request->age_category != 2 && $request->age_category != 'все' && $request->age_category != null) {
                        $query->where('age_category', '=', $request->age_category);
                    }
                    $query->whereHas('region', function ($query) use ($request){
                        if($request->region_id != 'все' && $request->region_id != null) {
                            $query->where('region_id', '=', $request->region_id);
                        }
                    });
                })
                ->get()
                ->groupBy('client.age_category');
            foreach($orders as $key => $ordersgroup) {
                $sums[$key] = $ordersgroup;
//                if(isset($request->type) && $request->type == 1){
//                    $data[$region->name] =  self::numberFormat($ordersgroup->sum('vts_overall_sum'));
//                } else {    // kolichestvo
//                    $data[$region->name] =  self::numberFormat(count($ordersgroup));
//                }

                $regionss[$key] = array(
                    'data' => $data
                );
            }
            if(!in_array($region->name,$labels)) {
                array_push($labels, $region->name);
            }
        }

        $property = 'Возраст';
        return response()->json([
            'property' => $property,
            'data' => $regionss,
            'labels' => $labels,
            'sums' => $data
        ]);*/

    }





    public function getTest2(Request $request) {
        ini_set('max_execution_time', 1500000000);
        $data = [];
        $labels = [];
        $sums = [];
        $regionss = [];
        $property = '';
        print 'sql';
        $orders = self::getFilteredOrdersQuery($request)
            ->whereHas('client',function ($query) use ($data){
                //...
                //$query->groupBy('clients.region_id');
                //$query->whereHas('region')->groupBy('clients.region_id');
            })
            ->whereHas('region',function ($query) use ($data){
                //...
                //$query->groupBy('clients.region_id');
                //$query->whereHas('region')->groupBy('clients.region_id');
                //$query->groupBy('regions.name');
            })
        ->get();
        $i = 0;
print 'Count:'.count($orders).'; ';
        foreach($orders as $order){
            //print '<pre>';print_r($order);print '</pre>';
            //exit();
            //print $order->client->region->name;exit();
            $or = Order::find($order->id);
            print 'Обновление записи №'.$order->id.'- age_category = '.$order->client->age_category.'<br>, Пожалуйста подождите ...';
            $or->region_id = $order->client->region_id;
            $or->region_name = $order->client->region->name;
            $or->age_category = $order->client->age_category;
            $or->update();
            print 'Запись обновлена №'.$order->id.'- age_category = '.$order->client->age_category.'<br>,';
            $i++;
            //exit();
        }
        print 'end time-'.date('Y.m.d H:i:s');

exit();

    }





    public function getSaleCentersReport(Request $request) {
        $centers = SaleCenter::all();

        $labels = [];
        $counts = [];
        $sums = [];
        $avgs = [];
        $payout_sums = [];
        $payout_counts = [];
        $cross_sums = [];

        foreach ($centers as $center) {
            $request->sale_center = $center->id;
            $orders = self::getFilteredOrdersQuery($request)->get();
            array_push($labels, $center->name);
            array_push($counts, count($orders));
            array_push($sums, self::numberFormat($orders->sum('vts_overall_sum')));
            array_push($avgs, self::numberFormat($orders->sum('avg_sum')));
            array_push($payout_counts, self::numberFormat($orders->sum('vts_lost_count')));
            array_push($payout_sums, self::numberFormat($orders->sum('payout_sum')));
            array_push($cross_sums, self::numberFormat($orders->sum('vts_cross_result')));
        }

        return response()->json([
            'labels' => $labels,
            'counts' => $counts,
            'sums' => $sums,
            'avgs' => $avgs,
            'payout_sums' => $payout_sums,
            'payout_counts' => $payout_counts,
            'cross_cums' => $cross_sums
        ]);

    }

    private function getFilteredOrdersQuery ($request) {
//        $orders = Order::whereHas('time', function (Builder $query) use ($request){
//            $query->where('date', '>=', $request->from)->where('date', '<=', $request->to);
//        });

//        if (isset($request->gender)
//            || isset($request->region_id)
//            || isset($request->)
//            || ( isset($request->age_category) && $request->age_category !== 'все') ) {
        //dd($request->from);
        $orders = self::filterOrdersByTime($request);

        //$orders = $orders->get();

//        if (isset($request->gender) && $request->gender != null
//            || isset($request->region_id) && $request->region_id != null
//            || ( isset($request->insurance_class) && $request->insurance_class != 'все' && $request->insurance_class != null)
//            || ( isset($request->age_category) && $request->age_category !== 'все' && $request->age_category != null))
//            $orders = self::filterByClient($orders,$request);

//        if (isset($request->vehicle_year_category) && $request->vehicle_year_category != null
//            || isset($request->vehicle_brand) && $request->vehicle_brand != null
//            || isset($request->vehicle_model) && $request->vehicle_model != null)
//            $orders = self::filterByVehicle($request, $orders);

//        if (isset($request->sale_center) && $request->sale_center != null)
//            $orders = $orders->where('sale_center_id', $request->sale_center);
//
//        if(isset($request->sale_channel) && $request->sale_channel != '')
//            $orders = $orders->where('sale_channel_id', $request->sale_channel);
//
//        if(isset($request->referrer) && $request->referrer != null)
//            $orders = $orders->where('referrer_id', $request->referrer);
//
//        if(isset($request->department) && $request->department != null)
//            $orders = $orders->where('department_id', $request->department);

        return $orders;
    }

    private function filterOrdersByTime($request) {
        return Order::whereHas('time', function ($query) use ($request){
            //$query->where('date', '>=', $request->from)->where('date', '<=', $request->to);
//            if(isset($request->from_date)) {
//                $query->whereBetween('date', [$request->from_date, $request->to_date]);
//            }
            //if(isset($request->from)) {
                $query->whereBetween('date', ['2018.01.01', '2018.01.30']);   //[$request->from, $request->to]);
            //}
        });
    }

    private function filterByClient($orders,$request){
        return $orders->whereHas('client', function ($query) use ($request) {
            if (isset($request->gender) && $request->gender != 2)
                $query->where('gender', $request->gender);

            if (isset($request->region_id) && $request->region_id != null)
                $query->where('region_id', $request->region_id);

            if (isset($request->age_category) && $request->age_category != 'все' && $request->age_category != null)
                $query->where('age_category', $request->age_category);

            if (isset($request->insurance_class) && $request->age_category != 'все')
                $query->where('insurance_class', $request->insurance_class);
        });
    }

    private function filterByVehicle($request, $orders){
        return $orders->whereHas('vehicle', function ($query) use ($request) {
            if (isset($request->vehicle_year_category))
                $query->where('year_category_id', $request->vehicle_year_category);

            if (isset($request->vehicle_brand))
                $query->where('vehicle_brand_id', $request->vehicle_brand);

            if (isset($request->vehicle_model))
                $query->where('vehicle_model_id', $request->vehicle_model);
        });
    }

//    private function filterBySeller($request, $orders){
//        return $orders->whereHas('vehicle', function (Builder $query) use ($request) {
//            if (isset($request->vehicle_year_category))
//                $query->where('year_category_id', $request->vehicle_year_category);
//
//            if (isset($request->vehicle_brand))
//                $query->where('vehicle_brand_id', $request->vehicle_brand);
//
//            if (isset($request->vehicle_model))
//                $query->where('vehicle_model_id', $request->vehicle_model);
//        });
//    }

    public function getReport(Request $request){
        ini_set('max_execution_time', 900);
//        $filter_set = FilterSet::where('id', $request->filter_set->id)->first() ?? null;

        // todo if isset filter set


//        $query = Time::where('date', '>=', $request->from)
//                ->where('date', '<=', $request->to);
//
//
//        $orders = $query->with('orders')->get()->pluck('orders')->flatten();
//
//        if (isset($request->gender))
//            $orders = $orders->whereHas('client', function (Builder $query) use ($request) {
//                $query->where('gender', $request->gender);
//            })->get();


        $orders = self::getFilteredOrdersQuery($request)->get();

        //dd($orders);

        return response()->json([
//            'general_report' => self::generateReport($orders->get())
            'general_report' => [
                'count' => self::numberFormat(count($orders)),
                'ogpo_vts_result' => self::numberFormat($orders->sum('ogpo_vts_result')),
                'vts_cross_result' => self::numberFormat($orders->sum('vts_cross_result')),
                'vts_overall_sum' => self::numberFormat($orders->sum('vts_overall_sum')),
                'payout_sum' => self::numberFormat($orders->sum('payout_sum')),
                'avg_sum' => self::numberFormat($orders->sum('avg_sum')),
                'avg_cross_result' => self::numberFormat($orders->sum('avg_cross_result')),
                'overall_lost_count' => self::numberFormat($orders->sum('overall_lost_count')),
                'vts_lost_count' => self::numberFormat($orders->sum('vts_lost_count')),
            ]
        ]);
//        $reports = Report::where('date', '>=', $request->from)
//                        ->where('date', '<=', $request->to)
//                        ->with('productsReports',
//                            'referrersReports',
//                            'giftsReports',
//                            'agesReports',
//                            'territoriesReports',
//                            'KBMReports',
//                            'saleCentersReports'
//                        )->get();
    }

    private function numberFormat($number){
        return number_format($number, 0, '.', ' ');
    }

    private static function generateReport($orders)
    {
        return [
//            'male_count' => $orders->sum('male_count'),
//            'female_count' => $reports->sum('female_count'),
            'count' => count($orders),
            'ogpo_vts_result' => $orders->sum('ogpo_vts_result'),
            'vts_cross_result' => $orders->sum('vts_cross_result'),
            'vts_overall_sum' => $orders->sum('vts_overall_sum'),
            'payout_sum' => $orders->sum('payout_sum'),
            'avg_sum' => $orders->sum('avg_sum'),
            'avg_cross_result' => $orders->sum('avg_cross_result'),
            'overall_lost_count' => $orders->sum('overall_lost_count'),
            'vts_lost_count' => $orders->sum('vts_lost_count'),
//            'products_report' => self::generateProductsReport(
//                                        $reports->pluck('productsReports')
//                                        ->flatten()
//                                        ->groupBy('name')
//                                 )
        ];
    }

    public function createSummaryTable(Request $request) {
        $region_id = $request->region_id;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $gender = $request->gender;
        $age_category = $request->age_category;
        $insurance_class = $request->insurance_class;

        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date = date('Y-m-d', strtotime($to_date));
//        dd($from_date);

//        $time = DB::table('times')->leftJoin('orders', 'times.id', '=', 'orders.time_id')
//
//        dd($times);


        $model = DB::table('regions')
            ->leftJoin('clients', 'regions.id', '=', 'clients.region_id')
            ->leftJoin('orders', 'clients.id', '=', 'orders.client_id')
            ->leftJoin('times', 'times.id', '=', 'orders.time_id')
            ->where('region_id', $region_id)
            ->where('insurance_class', $insurance_class)
            ->where('age_category', $age_category)
            ->where('gender', $gender)
            //->where('date', $from_date)
            ->whereBetween('date', [$from_date, $to_date])
//            ->groupBy('name')
            ->get();

        $result = [];

//        dd($model);

        foreach ($model as $item) {
            array_push($result, [
                'name' => $item->name,
                'gender' => $item->gender,
                'age' => $item->age,
                'insurance_class' => $item->insurance_class,
                'ogpo_vts_result' => $item->ogpo_vts_result,
                'vts_cross_result' => $item->vts_cross_result,
                'vts_overall_sum' => $item->vts_overall_sum,
                'avg_sum' => $item->avg_sum,
                'avg_cross_result' => $item->avg_cross_result,
                'overall_lost_count' => $item->overall_lost_count,
                'vts_lost_count' => $item->vts_lost_count,
                'declared_claims' => $item->declared_claims,
                'pending_claims' => $item->pending_claims,
                'accepted_claims' => $item->accepted_claims,
                'payout_reject_claims' => $item->payout_reject_claims,
                'client_reject_claims' => $item->client_reject_claims,
                'payout_sum' => $item->payout_sum,
                'date' => $item->date
            ]);
        }

        return $result;
    }




    private function getFilterData($filter,$request){
        $data = [];
        switch($filter){
            case 'region':
                if ($request->region_id != null && $request->region_id != 'все') {
                    $items[0] = Region::findOrFail($request->region_id);
                } else {
                    $items = Region::all();
                }
                $data[0] = $items;
                $data[1] = 'region_id';
            break;
            case 'age':
                if($request->age_category != null && $request->age_category != 'все'){
                    $items[0] = Age::findOrFail($request->age_category);
                } else {
                    $items = Age::all();
                }
                $data[0] = $items;
                $data[1] = 'age_category';
            break;
            case 'sale_center':
                if($request->sale_center != null && $request->sale_center != 'все'){
                    $items[0] = SaleCenter::findOrFail($request->sale_center);
                } else {
                    $items = SaleCenter::all();
                }
                $data[0] = $items;
                $data[1] = 'sale_center_id';
            break;
            case 'sale_channel':
                if($request->sale_channel != null && $request->sale_channel != 'все'){
                    $items[0] = SaleChannel::findOrFail($request->sale_channel);
                } else {
                    $items = SaleChannel::all();
                }
                $data[0] = $items;
                $data[1] = 'sale_channel_id';
            break;
            case 'department':
                if($request->department != null && $request->department != 'все'){
                    $items[0] = Department::findOrFail($request->department);
                } else {
                    $items = Department::all();
                }
                $data[0] = $items;
                $data[1] = 'department_id';
            break;
            case 'referrer':
                if($request->referrer != null && $request->referrer != 'все'){
                    $items[0] = Referrer::findOrFail($request->referrer);
                } else {
                    $items = Referrer::all();
                }
                $data[0] = $items;
                $data[1] = 'referrer_id';
            break;
            case 'agent':
                if($request->agent != null && $request->agent != 'все'){
                    $items[0] = Agent::findOrFail($request->agent);
                } else {
                    $items = Agent::all();
                }
                $data[0] = $items;
                $data[1] = 'agent_id';
            break;
        }
        return $data;
    }

    public function getWorkedReport(Request $request) {
        ini_set('max_execution_time', 15000);

//        $filterData = self::getFilterData('age',$request);
//        $vertical = $filterData[0];
//        $verticalQuery = $filterData[1];
//
//        $filterData = self::getFilterData('region',$request);
//        $horizontal = $filterData[0];
//        $horizontalQuery = $filterData[1];

//        $filterData = self::getFilterData('sale_channel',$request);
//        $horizontal = $filterData[0];
//        $horizontalQuery = $filterData[1];

//        $filterData = self::getFilterData('sale_center',$request);
//        $vertical = $filterData[0];
//        $verticalQuery = $filterData[1];

        $filterData = self::getFilterData('region',$request);
        $vertical = $filterData[0];
        $verticalQuery = $filterData[1];

        $filterData = self::getFilterData('agent',$request);
        $horizontal = $filterData[0];
        $horizontalQuery = $filterData[1];

        $data = [];
        $labels = [];
        $property = '';
        foreach ($vertical as $v) {
            $sums = [];
            $count = [];
            foreach($horizontal as $h){
                $order = self::getFilteredOrdersQuery($request)
                    ->where($verticalQuery,$v->id)
                    ->where($horizontalQuery,$h->id)
//                    ->whereHas('client', function ($query) use ($h,$v,$horizsontalQuery){
//                        $query->where($horizsontalQuery, $h->name);
//                    })
                    ->selectRaw('sum(orders.vts_overall_sum) as sum, count(orders.vts_overall_sum) as count')
                    ->get();

                if(isset($order[0]->sum)) {
                    $sums[$h->name] = $order[0]->sum;
                } else {
                    $sums[$h->name] = 0;
                }
                $count[$h->name] = $order[0]->count;

                if(!in_array($h->name,$labels)) {
                    array_push($labels, $h->name);
                }
            }

            //$data[$v->name] = array('sum'=>$sums,'count' => $count);
            $data[$v->name] = $sums;
        }

        print '<pre>';print_r($data);print '</pre>';exit();

        $property = 'Возраст';
        return response()->json([
            'property' => $property,
            'data' => $data,
            'labels' => $labels,
        ]);
    }

}
