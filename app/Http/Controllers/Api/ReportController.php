<?php

namespace App\Http\Controllers;

use App\Order;
use App\Region;
use App\Report;
use App\SaleCenter;
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

    public function getRegionsReport(Request $request) {
        //phpinfo();
        //exit();
        ini_set('max_execution_time', 900);
        $regions = Region::all();

        $labels = [];
        $counts = [];
        $sums = [];
        $avgs = [];
        $payout_sums = [];
        $payout_counts = [];
        $cross_sums = [];

        foreach ($regions as $region) {
            $request->region_id = $region->id;
            $orders = self::getFilteredOrdersQuery($request)
                ->with('client')
                ->get()
                ->groupBy('client.age_category');

//            $orders->with(['client' => function($q){
//                $q->groupBy('age_category');
//            }]);

//            $orders->leftjoin('clients as cn','cn.region_id', '=', 'regions.id')
//                ->groupBy('age_category');

            //$orders->get();

            //dd($orders);

            array_push($labels, $region->name);
            array_push($counts, count($orders));
            array_push($sums, self::numberFormat($orders->sum('vts_overall_sum')));
            array_push($avgs, self::numberFormat($orders->sum('avg_sum')));
            array_push($payout_counts, self::numberFormat($orders->sum('vts_lost_count')));
            array_push($payout_sums, self::numberFormat($orders->sum('payout_sum')));
            array_push($cross_sums, self::numberFormat($orders->sum('vts_cross_result')));
        }
//        $orders = self::getFilteredOrdersQuery($request);

//        $orders->with(['client' => function($q){
//            $q->groupBy('age_category');
//        }]); //->groupBy('age_category');

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

        if (isset($request->gender) && $request->gender != null
            || isset($request->region_id) && $request->region_id != null
            || ( isset($request->insurance_class) && $request->insurance_class != 'все' && $request->insurance_class != null)
            || ( isset($request->age_category) && $request->age_category !== 'все' && $request->age_category != null))
            $orders = self::filterByClient($orders,$request);

        if (isset($request->vehicle_year_category) && $request->vehicle_year_category != null
            || isset($request->vehicle_brand) && $request->vehicle_brand != null
            || isset($request->vehicle_model) && $request->vehicle_model != null)
            $orders = self::filterByVehicle($request, $orders);

        if (isset($request->sale_center) && $request->sale_center != null)
            $orders = $orders->where('sale_center_id', $request->sale_center);

        if(isset($request->sale_channel) && $request->sale_channel != '')
            $orders = $orders->where('sale_channel_id', $request->sale_channel);

        if(isset($request->referrer) && $request->referrer != null)
            $orders = $orders->where('referrer_id', $request->referrer);

        if(isset($request->department) && $request->department != null)
            $orders = $orders->where('department_id', $request->department);

        return $orders;
    }

    private function filterOrdersByTime($request) {
        return Order::whereHas('time', function ($query) use ($request){
            //$query->where('date', '>=', $request->from)->where('date', '<=', $request->to);
            if(isset($request->from_date)) {
                $query->whereBetween('date', [$request->from_date, $request->to_date]);
            }
            if(isset($request->from)) {
                $query->whereBetween('date', [$request->from, $request->to]);
            }
        });
    }

    private function filterByClient($orders,$request){
        return $orders->whereHas('client', function ($query) use ($request) {
            if (isset($request->gender) && $request->gender != 2)
                $query->where('gender', $request->gender);

            if (isset($request->region_id) && $request->region_id != null)
                $query->where('region_id', $request->region_id);

            if (isset($request->age_category) && $request->age_category != 'все')
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
}
