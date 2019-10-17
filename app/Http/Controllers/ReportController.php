<?php

namespace App\Http\Controllers;

use App\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getreport(Request $request){
        $reports = Report::where('date', '>=', $request->from)
                        ->where('date', '<=', $request->to)
                        ->with('productsReports',
                            'referrersReports',
                            'giftsReports',
                            'agesReports',
                            'territoriesReports',
                            'KBMReports',
                            'saleCentersReports'
                        )->get();

        return response()->json([
            'report' => self::generateReport($reports)
        ]);
    }

    private static function generateReport($reports)
    {
        return [
            'male_count' => $reports->sum('male_count'),
            'female_count' => $reports->sum('female_count'),
            'sale_count' => $reports->sum('sale_count'),
            'payout_count' => $reports->sum('payout_count'),
            'sum' => $reports->sum('sum'),
            'lost_sum' => $reports->sum('lost_sum'),
            'products_report' => self::generateProductsReport(
                                        $reports->pluck('productsReports')
                                        ->flatten()
                                        ->groupBy('name')
                                 )
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
