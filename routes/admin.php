<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::prefix('admin/')->name('admin.')->group(function () {

    Volt::route('statistics', 'admin.statistics')->name('statistics');

    Volt::route('settings', 'admin.settings')->name('settings')->middleware('check.permission:view_settings');

    Volt::route('duty/logs', 'admin.logs')->name('logs')->middleware('check.permission:view_duty_logs');

    Volt::route('/duty/active', 'admin.active')->name('duty.active')->middleware('check.permission:view_duty_active');

    Volt::route('panel', 'admin.panel')->name('panel')->middleware('check.permission:view_admin_panel');

    Volt::route('exam', 'admin.exam-manager')->name('exam-manager')->middleware('check.permission:view_exam_manager');

    Volt::route('exam/log', 'admin.exam-result')->name('exam-results')->middleware('check.permission:view_exam_result');

    Volt::route('blacklist', 'admin.blacklist')->name('blacklist')->middleware('check.permission:view_blacklist');
});
