<?php

namespace App\Console\Commands;
use App\Models\Participants;
use App\Models\Products;
use App\Models\Performance;
use App\Models\Cronjobs;

use Illuminate\Console\Command;

class FileData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filedata:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periodically fetch data from the file system to mysql database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Participants
        define('file','./commandline/participants.txt');
        $loadFile = @file(file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $i = 0;
        foreach($loadFile as $data) {

            $expData = explode(",", $data);

            $uid = $expData[0];
            $name = $expData[1];
            $password = $expData[2];
            $product = $expData[3];
            $date_of_birth = $expData[4];
            
            if (Participants::where('name', $name)->first()) {
                info('User Already Exist');
            } else {
                $party = new Participants();
                $party->partid = $uid;
                $party->name = $name;
                $party->password = $password;
                $party->product = $product;
                $party->date_of_birth = $date_of_birth;
                $party->save();
            }
                
        }

        //Products and Description of Participants
        define('fileprod','./commandline/products.txt');
        $prodFile = @file(fileprod, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $i = 0;
        foreach($prodFile as $item) {

            $expItem = explode(",", $item);

            $proid = $expItem[0];
            $uname = $expItem[1];
            $productname = $expItem[2];
            $quantity = $expItem[3];
            $price = $expItem[4];
            $description = $expItem[5];
            
            if (Products::where('uname', $uname)->first()) {
                info('Products for This Particular User Already Exist');
            } else {
                $party = new Products();
                $party->proid = $proid;
                $party->uname = $uname;
                $party->product = $productname;
                $party->quantity = $quantity;
                $party->price = $price;
                $party->description = $description;
                $party->save();
            }
                
        }
 
        //Performance of the Participants
        define('filereq','./commandline/request.txt');
        $reqFile = @file(filereq, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $i = 0;
        foreach($reqFile as $req) {

            if($i==0){

            }else{
                $expReq = explode(",", $req);
                $reqid = $expReq[0];
                if (Performance::where('participant', $reqid)->first()) {
                    $reqtime = $expReq[1];

                    $table = Performance::where('participant', $reqid)->get();
                    //echo $table."<br>";
                    foreach($table as $tab){
                        $uid = $tab->uid;
                        $part = $tab->participant;
                        $rank = $tab->rank;
                        $points = $tab->points;
                        $quantity_left = $tab->quantity_left;
                        $returns = $tab->returns;
                        $date = $tab->date_created;

                        $myFile = "./commandline/performance.txt";
                        $fh = fopen($myFile, 'a') or die("can't open file");
                        fwrite($fh, $uid.",");
                        fwrite($fh, $part.",");
                        fwrite($fh, $rank.",");
                        fwrite($fh, $points.",");
                        fwrite($fh, $quantity_left.",");
                        fwrite($fh, $returns.",");
                        fwrite($fh, $date."\n");
                        fclose($fh);

                        
                        $cron = new Cronjobs();
                        $cron->user = $part;
                        $cron->request_made = $reqtime;
                        $cron->save();


                    }
                }else {
                     info('User Information Does not Exist');
                }
                 
                 
            }$i++;
        }

        $header = "Header line";
        $fileresquest = fopen('./commandline/request.txt', 'w');
        fwrite($fileresquest, $header."\n");
        fclose($fileresquest);


        //Cronjob Activities
        define('fileres','./commandline/response.txt');
        $resFile = @file(fileres, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $i = 0;

        foreach($resFile as $res){
            if($i==0){
                // skips the header
            }else{
                $expRes = explode(",", $res);
                $resname = $expRes[0];
                $restime = $expRes[1];

                $cron = new Cronjobs();
                $cron->user = $resname;
                $cron->request_seen = $restime;
                $cron->save();


            }
            $i++;

        }

        $header = "Header line";
        $fileresquest = fopen('./commandline/response.txt', 'w');
        fwrite($fileresquest, $header."\n");
        fclose($fileresquest);


    }
}
