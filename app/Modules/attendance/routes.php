<?php
/**
 * Attendance Module Routes
 *
 * This file returns a callable that registers the module's routes
 */

return function($router, $moduleName) {
    // Attendance management routes (coach)
    $router->get('/coach/attendance', 'Modules\\attendance\\Controllers\\AttendanceController', 'index');
    $router->get('/coach/attendance/take', 'Modules\\attendance\\Controllers\\AttendanceController', 'takeAttendance');
    $router->post('/coach/attendance/save', 'Modules\\attendance\\Controllers\\AttendanceController', 'saveAttendance');
    $router->get('/coach/attendance/history', 'Modules\\attendance\\Controllers\\AttendanceController', 'history');
    $router->get('/coach/attendance/report', 'Modules\\attendance\\Controllers\\AttendanceController', 'report');
};
