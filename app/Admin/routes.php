<?php

Route::get('', ['as' => 'admin.dashboard', function () {
	$content = __('lang.logowanie.login');
	return AdminSection::view($content, 'Dashboard');
}]);

Route::get('information', ['as' => 'admin.information', function () {
	$content = 'Define your information here.';
	return AdminSection::view($content, 'Information');
}]);