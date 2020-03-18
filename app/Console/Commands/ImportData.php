<?php

namespace App\Console\Commands;

use App\Agent;
use App\Client;
use App\Data;
use App\Department;
use App\Gift;
use App\Order;
use App\Status;
use App\Referrer;
use App\Region;
use App\Age;
use App\SaleCenter;
use App\SaleCenterDepartment;
use App\SaleChannel;
use App\Time;
use App\Vehicle;
use App\VehicleBrand;
use App\VehicleType;
use App\VehicleModel;
use App\VehicleYearCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data for report from csv files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = $this->getFiles();
        // rename files
        foreach ($files as $key => $file){
            $new_filename = 'pendingReports/inprocess_'.time(). '_' . $key.'.xlsx';
		Storage::move($file, $new_filename);
             $files[$key] = $new_filename;

        }

        foreach ($files as $file) {
            $orders = (new FastExcel)->import(storage_path('app/').$file, function ($line) {
//                 return $this->importLineToDataTable($line);
                return $this->processExcelLine($line);
            });

            //delete the file
            Storage::delete($file);
            echo "\t" . $file . " Done.\n";
//            }

        }
    }

    /*
     * импорт строк, полученных из файла, в таблицу data
     *
     * индесы строк соответсвуют заголовкам колонок в excel файле
     *
     * param array $line
     */
    private function importLineToDataTable($line) {
        $data = new Data();

        foreach (Data::MAP_FIELDS as $key => $field){
            if( $line[$field] == '' )
                continue;

            $data[$key] = $line[$field];
        }
        $data->save();
        return $data;
    }

    /*
     * парсинг данных из строки excel файла
     *
     * param array $line
     * @return Order
     */
    private function processExcelLine($line) {

	

        $query = Order::where('isn', $line[Data::MAP_FIELDS['isn']])->first();
        if(isset($query->id) && $query->id != '') {
            echo "\torder already exists\n";
            print $line[Data::MAP_FIELDS['isn']].'====id==='.$query->id;
            return null;
        }

//        $query = Order::where('isn', $line[Data::MAP_FIELDS['isn']])->first();
//        if($query == null){
//            print 'null';
//        }
        //print $line[Data::MAP_FIELDS['isn']].'='.$query;exit();]
        //print 'insert';exit();

//        if( Order::where('isn', $line[Data::MAP_FIELDS['isn']])->first() !== null) {
//            echo "\torder already exists\n";
//            return null;
//        }

        // insert basic order data
        $order = $this->fill_order($line);

        // insert gift
        if ( $line[Data::MAP_FIELDS['gift']] != '' ) {
            $gift = Gift::where('name', $line[Data::MAP_FIELDS['gift']])->first()
                ?? Gift::create(['name' => $line[Data::MAP_FIELDS['gift']]]);
            $order->gift_id = $gift->id;
        }

        // insert agent
        if ( $line[Data::MAP_FIELDS['agent']] != '' ) {
            $agent = Agent::where('fullname', $line[Data::MAP_FIELDS['agent']])->first()
                ?? Agent::create(['fullname' => $line[Data::MAP_FIELDS['agent']]]);

            $order->agent_id = $agent->id;
        }

        // insert sale channel
        if ( $line[Data::MAP_FIELDS['sale_channel']] != '' ) {
            $sale_channel = SaleChannel::where('name', $line[Data::MAP_FIELDS['sale_channel']])->first()
                ?? SaleChannel::create(['name' => $line[Data::MAP_FIELDS['sale_channel']]]);

            $order->sale_channel_id = $sale_channel->id;
        }

        if ( $line[Data::MAP_FIELDS['city']] != '' ) {
            // insert region
            $region = Region::where('name', $line[Data::MAP_FIELDS['city']])->first()
                ?? Region::create(['name' => $line[Data::MAP_FIELDS['city']]]);
            $order->region_d = $region->id;
        }

        // insert sale center
        if ( $line[Data::MAP_FIELDS['sale_center']] != '' ) {

            $sale_center = SaleCenter::where('name', $line[Data::MAP_FIELDS['sale_center']])->first()
                ?? SaleCenter::insert(['region_id' => $region->id,'name' => $line[Data::MAP_FIELDS['sale_center']]]);

            $order->sale_center_id = $sale_center->id;
            if($sale_center->region_id == null && isset($region->id)){
                $sale_center->region_id = $region->id;
                $sale_center->save();
            }
        }

        // insert referrer
        if ( $line[Data::MAP_FIELDS['referrer']] != '' ) {
            $referrer = Referrer::where('name', $line[Data::MAP_FIELDS['referrer']])->first()
                ?? Referrer::create(['name' => $line[Data::MAP_FIELDS['referrer']]]);
            $order->referrer_id = $referrer->id;
        }

        if ( $line[Data::MAP_FIELDS['new']] != '' ) {
            $status = Status::where('name', 'Новый')->first()
                ?? Status::create(['name' => 'Новый']);
            $order->status_id = $status->id;
        }
        if ( $line[Data::MAP_FIELDS['active']] != '' ) {
            $status = Status::where('name', 'Действующий')->first()
                ?? Status::create(['name' => 'Действующий']);
            $order->status_id = $status->id;
        }
        if ( $line[Data::MAP_FIELDS['returned']] != '' ) {
            $status = Status::where('name', 'Вернувшийся')->first()
                ?? Status::create(['name' => 'Вернувшийся']);
            $order->status_id = $status->id;
        }

        if ( $line[Data::MAP_FIELDS['age_category']] != '' ) {
            // insert age
            $age = Age::where('name', $line[Data::MAP_FIELDS['age_category']])->first()
                ?? Age::create(['name' => $line[Data::MAP_FIELDS['age_category']]]);
            $order->age_id = $age->id;
        }

        // insert client
         $client = Client::where('isn', $line[Data::MAP_FIELDS['isn']])->first()
            ?? Client::create([
                'isn' => $line[Data::MAP_FIELDS['isn']],
                'gender' => $line[Data::MAP_FIELDS['gender']],
                'age' => $line[Data::MAP_FIELDS['age']] !== '' ? $line[Data::MAP_FIELDS['age']] : 0,
                'age_category' => $line[Data::MAP_FIELDS['age_category']],
                'insurance_class' => $line[Data::MAP_FIELDS['insurance_class']],
                'region_id' => $region->id
            ]);
        $order->client_id = $client->id;
        $order->age_category_name = $client->age_category;

        // insert department
        if ( $line[Data::MAP_FIELDS['department']] != '' ) {
            $department = Department::where('name', $line[Data::MAP_FIELDS['department']])->first()
                ?? Department::create(['name' => $line[Data::MAP_FIELDS['department']], 'region_id' => $region->id]);
            $order->department_id = $department->id;
            if(isset($sale_center->id)){
                $sale_department = SaleCenterDepartment::where('department_id', $department->id)->first();
                if($sale_department != null){
                    if($sale_department->sale_center_id == null) {
                        $sale_department->sale_center_id = $sale_center->id;
                        $sale_department->department_name = $line[Data::MAP_FIELDS['department']];
                        $sale_department->sale_center_name = $line[Data::MAP_FIELDS['sale_center']];
                        $sale_department->save();
                    }
                } else {
                    $sale_department = new SaleCenterDepartment;
                    $sale_department->department_id = $department->id;
                    $sale_department->sale_center_id = $sale_center->id;
                    $sale_department->department_name = $line[Data::MAP_FIELDS['department']];
                    $sale_department->sale_center_name = $line[Data::MAP_FIELDS['sale_center']];
                    $sale_department->save();
                }
            }
        }

        // insert time
        $time = $this->fill_time($line[Data::MAP_FIELDS['date']]);

        $order->time_id = $time->id;

        // insert vehicle
        $vehicle = $this->fill_vehicle(
            $line[Data::MAP_FIELDS['vehicle_brand']],
            $line[Data::MAP_FIELDS['vehicle_model']],
            $line[Data::MAP_FIELDS['vehicle_year_category']],
            $line[Data::MAP_FIELDS['vehicle_year']],
            $line[Data::MAP_FIELDS['vehicle_type']]
        );

        $order->vehicle_id = $vehicle->id;


        $order->save();

        return $order;
    }

    private function fill_order ($line) {

        $order = new Order();

        foreach ( Data::MAP_FIELDS as $key => $field ) {
//            dd($line);
            if( $line[$field] == '' )
                continue;

            if( Schema::hasColumn($order->getTable(), $key)) {
                $order[$key] = $line[$field];
            }
        }

        return $order;
    }

    private function fill_vehicle ($_brand, $_model, $_year_category, $_year,$_type) {

        $vehicle = Vehicle::whereHas('vehicle_brand', function (Builder $query) use ($_brand, $_model) {

            $query->where('name', $_brand)
                ->whereHas('vehicle_models', function (Builder $query) use ($_model) {

                    $query->where('name', $_model);

                });

        })->whereHas('year_category', function (Builder $query) use ($_year_category) {

            $query->where('category', $_year_category);

        })->where('year', $_year)->first() ?? null;

        if(isset($vehicle))
            return $vehicle;
        else
            $vehicle = new Vehicle();

        $type = VehicleType::where('name', $_type)->first()
            ?? new VehicleType([
                'name' => $_type
            ]);
        $type->save();


        $brand = VehicleBrand::where('name', $_brand)->first()
            ?? new VehicleBrand([
                'name' => $_brand,
                'vehicle_type_id' => $type->id
            ]);
        $brand->save();

//        $vehicle->vehicle_brand()->addBinding($brand);

        $model = VehicleModel::where('name', $_model)->first()
            ?? new VehicleModel([
                'name' => $_model,
                'vehicle_brand_id' => $brand->id,
                'vehicle_type_id' => $type->id
            ]);

//        $model->vehicle_brand()->addBinding($brand);
        $model->save();

//        $vehicle->vehicle_model()->addBinding($model);

        $year_category = VehicleYearCategory::where('category', $_year_category)->first()
            ?? new VehicleYearCategory([
                'category' => $_year_category
            ]);
        $year_category->save();

//        $vehicle->year_category()->addBinding($year_category);

        $vehicle->year_category_id = $year_category->id;
        $vehicle->vehicle_brand_id = $brand->id;
        $vehicle->vehicle_model_id = $model->id;
        $vehicle->year = $_year;
        $vehicle->vehicle_type_id = $type->id;

        $vehicle->save();

        return $vehicle;
    }

    private function fill_time($date)
    {
        return Time::where('date', $date)->first()
            ?? Time::create([
                'date' => $date,
                'day' => date('d', strtotime($date)),
                'month' => date('m', strtotime($date)),
                'year' => date('y', strtotime($date)),
                //'day_of_week' => date('w', strtotime($date)) 
            ]);
    }

    private function getFiles()
    {

        $files_in_folder = Storage::files('pendingReports');
        $files = [];
        foreach ($files_in_folder as $file){



            /*if(count($files) > 3)
                break;*/

//            if(strpos($file, 'inprocess') !== false || strpos($file, '.DS_Store') !== false )
//                continue;

            array_push($files, $file);

        }

 

        return $files;
    }
}
