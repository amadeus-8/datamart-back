<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class SeparateSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'separate:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Separate large excel files from inputsource folder';

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
        $files = Storage::files('importsource');

        foreach ($files as $item){
            if(strpos($item, 'inprocess') == false){
                $file = $item;
                break;
            }

        }

        // по 2 файла
//        foreach (array_slice($files,1,1) as $file) {

        echo "\t" . $file . " is in process...\n";

        // rename
        $new_filename = 'importsource/inprocess_' . time() . '.xlsx';
        echo "\trename " . $file . " to " . $new_filename . "\n";
        Storage::move($file, $new_filename);

        $rows = (new FastExcel)->import(storage_path('app/').$new_filename)->toArray();


        //remove first line
        //$rows = array_slice($rows, 1);

        //loop through file and split every 1000 lines
        $parts = (array_chunk($rows, 30000));   // 500
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

        Storage::delete($new_filename);
        echo "\t" . $file . " Done.\n";
//        }
    }
}
