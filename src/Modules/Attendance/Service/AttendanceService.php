<?php
 
namespace App\Modules\Attendance\Service;
use App\Modules\Attendance\Models\AttendanceModel;
use App\Modules\Attendance\Dao\AttendanceDao;

class AttendanceService {
    function getAllEmployeeAttendance($req, $res){
        //return AttendanceModel::all()->toArray(); getQueryParams()
        //Unix Timestamps from GET Param
        $dtfrom = date('Y-m-d H:i:s', strip_tags($req->getAttribute('dtfrom')));
        $dtto = date('Y-m-d H:i:s', strip_tags($req->getAttribute('dtto')));
        $accstat = strip_tags($req->getAttribute('accstat'));
        //var_dump($dtfrom); var_dump($dtto); var_dump($accstat."<br>"); die();
        if(empty($dtfrom))
            $dtfrom = AttendanceDao::getMinimumDTOfEmployeeAttendance();
            
        if(empty($dtto))
            $dtto = AttendanceDao::getMaximumDTOfEmployeeAttendance();

        (empty($accstat)) ? $accstat = "is_active IS NOT NULL" : $accstat = "is_active = ".$accstat;
        //$dtfrom = '1497110400'; $dtto = '1497196800';
        //var_dump("<br><br>");
        //var_dump($dtfrom); var_dump($dtto); var_dump($accstat."<br>"); //die();
        $data =  AttendanceDao::getAllEmployeeAttendance($dtfrom, $dtto, $accstat)->toArray();
        //var_dump($data);die();
        echo "From: ".date('M d, Y g:i A', strtotime($dtfrom))." To: ".date('M d, Y g:i A', strtotime($dtto))."<br>";
        for($c=0;$c<count($data);$c++){
            echo $data[$c]->last_name.", ".$data[$c]->first_name."<br>";
            echo $data[$c]->pos_title."<br>";
            echo date('M d, Y g:i A', strtotime($data[$c]->datetime))." - ".$data[$c]->description." - ".$data[$c]->code."<br>";

        }
        die();
        //return AttendanceDao::getAllEmployeeAttendance($dtfrom, $dtto, $accstat)->toArray();
        //var_dump($obj[1]->first_name); die();
    }

    function getEmployeeAttendance($req, $res){ 
        $dtfrom = date('Y-m-d H:i:s', strip_tags($req->getAttribute('dtfrom')));
        $dtto = date('Y-m-d H:i:s', strip_tags($req->getAttribute('dtto')));
        //echo strtotime('2018-06-13 00:00:00'); die();
        //$emp_id = strip_tags($req->getAttribute('emp_id'));
        $emp_id = base64_decode(urldecode($req->getAttribute('emp_id')));
        //var_dump(urlencode(base64_encode(1))); die();
        echo "From: ".date('M d, Y g:i A', strtotime($dtfrom))." To: ".date('M d, Y g:i A', strtotime($dtto))."<br>";
        return (is_numeric($emp_id)) ? AttendanceDao::getEmployeeAttendance($dtfrom, $dtto, $emp_id)->toArray() : null;
    }

    function deleteEmployeeAttendance($req, $res){
        $att_id = base64_decode(urldecode($req->getAttribute('att_id')));
        //$att_id = strip_tags($req->getAttribute('att_id'));
        return (is_numeric($att_id)) ? AttendanceModel::where('id', $att_id)->delete() : null;
    }
}

