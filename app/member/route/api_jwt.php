<?php
use think\facade\Route;

Route::post('api/jwt/token', 'api/jwt/token')->middleware(['ParamCheck','ApiAuth'],[1,2]);	//默认;
