<?php

namespace App\Http\Controllers;

use App\Order;
use App\Region;
use App\Age;
use App\Gift;
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
        $orders = self::getFilteredOrdersQuery($request)
            ->groupBy(['region_name','age_category'])->sum('vts_overall_sum');
        $property = 'Возраст';
        return response()->json([
            'property' => $property,
            'data' => $orders
        ]);
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

    private function getFilteredOrdersQuery ($request,$tableType = '') {
        $orders = self::filterOrdersByTime($request,$tableType);

        if (isset($request->gender) && $request->gender != null && $request->gender != 'все'
            || isset($request->region_id) && $request->region_id != null && $request->region_id != 'все'
            || ( isset($request->insurance_class) && $request->insurance_class != 'все' && $request->insurance_class != null)
            || ( isset($request->age_category) && $request->age_category !== 'все' && $request->age_category != null))
            $orders = self::filterByClient($orders,$request);

        if (isset($request->vehicle_year_category) && $request->vehicle_year_category != null
            || isset($request->vehicle_brand) && $request->vehicle_brand != null
            || isset($request->vehicle_model) && $request->vehicle_model != null)
            $orders = self::filterByVehicle($request, $orders);

        if (isset($request->sale_center) && $request->sale_center != null)
            $orders = $orders->where('sale_center_id', $request->sale_center);

        if(isset($request->sale_channel) && $request->sale_channel != '' && $request->sale_channel != null)
            $orders = $orders->where('sale_channel_id', $request->sale_channel);

        if(isset($request->referrer) && $request->referrer != null)
            $orders = $orders->where('referrer_id', $request->referrer);

        if(isset($request->department) && $request->department != null)
            $orders = $orders->where('department_id', $request->department);

        return $orders;
    }

    private function filterOrdersByTime($request,$tableType = '') {
        return Order::whereHas('time', function ($query) use ($request,$tableType){
            $from = '2019.01.01';   //$request->from_date;
            $to = '2019.01.30';     //$request->to_date;
            if($tableType != ''){
                $from = self::minusOneYear($from);
                $to = self::minusOneYear($to);
            }
            $query->whereBetween('date', [$from, $to]);
        });
    }

    private function filterByClient($orders,$request){
        return $orders->whereHas('client', function ($query) use ($request) {
            if (isset($request->gender) && $request->gender != 2 && $request->gender != null)
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
        $model = DB::table('regions')
            ->leftJoin('clients', 'regions.id', '=', 'clients.region_id')
            ->leftJoin('orders', 'clients.id', '=', 'orders.client_id')
            ->leftJoin('times', 'times.id', '=', 'orders.time_id')
            ->where('region_id', $region_id)
            ->where('insurance_class', $insurance_class)
            ->where('age_category', $age_category)
            ->where('gender', $gender)
            ->whereBetween('date', [$from_date, $to_date])
            ->get();

        $result = [];

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
                if (isset($request->region_id ) && $request->region_id != null && $request->region_id != 'все') {
                    $items[0] = Region::findOrFail($request->region_id);
                } else {
                    $items = Region::all();
                }
                $data[0] = $items;
                $data[1] = 'region_d';
                $data[2] = 'Город';
            break;
            case 'age':
                if(isset($request->age_category) && $request->age_category != null && $request->age_category != 'все'){
                    $items[0] = Age::where('name',$request->age_category)->first();
                } else {
                    $items = Age::all();
                }
                $data[0] = $items;
                $data[1] = 'age_id';
                $data[2] = 'Возраст';
            break;
            case 'sale_center':
                if($request->sale_center_id != null && $request->sale_center_id != 'все'){
                    $items[0] = SaleCenter::findOrFail($request->sale_center_id);
                } else {
                    $items = SaleCenter::all();
                }
                $data[0] = $items;
                $data[1] = 'sale_center_id';
                $data[2] = 'Центр продаж';
            break;
            case 'sale_channel':
                if($request->sale_channel_id != null && $request->sale_channel_id != 'все'){
                    $items[0] = SaleChannel::findOrFail($request->sale_channel_id);
                } else {
                    $items = SaleChannel::all();
                }
                $data[0] = $items;
                $data[1] = 'sale_channel_id';
                $data[2] = 'Канал продаж';
            break;
            case 'department':
                if($request->department_id != null && $request->department_id != 'все'){
                    $items[0] = Department::findOrFail($request->department_id);
                } else {
                    $items = Department::all();
                }
                $data[0] = $items;
                $data[1] = 'department_id';
                $data[2] = 'Департамент';
            break;
            case 'referrer':
                if($request->referrer_id != null && $request->referrer_id != 'все'){
                    $items[0] = Referrer::findOrFail($request->referrer_id);
                } else {
                    $items = Referrer::all();
                }
                $data[0] = $items;
                $data[1] = 'referrer_id';
                $data[2] = 'Referrer';
            break;
            case 'agent':
                if($request->agent_id != null && $request->agent_id != 'все'){
                    $items[0] = Agent::findOrFail($request->agent_id);
                } else {
                    $items = Agent::all();
                }
                $data[0] = $items;
                $data[1] = 'agent_id';
                $data[2] = 'Агенты';
            break;
            case 'gift':
                if(isset($request->gift_id ) && $request->gift_id != null && $request->gift_id != 'все'){
                    $items[0] = Gift::findOrFail($request->gift_id);
                } else {
                    $items = Gift::all();
                }
                $data[0] = $items;
                $data[1] = 'gift_id';
                $data[2] = 'Подарки';
            break;
        }
        return $data;
    }

    public function getPivotReport(Request $request) {
        //$request = $request->filters;

        ini_set('max_execution_time', 150000);
        $firstFilter = 'region';
        if(isset($request->filter_1) && $request->filter_1 != '' && $request->filter_1 != null){
            $firstFilter = $request->filter_1;
        }

        $filterData = self::getFilterData($firstFilter,$request);
        $vertical = $filterData[0];
        $verticalQuery = $filterData[1];
        $property = $filterData[2];

        if($firstFilter == $request->filter_2){
            $request->filter_2 = null;
        }

        if($request->filter_2 && $request->filter_2 != '' && $request->filter_2 != null) {
            $filterData = self::getFilterData($request->filter_2, $request);
            $horizontal = $filterData[0];
            $horizontalQuery = $filterData[1];
        } else {
            $horizontal = [];
        }

        $data = [];
        $labels = [];
        foreach ($vertical as $v) {
            $sums = [];
            $count = [];
            $avg = [];
            if(count($horizontal) > 0) {
                foreach ($horizontal as $h) {
                    $order = self::getFilteredOrdersQuery($request)
                        ->where($verticalQuery, $v->id)
                        ->where($horizontalQuery, $h->id)
                        ->selectRaw('
                            sum(orders.vts_overall_sum) as sum,
                            count(orders.id) as count,
                            sum(orders.avg_sum) as avg
                            ')
                        ->get();

                    if (isset($order[0]->sum)) {
                        $sums[$h->name] = self::numberFormat($order[0]->sum);
                    } else {
                        $sums[$h->name] = 0;
                    }
                    $count[$h->name] = self::numberFormat($order[0]->count);
                    $avg[$h->name] = self::numberFormat($order[0]->avg);

                    if (!in_array($h->name, $labels)) {
                        array_push($labels, $h->name);
                    }
                }
            } else {
                    $order = self::getFilteredOrdersQuery($request)
                        ->where($verticalQuery, $v->id)
                        ->selectRaw('
                            sum(orders.vts_overall_sum) as sum,
                            count(orders.id) as count,
                            sum(orders.avg_sum) as avg
                            ')
                        ->get();

                    if (isset($order[0]->sum)) {
                        $sums[] = self::numberFormat($order[0]->sum);
                    } else {
                        $sums[] = 0;
                    }
                    $count[] = self::numberFormat($order[0]->count);
                    $avg[] = self::numberFormat($order[0]->avg);

                    if (!in_array('', $labels)) {
                        array_push($labels, '');
                    }
            }

            $data[$v->name] = array('sum'=>$sums,'count' => $count,'avg' => $avg);
            //$data[$v->name] = $sums;
        }

        //print '<pre>';print_r($data);print '</pre>';exit();

        return response()->json([
            'property' => $property,
            'data' => $data,
            'labels' => $labels,
        ]);
    }

    public function getComparativeReport(Request $request){
        $request = $request->filters;
        ini_set('max_execution_time', 150000);
        $firstFilter = 'age';
        if(isset($request->filter_1) && $request->filter_1 != '' && $request->filter_1 != null){
            $firstFilter = $request->filter_1;
        }

        $filterData = self::getFilterData($firstFilter,$request);
        $vertical = $filterData[0];
        $verticalQuery = $filterData[1];
        $property = $filterData[2];

        $data = [];
        $labels = [];
        foreach ($vertical as $v) {
            $sums = [];
            $count = [];
            $avg = [];
            $sumsprev = [];
            $countprev = [];
            $avgprev = [];
            $order = self::getFilteredOrdersQuery($request)
                ->where($verticalQuery, $v->id)
                ->selectRaw('
                        sum(orders.vts_overall_sum) as sum,
                        count(orders.id) as count,
                        sum(orders.avg_sum) as avg
                        ')
                ->get();
            $orderPrevious = self::getFilteredOrdersQuery($request,'comparative')
                ->where($verticalQuery, $v->id)
                ->selectRaw('
                        sum(orders.vts_overall_sum) as sum,
                        count(orders.id) as count,
                        sum(orders.avg_sum) as avg
                        ')
                ->get();

            if (isset($order[0]->sum)) {
                $sums[] = self::numberFormat($order[0]->sum);
            } else {
                $sums[] = 0;
            }
            if (isset($orderPrevious[0]->sum)) {
                $sumsprev[] = self::numberFormat($orderPrevious[0]->sum);
            } else {
                $sumsprev[] = 0;
            }
            $count[] = self::numberFormat($order[0]->count);
            $avg[] = self::numberFormat($order[0]->avg);

            $countprev[] = self::numberFormat($orderPrevious[0]->count);
            $avgprev[] = self::numberFormat($orderPrevious[0]->avg);

            if (!in_array($request->from_date.'-'.$request->to_date, $labels)) {
                array_push($labels, $request->from_date.'-'.$request->to_date);
            }

            if (!in_array('доля', $labels)) {
                array_push($labels, 'доля');
            }

            $fromPrev = self::minusOneYear($request->from_date);
            $toPrev = self::minusOneYear($request->to);

            if (!in_array($fromPrev.'-'.$toPrev, $labels)) {
                array_push($labels, $fromPrev.'-'.$toPrev);
            }

            if (!in_array(' доля ', $labels)) {
                array_push($labels, ' доля ');
            }

            if (!in_array('изменение', $labels)) {
                array_push($labels, 'изменение');
            }

            $data[$v->name] = array(
                'sum'=>$sums,
                'count' => $count,
                'avg' => $avg,
                'sumsprev'=>$sumsprev,
                'countprev' => $countprev,
                'avgprev' => $avgprev
            );
            //$data[$v->name] = $sums;
        }

        print '<pre>';print_r($data);print '</pre>';exit();

        return response()->json([
            'property' => $property,
            'data' => $data,
            'labels' => $labels,
        ]);
    }

    private function numberFormat($number){
        $number = round($number);
        return number_format($number, 0, '.', ' ');
    }

    private function minusOneYear($date){
        return date('Y.m.d',strtotime(str_replace('.','-',$date).' -1 year'));
    }

}
