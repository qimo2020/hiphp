<?php
use think\facade\Route;

Route::post('api/oauth/token', 'api/oauth/token')->middleware(['ParamCheck','ApiAuth'],[1,1]);	//默认;
