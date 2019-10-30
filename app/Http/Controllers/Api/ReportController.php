<?php

namespace App\Http\Controllers;

use App\Order;
use App\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class ReportController extends Controller
{
    public function getreport(Request $request){

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


        $orders = Order::whereHas('time', function (Builder $query) use ($request){
            $query->where('date', '>=', $request->from)->where('date', '<=', $request->to);
        });

        if (isset($request->gender) || isset($request->region_id)) // or city or age category
            $orders = $orders->whereHas('client', function (Builder $query) use ($request) {
                if(isset($request->gender))
                    $query->where('gender', $request->gender);

                if(isset($request->region_id))
                    $query->where('region_id', $request->region_id);
            });

        return response()->json([
            'general_report' => self::generateReport($orders->get())
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

    private static function generateProductsReport($products_reports)
    {
        $result = [];

        foreach ($products_reports as $key => $product_report ) {
            $result[$key] = [
                'count' => $product_report->sum('count'),
                'sum' => $product_report->sum('sum'),
                'lost_sum' => $product_report->sum('lost_sum'),
            ];
        }

        return $result;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$request->user()->authorizeRoles(['admin', 'user']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report $report)
    {
        //
    }
}
