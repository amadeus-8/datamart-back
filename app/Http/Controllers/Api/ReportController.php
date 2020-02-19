<?php

namespace App\Http\Controllers;

use App\Order;
use App\Data;
use App\Region;
use App\Age;
use App\Status;
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
use Rap2hpoutre\FastExcel\FastExcel;


class ReportController extends Controller
{
    const GETFIELDS = '
                sum(orders.vts_overall_sum) as vts_overall_sum,
                sum(orders.ogpo_vts_result) as ogpo_vts_result,
                sum(orders.vts_cross_result) as vts_cross_result,
                sum(orders.avg_sum) as avg_sum,
                sum(orders.avg_cross_result) as avg_cross_result,
                sum(orders.overall_lost_count) as overall_lost_count,
                sum(orders.vts_lost_count) as vts_lost_count,
                sum(orders.declared_claims) as declared_claims,
                sum(orders.pending_claims) as pending_claims,
                sum(orders.accepted_claims) as accepted_claims,
                sum(orders.payout_reject_claims) as payout_reject_claims,
                sum(orders.client_reject_claims) as client_reject_claims,
                sum(orders.payout_sum) as payout_sum,
                sum(ogpo_vts_count) as ogpo_vts_count,
                sum(medical_count) as medical_count,
                sum(megapolis_count) as megapolis_count,
                sum(amortization_count) as amortization_count,
                sum(kasko_count) as kasko_count,
                sum(kommesk_comfort_count) as kommesk_comfort_count,
                sum(tour_count) as tour_count
    ';

    const GETFIELDSSUM = array('vts_overall_sum', 'ogpo_vts_result', 'vts_cross_result', 'avg_sum', 'avg_cross_result',
                'overall_lost_count','vts_lost_count','declared_claims','pending_claims','accepted_claims',
                'payout_reject_claims','client_reject_claims','payout_sum','ogpo_vts_count','medical_count',
                'megapolis_count','amortization_count','kasko_count','kommesk_comfort_count','tour_count'
    );

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

        if (isset($request->status_id) && $request->status_id != null)
            $orders = $orders->where('status_id', $request->status_id);

        if (isset($request->sale_center_id) && $request->sale_center_id != null)
            $orders = $orders->where('sale_center_id', $request->sale_center_id);

        if(isset($request->sale_channel_id) && $request->sale_channel_id != '' && $request->sale_channel_id != null)
            $orders = $orders->where('sale_channel_id', $request->sale_channel_id);

        if(isset($request->referrer_id) && $request->referrer_id != null)
            $orders = $orders->where('referrer_id', $request->referrer_id);

        if(isset($request->department_id) && $request->department_id != null)
            $orders = $orders->where('department_id', $request->department_id);

        return $orders;
    }

    private function filterOrdersByTime($request,$tableType = '') {
        return Order::whereHas('time', function ($query) use ($request,$tableType){
            $from = $request->from_date;    //$from = '2019.01.01';
            $to = $request->to_date;      //$to = '2019.01.31';
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
                $data[2] = 'Канал привлечения';
            break;
            case 'status':
                if(isset($request->status_id ) && $request->status_id != null && $request->status_id != 'все'){
                    $items[0] = Status::findOrFail($request->status_id);
                } else {
                    $items = Status::all();
                }
                $data[0] = $items;
                $data[1] = 'status_id';
                $data[2] = 'Статус';
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
        ini_set('max_execution_time', 150000);
        $firstFilter = 'region';
        if(isset($request->columns) && $request->columns != '' && $request->columns != null){
            $firstFilter = $request->columns;
        }
        $filterData = self::getFilterData($firstFilter,$request);
        $vertical = $filterData[0];
        $verticalQuery = $filterData[1];
        $property = $filterData[2];

        if($firstFilter == $request->rows){
            $request->rows = null;
        }

        if($request->rows && $request->rows != '' && $request->rows != null) {
            $filterData = self::getFilterData($request->rows, $request);
            $horizontal = $filterData[0];
            $horizontalQuery = $filterData[1];
        } else {
            $horizontal = [];
        }

        $data = [];
        $labels = [];
        $list = [];
        $list_h = [];
        foreach ($vertical as $v) {
            $sums = [];
            $count = [];
            $avg = [];
            $vts_overall_sum = [];
            if(count($horizontal) > 0) {
                $order = [];
                $i=0;
                foreach ($horizontal as $h) {
                    $order[$h->name] = self::getFilteredOrdersQuery($request)
                        ->where($verticalQuery, $v->id)
                        ->where($horizontalQuery, $h->id)
                        ->selectRaw(self::GETFIELDS)
                        ->get();
                    if (!in_array($h->name, $labels)) {
                        array_push($labels, $h->name);
                    }
                    $order[$h->name] = $order[$h->name][0];

                    if(isset($request->export) && $request->export != '') {
                        if ($i == 0) {
                            $list_h[$property] = $v->name;
                        }
                        $list_h[$h->name] = self::numberFormat($order[$h->name]->vts_overall_sum);  // параметр принять
                        $i++;
                    }
                }
                $data[$v->name] = $order;
            } else {

                    $order = self::getFilteredOrdersQuery($request)
                        ->where($verticalQuery, $v->id)
                        ->selectRaw(self::GETFIELDS)
                        ->get();
                    if (!in_array(Data::MAP_FIELDS[$request->values], $labels)) {
                        array_push($labels, Data::MAP_FIELDS[$request->values]);
                    }
                    $data[$v->name] = $order;

//  Export to EXCELL
//                    if(isset($request->export) && $request->export != '') {
//                        $list_h[$property] = $v->name;
//                        $lineName = Data::MAP_FIELDS['vts_overall_sum'];
//                        $list_h[$lineName] = self::numberFormat($order[0]->vts_overall_sum);
//                    }

            }
//            if(isset($request->export) && $request->export != '') {
//                $list[] = $list_h;
//            }
        }
//        if(isset($request->export) && $request->export != '') {
//            return self::getExport(collect($list));
//        }

        //print '<pre>';print_r($data);print '</pre>';exit();

        return response()->json([
            'property' => $property,
            'data' => $data,
            'labels' => $labels,
        ]);
    }

    public function getComparativeReport(Request $request){
        ini_set('max_execution_time', 150000);
        $firstFilter = 'age';
        if(isset($request->columns) && $request->columns != '' && $request->columns != null){
            $firstFilter = $request->columns;
        }

        $filterData = self::getFilterData($firstFilter,$request);
        $vertical = $filterData[0];
        $verticalQuery = $filterData[1];
        $property = $filterData[2];

        $data = [];
        $labels = [];
        $sumData = [];
        $bottomLabels = [];
        $orderAll = self::getFilteredOrdersQuery($request)
            ->selectRaw(self::GETFIELDS)
            ->get();
        $orderAllPrev = self::getFilteredOrdersQuery($request,'comparative')
            ->selectRaw(self::GETFIELDS)
            ->get();
        $bottomData = $this->createComparativeData($orderAll,$orderAllPrev,1);
        $bottomD = array($orderAll[0],$orderAllPrev[0],$bottomData);
        $order = [];
        $orderPrevious = [];
        foreach ($vertical as $v) {
            $sums = [];
            $count = [];
            $avg = [];
            $sumsprev = [];
            $countprev = [];
            $avgprev = [];
            $order = self::getFilteredOrdersQuery($request)
                ->where($verticalQuery, $v->id)
                ->selectRaw(self::GETFIELDS)
                ->get();
            $orderPrevious = self::getFilteredOrdersQuery($request,'comparative')
                ->where($verticalQuery, $v->id)
                ->selectRaw(self::GETFIELDS)    //GETFIELDSPREV
                ->get();

            $labelData = date('d.m.Y',strtotime($request->from_date)).' - '.date('d.m.Y',strtotime($request->to_date));

            if (!in_array($labelData, $labels)) {
                array_push($labels, $labelData);
            }

            if (!in_array('доля', $labels)) {
                array_push($labels, 'доля');
            }

            $fromPrev = str_replace('-','.',$this->minusOneYear($request->from_date));
            $toPrev = str_replace('-','.',$this->minusOneYear($request->to_date));

            if (!in_array($fromPrev.' - '.$toPrev, $labels)) {
                array_push($labels, $fromPrev.' - '.$toPrev);
            }

            if (!in_array(' доля ', $labels)) {
                array_push($labels, ' доля ');
            }

            if (!in_array('изменение', $labels)) {
                array_push($labels, 'изменение');
            }

            $firstDolya = $this->createComparativeData($order,$orderAll);
            $secondDolya = $this->createComparativeData($orderPrevious,$orderAllPrev);
            $comparative = $this->createComparativeData($order,$orderPrevious,1);

            $data[$v->name] = array($order[0],$firstDolya,$orderPrevious[0],$secondDolya,$comparative);
        }
//        print '<pre>';print_r($data);print '</pre>';
//        print '<pre>';print_r($bottomD);print '</pre>';exit();
        //$data[] = $data;

        return response()->json([
            'property' => $property,
            'data' => $data,
            'labels' => $labels,
            'bottomData' => $bottomD
        ]);
    }

    public function getChartReport(Request $request) {
        ini_set('max_execution_time', 150000);
        $firstFilter = isset($request->columns) && $request->columns != '' && $request->columns != null ? $request->columns : 'region';

        $filterData = self::getFilterData($firstFilter,$request);
        $vertical = $filterData[0];
        $verticalQuery = $filterData[1];

        $xaxis = [];
        $series = [];
        $labels = [];
        foreach ($vertical as $v) {
            $order = self::getFilteredOrdersQuery($request)
                ->where($verticalQuery, $v->id)
                ->selectRaw(self::GETFIELDS)
                ->get();
            if (!in_array('', $labels)) {
                array_push($labels, '');
            }
            $xaxis[] = $v->name;
            for($i=0;$i<count(self::GETFIELDSSUM);$i++){
                $series[self::GETFIELDSSUM[$i]][] = $order[0]->{self::GETFIELDSSUM[$i]} != null ? $order[0]->{self::GETFIELDSSUM[$i]} : 0;
            }
        }

        for($i=0;$i<count(self::GETFIELDSSUM);$i++){
            $seriess[$i]['data'] = $series[self::GETFIELDSSUM[$i]];
            $seriess[$i]['value'] = self::GETFIELDSSUM[$i];
            $seriess[$i]['name'] = Data::MAP_FIELDS[self::GETFIELDSSUM[$i]];
        }

        return response()->json([
            'xaxis' => $xaxis,
            'series' => $seriess,
        ]);
    }

    private function numberFormat($number){
        $number = round($number);
        return number_format($number, 0, '.', ' ');
    }

    private function createComparativeData($orderFirst,$orderSecond,$comparative = ''){
        $lineSums = self::GETFIELDSSUM;
        foreach($lineSums as $name){
            if(intval($orderFirst[0][$name]) != 0 && $orderSecond[0][$name]) {
                if($comparative == 1) {
                    $result[$name] = $this->numberFormat(((intval($orderFirst[0][$name]) / intval($orderSecond[0][$name]))-1) * 100);
                } else {
                    $result[$name] = $this->numberFormat((intval($orderFirst[0][$name]) / intval($orderSecond[0][$name])) * 100);
                }

            } else {
                $result[$name] = intval($orderFirst[0][$name]);
            }
        }
        return $result;
    }

    private function minusOneYear($date){
        return date('Y.m.d',strtotime(str_replace('.','-',$date).' -1 year'));
    }

    public function getExport($data){
        return (new FastExcel($data))->download('file.xlsx');
    }

}
