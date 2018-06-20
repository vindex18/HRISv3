<?php

$app->get('/attendance/allemployee/{dtfrom:[0-9]+}/{dtto:[0-9]+}/[{accstat}]', 'AttendanceController:getAllEmployeeAttendance');
$app->get('/attendance/employee/{dtfrom:[0-9]+}/{dtto:[0-9]+}/{emp_id}', 'AttendanceController:getEmployeeAttendance');
$app->get('/attendance/employee-summary/{dtfrom:[0-9]+}/{dtto:[0-9]+}/{emp_id}', 'AttendanceController:getEmployeeAttendanceSummary');
$app->delete('/attendance/employee/{att_id}', 'AttendanceController:deleteEmployeeAttendance');
$app->post('/attendance/employee', 'AttendanceController:addAttendance');