<?php

namespace App\Modules\Attendance\Dao;
use App\Modules\Attendance\Models\AttendanceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class AttendanceDao {
    function __construct(){ 
        
    }

    function getMinTimeInBetweenDatetime($from, $to, $emp_id){
        //DB::connection()->enableQueryLog();
        return DB::table('attendance as a')
                        ->select(DB::raw('MIN(datetime) as date'))
                        ->where('emp_id', $emp_id)
                        ->where('type_id', 1)
                        ->whereBetween('datetime', [$from, $to])
                        ->orderBy('datetime', 'ASC')
                        ->get()
                        ->toArray();
        //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");
    }

    function getDistinctDateAttendanceOfEmployee($from, $to, $emp_id){
        //DB::connection()->enableQueryLog();
        return DB::table('attendance as a')
                        ->select(DB::raw('DISTINCT(DATE(datetime)) as date'))
                        ->where('emp_id', $emp_id)
                        ->whereBetween('datetime', [$from, $to])
                        ->orderBy('datetime', 'ASC')
                        ->get()
                        ->toArray();
        //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");
    }

    function traverseRecords($data, $start_code, $start_datetime, $datetime, $ti, $to, $seq, $seqtime){

        $rec = array();
        //Normal Seq
        echo "<br>NORMAL Sequential<br>";
        for($c=0;$c<count($data);$c++){
            echo $data[$c]->code." - ".date('M d, Y g:i A', strtotime($data[$c]->datetime))."<br>";
        }
        //Trace Seq
        echo "<br>Sequential TI-TO<br>";
        for($c=0;$c<count($seqtime);$c++){
            echo $seq[$c]." - ".date('M d, Y g:i A', strtotime($seqtime[$c]))."<br>";
        }

        echo "<br>Calculating......<br>";

        for($c=0;$c<count($seqtime);$c++){
            $f = strtotime($seqtime[$c]);
            echo date('M d, Y g:i A', strtotime($seqtime[$c]))." - ".$seq[$c]." - ";
          
            if(array_key_exists($c+1, $seqtime)){
                $fs = $seq[$c];
                $sc = $seq[$c+1];
                if($fs=="TI"&&$sc=="TO"){
                    // $grpseq[$c] = [$fs, $sc];
                    // $grptime[$c] = [$seqtime[$c], $seqtime[$c+1]];
                    echo " OK ";
                }else{
                    if($fs!="TI")
                        

                    if($sc!="TO")
                }

                $t = strtotime($seqtime[$c+1]);
                $diff = $t - $f;
                $diff = AttendanceDao::convertTime($diff);
                echo "- DIFFERENCE: ".($diff)."<br>"; 
                echo $fs." - ".$sc."<br>";   
            }
        }
        
        //echo "<br><br>----------TIME DIFF---------------------<br>";
        //echo "<br>Total Hours: ".$total->diff($sum)->format('%h Hr %i Min %s Sec'); //%y Years %m Months %d Days 
        //return $total->diff($sum)->format('%h Hours %i Minutes %s Seconds');
        //echo "<br>".date('Y-m-j', strtotime('3 weekdays')); die(); I can use this function
        //echo "<br>Total Hours: ".$total->diff($sum)->format('%y Years %m Months %d Days %h Hours %i Minutes %s Seconds');
        die('<br>--------------------------------');    
    }

    function convertTime($s) { 
        $m = 0; $hr = 0; $td = "now";

        if($s > 59){ 
            $m = (int)($s/60); 
            $s = $s-($m*60); // sec left over 
            $td = "$m min"; 
        } 

        if($m>59){ 
            $hr = (int)($m / 60); 
            $m = $m - ($hr*60); // min left over 
            $td = "$hr hr"; 
            if($hr > 1) 
                $td .= "s";
            
            if($m > 0) 
                $td .= ", $m min";
        } 
    
        return $td; 
    } 

    function getEmployeeAttendance($from, $to, $emp_id){

        //Select MIN (TI) between 2 dates
        //$start = AttendanceDao::getMinTimeInBetweenDatetime($from, $to, $emp_id);
        
        $data = DB::table('employees AS e')
                    ->select('e.first_name', 'e.last_name', 'e.is_active', 'e.pos_title', 'a.datetime', 'a.emp_id', 't.code', 't.description')
                    ->leftJoin('attendance AS a' , 'e.id', '=', 'a.emp_id')
                    ->leftJoin('attendancetype as t', 'a.type_id', '=', 't.id')
                    ->where('e.id', '=', $emp_id)
                    ->whereBetween('datetime', [$from, $to])
                    ->orderBy('datetime', 'ASC')
                    ->get()
                    ->toArray();

        $ti = $to = $datetime = $seq = $seqtime = $preset = array();
        $brkincount = $brkoutcount = 0;

        for($c=0;$c<count($data);$c++){
            //check ti to to
            echo $data[$c]->code." - ".date('M d, Y g:i A', strtotime($data[$c]->datetime))."<br>";

            $datetime[$c] = $data[$c]->datetime;
            $preset[$c] = $data[$c]->code;

            if($data[$c]->code=="TI"||$data[$c]->code=="TO"){
                $seq[] = $data[$c]->code;
                $seqtime[] = $data[$c]->datetime;
            }

            ($data[$c]->code=="BI") ? $brkincount++ : null;
    
            ($data[$c]->code=="BO") ? $brkoutcount++ : null;

            if($data[$c]->code=="TI")
                $ti[$c] = $data[$c]->datetime;

            if($data[$c]->code=="TO")
                $to[$c] = $data[$c]->datetime;
        }

        echo "<br>BRK-OUT-COUNT: ".$brkincount."<br>BRK-IN-COUNT: ".$brkoutcount."<br>"; 
        echo "TIME-IN COUNT: ".count($ti)."<br>TIME-OUT COUNT: ".count($to)."<br><br>";

        //*** DEFICIENCY -> DECLARE INCOMPLETE 

        if(!is_null($data)){
            $start_code = $data[0]->code;
            $start_datetime = $data[0]->datetime;

            //Traversing Through Records 
            AttendanceDao::traverseRecords($data, $start_code, $start_datetime, $datetime, $ti, $to, $seq, $seqtime);
        }

        // //TEMP
        // if($brkincount!=$brkoutcount||count($ti)!=count($to)){
        //     echo "<u>Incomplete Record</u><br><br>";
        //     echo "<br>Total Hours: Incomplete Record"; die();
        // }else{
        //     echo "<br>Complete";
        // }
        // //TEMP

        //Debugging
        echo "<br><br>-----FOR DEBUGGING---------<br>";
        for($c=0;$c<count($datetime);$c++){
            echo $datetime[$c]." - ".$preset[$c]."<br>";
        }
    
        die('');
    }

    function checkIfPunchComplete($data, $emp_id){ //Checking One Day if completed
        //Declared to be constant
        //var_dump($data); die();
        //get the next missing and must be less than
        $ti = $to = false;
        $brkincount = $brkoutcount = 0;
        $presets = $datetime = array();
    
        for($c=0;$c<count($data);$c++){
            echo $data[$c]->code."<br>";

            $datetime[$c] = $data[$c]->datetime;
            $presets[$c] = $data[$c]->code;

            ($data[$c]->code=="BI") ? $brkincount++ : null;
    
            ($data[$c]->code=="BO") ? $brkoutcount++ : null;

            if($data[$c]->code=="TI"){
                $last_time_in = $data[$c]->datetime;
                $ti = true;
            }

            if($data[$c]->code=="TO"){
                $last_time_out = $data[$c]->datetime;
                $to = true;
            }
        }

        echo "BRK-OUT-COUNT: ".$brkincount."<br>BRK-IN-COUNT: ".$brkoutcount."<br>"; 
        
        //*** DEFICIENCY -> DECLARE INCOMPLETE 
        //($brkincount % 2 != 0)||($brkoutcount % 2 != 0)
        if($brkincount!=$brkoutcount||$ti==false||$to==false){ 
            if($ti==false){ //No Time In 
                //$next_time_in = AttendanceDao::getNextTimeIn($last_time_out, $emp_id);
            }

            if($to==false){ //No Time Out

            }

            echo "<u>Incomplete Record</u><br><br>";
            echo "<br>Total Hours: Incomplete Record"; die();
        }
        else //Complete then calculate total hours then check for violation 2 (15 Minutes Break) 1 (1 Hour Lunchbreak)
        {
             echo "Completed!<br>";
        } 
     
        //Debugging
        echo "<br><br>-----FOR DEBUGGING---------<br>";
        for($c=0;$c<count($datetime);$c++){
            echo $datetime[$c]." - ".$presets[$c]."<br>";
        }
    
        die('<br>zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz');
    }

    /*function getEmployeeAttendance($from, $to, $emp_id){ //With Full Type Support
        //Get Distinct?
        //Get the minimum punch >= dtfrom
      
        $distnctdate = AttendanceDao::getDistinctDateAttendanceOfEmployee($from, $to, $emp_id);
        //var_dump($distnct); die();
        for($a=0;$a<count($distnctdate);$a++){
            //DB::connection()->enableQueryLog();
            $data[$a] = DB::table('employees AS e')
                        ->select('e.first_name', 'e.last_name', 'e.is_active', 'e.pos_title', 'a.datetime', 'a.emp_id', 't.code', 't.description')
                        ->leftJoin('attendance AS a' , 'e.id', '=', 'a.emp_id')
                        ->leftJoin('attendancetype as t', 'a.type_id', '=', 't.id')
                        ->where('e.id', '=', $emp_id)
                        ->whereDate('a.datetime', $distnctdate[$a]->date)
                        ->orderBy('datetime', 'ASC')
                        ->get()
                        ->toArray();
            //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");         
        }
      
        echo "Employee Name: <b>".$data[0][0]->last_name.", ".$data[0][0]->first_name."</b><br>Position: <b>".$data[0][0]->pos_title."</b><br><br><strong>Attendance Record</strong><br>----------------------------<br><br>****************************<br>";

        for($a=0;$a<count($data);$a++){ //distinct date
            //for($b=0;$b<count($data[$a]);$b++){ //traversing through presets [TI - TO]
                //check if punch completed
                echo "<i>".date('M d, Y g:i A', strtotime($data[$a][0]->datetime))."</i><br>";
                $stat[$a] = AttendanceDao::checkIfPunchComplete($data[$a], $emp_id);
                //var_dump($stat[$a]); die();
                //echo $data[$a][$b]->datetime." - ".$data[$a][$b]->description." - ".$data[$a][$b]->code."<br>";
            //}
            echo "<br>****************************<br>";
        }
        
        //var_dump($stat); 
        die('----------End of Result-------------');
    }

    function checkIfPunchComplete($data, $emp_id){ //Checking One Day if completed (WITH FULL TYPE SUPPORT)
        //Declared to be constant
        $presets = array('TI', 'BO1', 'BI1', 'LO', 'LI', 'BO2', 'BI2', 'TO');
        $presets1 = array('Time in', 'Break Out 1', 'Break In 1', 'Lunch Out', 'Lunch In', 'Break Out 2', 'Break In 2', 'Time Out');
        $datetime = array('', '', '', '', '', '', '', '');
        
        for($a=0;$a<count($presets);$a++){
            for($c=0;$c<count($data);$c++){
                if($presets[$a]==$data[$c]->code)
                {
                    $datetime[$a] = $data[$c]->datetime;
                    if($data[$c]->code=="TI")
                        $last_time_in = $data[$c]->datetime;

                    if($data[$c]->code=="TO")
                        $last_time_out = $data[$c]->datetime;
                }
            }
        }

        //if TI or TO is 0 -> get the next TI else calculate
        if(in_array('', $datetime)){ //*** DEFICIENCY *REVISION ONCE HAS DEFICIENCY DECLARE INCOMPLETE 
            //if($datetime[7]==0){ //No Time Out - REMARKKKKKKKKKKKKKKKKKKKKKKSSSS
                //$next_time_in = AttendanceDao::getNextTimeIn($last_time_out, $emp_id);
            //if($datetime[7]==''&&$datetime[0]!='') //No TimeOut but has Time In
             //   $next_time_in = AttendanceDao::getNextTimeIn($last_time_out, $emp_id);
            echo "<u>Incomplete Record</u><br><br>";
            for($c=0;$c<count($datetime);$c++){
                if($datetime[$c]!="")
                    echo date('M d, Y g:i A', strtotime($datetime[$c]))." - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
                else
                    echo "No Record&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
            }
            echo "<br>Total Hours: Incomplete Record";
            return 0;
            //return "Incomplete";
        }
        else //Complete then calculate total hours then check for violation 2 (15 Minutes Break) 1 (1 Hour Lunchbreak)
        {
            //echo "<br>Calculating......<br>";
            echo "<u>Complete Record</u><br><br>";
            $sum = new DateTime('00:00');
            $total = clone $sum;
            
            for($c=0;$c<count($datetime);$c++){
                $f = new DateTime($datetime[$c]);
                echo date('M d, Y g:i A', strtotime($datetime[$c]))." - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
                if($c!=3){ //Don't Include Lunch Breaks
                    if(array_key_exists($c+1,$datetime))
                    {
                        $t = new DateTime($datetime[$c+1]);
                        $diff = date_diff($f, $t);
                        //echo $diff->format('%y Years %m Months %d Days %h Hours %i Minutes %s Seconds')."<br>";
                        $diffx[$c] = $diff;
                        $total->add($diffx[$c]);
                    }
                }
            }
            //echo "<br><br>----------TIME DIFF---------------------<br>";
            echo "<br>Total Hours: ".$total->diff($sum)->format('%h Hr %i Min %s Sec'); //%y Years %m Months %d Days 
            return $total->diff($sum)->format('%h Hours %i Minutes %s Seconds');
            //echo "<br>".date('Y-m-j', strtotime('3 weekdays')); die(); I can use this function
            //echo "<br>Total Hours: ".$total->diff($sum)->format('%y Years %m Months %d Days %h Hours %i Minutes %s Seconds');
            
              Total Amount for break - 30 minutes 
              Total Amount for Lunchbreak - 1 Hour
             
        } 
     
        //Debugging
        /*echo "<br><br>-----FOR DEBUGGING---------<br>";
        for($c=0;$c<count($datetime);$c++){
            echo $datetime[$c]." - ".$presets[$c]."<br>";
        }
    
        die('<br>zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz');
    }*/

    function getNextTimeIn($last_time_out, $emp_id){
        var_dump(DB::table('attendance as a')
                ->select('a.datetime')
                ->leftJoin('attendancetype AS t' , 'a.type_id', '=', 't.id')
                ->where('a.emp_id', '=', $emp_id)
                ->where('a.datetime', '>', $last_time_out)
                ->where('t.code', 'TI')
                ->get()
                ->toArray()); die();
    }

    function getAllEmployeeAttendance($from, $to, $accstat){
        //DB::connection()->enableQueryLog();
        return DB::table('employees AS e')
                ->select('e.first_name', 'e.last_name', 'e.is_active', 'e.pos_title', 'a.datetime', 'a.emp_id', 't.code', 't.description')
                ->leftJoin('attendance AS a' , 'e.id', '=', 'a.emp_id')
                ->leftJoin('attendancetype as t', 'a.type_id', '=', 't.id')
                ->whereRaw($accstat)
                ->whereBetween('datetime', [$from, $to])
                ->orderBy('datetime', 'ASC')
                ->get();
        
        //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");
                //->toSql();
                //->toArray();
    }

    function getMinimumDTOfEmployeeAttendance($accstat){
        $qry = DB::table('attendance')
                   ->select('datetime')
                   ->whereRaw($accstat)
                   ->min('datetime');

        return (is_null($qry)) ? date('Y-m-d 00:00:00') : $qry;
    }

    function getMaximumDTOfEmployeeAttendance($accstat){
        $qry = DB::table('attendance')
                   ->whereRaw($accstat)
                   ->max('datetime');

        return (is_null($qry)) ? date('Y-m-d 00:00:00') : $qry;
    }
}