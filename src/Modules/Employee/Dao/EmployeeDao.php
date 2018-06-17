<?php 

namespace App\Modules\Employee\Dao;
use App\Modules\Employee\Models\EmployeeModel;
use Illuminate\Database\Capsule\Manager as DB;

class EmployeeDao {
    function getAllEmployee($dcfrom, $dcto, $accstat){
        return DB::table('employees AS e')
               ->select('e.*')
               ->get();
    }

    function getMinimumDTOfEmployeeAttendance(){
        $qry = DB::table('attendance')
               ->orderBy('id', 'DESC')
               ->limit(1)
               ->select('datetime')
               ->get();

        if(is_null($qry))
            return date('Y-m-d 00:00:00');
    }
}