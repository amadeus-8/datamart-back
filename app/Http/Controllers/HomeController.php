<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        return view('home');
    }

    /*
     * Загрузка данных с файла csv
     */
    public function feedFromFile(Request $request){

        request()->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        $path = request()->file('file')->getRealPath();

        $rows = (new FastExcel)->import($path)->toArray();
        //remove first line
        $rows = array_slice($rows, 1);

//        dd($rows[3]);

        //loop through file and split every 1000 lines
        $parts = (array_chunk($rows, 500));
        $i = 1;
        foreach($parts as $part) {
            foreach ($part as $key => $item){
                $part[$key]['Дата подписания ГПО ВТС'] = date('Y-m-d', $item['Дата подписания ГПО ВТС']->getTimestamp());
            }
            $collection = collect($part);

            $filename = '/pendingReports/'.date('y-m-d-H-i-s').$i.'.xlsx';

            (new FastExcel($collection))->export(storage_path('app') . $filename);

            $i++;
        }

        session()->flash('status', 'queued for importing');

        return redirect()->route('home');
    }
}
