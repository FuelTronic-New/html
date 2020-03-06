<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route:get('testing',function(){
	echo 'testing developer';
	
	
});

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('test-procedure', function (){

	$tankReconData = \DB::select("call TankReconDates('10','".date('Y-m-d', strtotime('-3 months'))."', '" . date('Y-m-d')."')");
	foreach ($tankReconData as $tankRecon){
		$tankRecon->date = date('Y-m-d', strtotime($tankRecon->TransactionDate));
	}
	$tankReconDataGroupByTank = collect($tankReconData)->groupBy('TankName');
	$tankReconDataGroupByDate = [];
	foreach ($tankReconDataGroupByTank as $key=>$value){
		$tankReconDataGroupByDate[$key] = [];
		foreach ($value as $Tkey=>$Tvalue){
			$tankReconDataGroupByDate[$key][$Tvalue->date][] = $Tvalue;
		}
	}
	dd($tankReconDataGroupByDate);
});

Route::group(['prefix' => 'api', 'middleware' => 'allowCrossDomain'], function () {
    /*Route::get('testing',function(){
    echo "testing developer";
});   
Route::middleware('jwt.auth')->get('users', function(Request $request) {
   // return auth()->user();
});*/
    // User registration and authentication
    Route:post('/user/create', ['as' => 'createUser', 'uses' => 'AuthenticateController@register']);
    Route::post('/user/authenticate', ['as' => 'authenticateUser', 'uses' => 'AuthenticateController@authenticateUser']);

    // User forgot password and password recovery
    Route::post('/password/email', ['as' => 'postEmail', 'uses' => 'AuthenticateController@postEmail']);
    Route::post('/password/reset', ['as' => 'postReset', 'uses' => 'AuthenticateController@postReset']);


    /* ATG Readings Routes */
    Route::get('/atg/{guid}', ['as' => 'getAtgDetail', 'uses' => 'AtgController@getAtgDetail']);
    Route::post('/atg/{guid}', ['as' => 'updateAtgDetail', 'uses' => 'AtgController@store']);
    Route::put('/atg/{guid}', ['as' => 'storeAtgTransactions', 'uses' => 'AtgController@storeTransaction']);


	/* Pump System Routes */
	Route::get('/pump/{guid}', ['as' => 'getPumpDetail', 'uses' => 'AtgController@getPumpDetail']);
	Route::get('/attendant/{guid}', ['as' => 'getAttendantDetail', 'uses' => 'AtgController@getAttendantDetail']);
	Route::get('/vehicle/{guid}', ['as' => 'getVehicleDetail', 'uses' => 'AtgController@getVehicleDetail']);
	Route::get('/job/{guid}', ['as' => 'getJobDetail', 'uses' => 'AtgController@getJobDetail']);
	Route::get('/customertransaction/{guid}', ['as' => 'getcustomertransaction', 'uses' => 'AtgController@customertransaction']);
	Route::post('/customertransaction/{guid}', ['uses' => 'AtgController@createcustomertransaction']);

    /* ATG Transactions Routes */
    Route::resource('atgtransactions', 'AtgTransactionsController', ['except' => ['update', 'destroy']]);

    Route::group(['middleware' => ['jwt.auth', 'allowAccess']], function () {
	    // User login, update profile and change password
	    Route::get('/user', ['as' => 'getUser', 'uses' => 'UserController@getUser']);
	    Route::post('/user/update', ['as' => 'updateUser', 'uses' => 'UserController@updateUser']);
	    Route::post('/user/change-password', ['as' => 'changePassword', 'uses' => 'UserController@changePassword']);

	    Route::get('/usertrasactions', ['as' => 'getUserTransactions', 'uses' => 'UserController@getUserTransactions']);
        Route::post('/usertrasactiondetail', ['as' => 'getUserTransactionDetail', 'uses' => 'UserController@getUserTransactionDetail']);

	    /* Sites Routes */
        Route::resource('sites', 'SitesController', ['except' => ['update', 'destroy']]);
        Route::post('/sites/update/{id}', ['as' => 'updateSite', 'uses' => 'SitesController@update']);
        Route::get('/sites/delete/{id}', ['as' => 'deleteSite', 'uses' => 'SitesController@delete']);

        /* Grades Routes */
        Route::resource('grades', 'GradesController', ['except' => ['update', 'destroy']]);
        Route::post('/grades/update/{id}', ['as' => 'updateGrade', 'uses' => 'GradesController@update']);
        Route::get('/grades/delete/{id}', ['as' => 'deleteGrade', 'uses' => 'GradesController@delete']);

        /* Tanks Routes */
        Route::get('/tanks/create/{siteID}', ['as' => 'createTank', 'uses' => 'TanksController@create']);
        Route::resource('tanks', 'TanksController', ['except' => ['update', 'destroy']]);
        Route::post('/tanks/update/{id}', ['as' => 'updateTank', 'uses' => 'TanksController@update']);
        Route::get('/tanks/delete/{id}', ['as' => 'deleteTank', 'uses' => 'TanksController@delete']);

        /* Pumps Routes */
        Route::get('/pumps/guid', ['as' => 'guidPump', 'uses' => 'PumpsController@generateGUID']);
        Route::resource('pumps', 'PumpsController', ['except' => ['update', 'destroy']]);
        Route::post('/pumps/update/{id}', ['as' => 'updatePump', 'uses' => 'PumpsController@update']);
        Route::get('/pumps/delete/{id}', ['as' => 'deletePump', 'uses' => 'PumpsController@delete']);

        /* Hoses Routes */
        Route::get('/hoses/create/{siteID}', ['as' => 'createHose', 'uses' => 'HosesController@create']);
        Route::resource('hoses', 'HosesController', ['except' => ['update', 'destroy', 'create']]);
        Route::post('/hoses/update/{id}', ['as' => 'updateHose', 'uses' => 'HosesController@update']);
        Route::get('/hoses/delete/{id}', ['as' => 'deleteHose', 'uses' => 'HosesController@delete']);

        /* Attendants Routes */
        Route::get('/attendants/getsites/{attendantID}', ['as' => 'getSitesOfAttendant', 'uses' => 'AttendantsController@getSitesOfAttendant']);
        Route::post('/attendants/updatesitesofattedant', ['as' => 'updateSitesOfAttendant', 'uses' => 'AttendantsController@updateSitesOfAttendant']);
        Route::any('/attendants/uploadimage', ['as' => 'uploadImage', 'uses' => 'AttendantsController@uploadImage']);
        Route::get('/attendants/create', ['as' => 'createSite', 'uses' => 'AttendantsController@create']);
        Route::resource('attendants', 'AttendantsController', ['except' => ['update', 'destroy', 'create']]);
        Route::post('/attendants/update/{id}', ['as' => 'updateSite', 'uses' => 'AttendantsController@update']);
        Route::get('/attendants/delete/{id}', ['as' => 'deleteSite', 'uses' => 'AttendantsController@delete']);

        /* Customers Routes */
        Route::get('/customers/getsites/{customerID}', ['as' => 'getSitesOfCustomer', 'uses' => 'CustomersController@getSitesOfCustomer']);
        Route::post('/customers/updatesitesofcustomer', ['as' => 'updateSitesOfCustomer', 'uses' => 'CustomersController@updateSitesOfCustomer']);
        Route::resource('customers', 'CustomersController', ['except' => ['update', 'destroy']]);
        Route::post('/customers/update/{id}', ['as' => 'updateSite', 'uses' => 'CustomersController@update']);
        Route::get('/customers/delete/{id}', ['as' => 'deleteSite', 'uses' => 'CustomersController@delete']);

        /* Vehicles Routes */
        Route::get('/vehicles/getsites/{vehicleID}', ['as' => 'getSitesOfVehicle', 'uses' => 'VehiclesController@getSitesOfVehicle']);
        Route::post('/vehicles/updatesitesofvehicle', ['as' => 'updateSitesOfVehicle', 'uses' => 'VehiclesController@updateSitesOfVehicle']);
        Route::any('/vehicles/uploadimage', ['as' => 'uploadImage', 'uses' => 'VehiclesController@uploadImage']);
        Route::get('/vehicles/create', ['as' => 'createSite', 'uses' => 'VehiclesController@create']);
        Route::resource('vehicles', 'VehiclesController', ['except' => ['update', 'destroy', 'create']]);
        Route::post('/vehicles/update/{id}', ['as' => 'updateSite', 'uses' => 'VehiclesController@update']);
        Route::get('/vehicles/delete/{id}', ['as' => 'deleteSite', 'uses' => 'VehiclesController@delete']);

        /* Suppliers Routes */
        Route::get('/suppliers/getsites/{supplierID}', ['as' => 'getSitesOfSupplier', 'uses' => 'SuppliersController@getSitesOfSupplier']);
        Route::post('/suppliers/updatesitesofsupplier', ['as' => 'updateSitesOfSupplier', 'uses' => 'SuppliersController@updateSitesOfSupplier']);
        Route::resource('suppliers', 'SuppliersController', ['except' => ['update', 'destroy']]);
        Route::post('/suppliers/update/{id}', ['as' => 'updateSite', 'uses' => 'SuppliersController@update']);
        Route::get('/suppliers/delete/{id}', ['as' => 'deleteSite', 'uses' => 'SuppliersController@delete']);

        /* Suppliers Routes */
        Route::get('/tags/getsites/{tagID}', ['as' => 'getSitesOfVehicle', 'uses' => 'TagsController@getSitesOfTag']);
        Route::post('/tags/updatesitesoftag', ['as' => 'updateSitesOfVehicle', 'uses' => 'TagsController@updateSitesOfTag']);
        Route::resource('tags', 'TagsController', ['except' => ['update', 'destroy']]);
        Route::post('/tags/update/{id}', ['as' => 'updateTag', 'uses' => 'TagsController@update']);
        Route::get('/tags/delete/{id}', ['as' => 'deleteTag', 'uses' => 'TagsController@delete']);

        /* Site Users Routes */
        Route::get('/siteusers/getsites/{userID}', ['as' => 'getSitesOfUser', 'uses' => 'SiteusersController@getSitesOfUser']);
        Route::post('/siteusers/updatesitesofuser', ['as' => 'updateSitesOfUser', 'uses' => 'SiteusersController@updateSitesOfUser']);
        Route::resource('siteusers', 'SiteusersController', ['except' => ['update', 'destroy']]);
        Route::post('/siteusers/update/{id}', ['as' => 'updateUser', 'uses' => 'SiteusersController@update']);
        Route::get('/siteusers/delete/{id}', ['as' => 'deleteUser', 'uses' => 'SiteusersController@delete']);

        /* ATG Readings Users Routes */
        Route::resource('atgreadings', 'AtgReadingsController', ['except' => ['update', 'destroy']]);
        Route::post('/atgreadings/update/{id}', ['as' => 'updateUser', 'uses' => 'AtgReadingsController@update']);
        Route::get('/atgreadings/delete/{id}', ['as' => 'deleteUser', 'uses' => 'AtgReadingsController@delete']);
        Route::get('/atgreadings/create/{siteid}', ['as' => 'createSite', 'uses' => 'AtgReadingsController@create']);

        /* Site Users Routes */
        Route::get('/jobs/getsites/{jobID}', ['as' => 'getSitesOfJob', 'uses' => 'JobsController@getSitesOfJob']);
        Route::post('/jobs/updatesitesofjob', ['as' => 'updateSitesOfJob', 'uses' => 'JobsController@updateSitesOfJob']);
        Route::get('/jobs/create/{siteID}', ['as' => 'createJob', 'uses' => 'JobsController@create']);
        Route::resource('jobs', 'JobsController', ['except' => ['update', 'destroy']]);
        Route::post('/jobs/update/{id}', ['as' => 'updateJob', 'uses' => 'JobsController@update']);
        Route::get('/jobs/delete/{id}', ['as' => 'deleteJob', 'uses' => 'JobsController@delete']);

        /* Location */
	    Route::get('/location/getsites/{locationID}', ['as' => 'getSitesOfLocation', 'uses' => 'LocationController@getSitesOfLocation']);
	    Route::post('/location/updatesitesoflocation', ['as' => 'updateSitesOfLocation', 'uses' => 'LocationController@updateSitesOfLocation']);
	    Route::resource('location', 'LocationController', ['except' => ['update', 'destroy']]);
        Route::post('/location/update/{id}', ['as' => 'updateLocation', 'uses' => 'LocationController@update']);
        Route::get('/location/delete/{id}', ['as' => 'deleteLocation', 'uses' => 'LocationController@delete']);

        /* ATG Readings Routes */
        Route::get('/atgdata/guid', ['as' => 'guidATGData', 'uses' => 'AtgDatasController@generateGUID']);
        Route::resource('atgdata', 'AtgDatasController', ['except' => ['update', 'destroy']]);
        Route::post('/atgdata/update/{id}', ['as' => 'updateUser', 'uses' => 'AtgDatasController@update']);
        Route::get('/atgdata/delete/{id}', ['as' => 'deleteUser', 'uses' => 'AtgDatasController@delete']);
        Route::get('/atgdata/create/{siteid}', ['as' => 'createSite', 'uses' => 'AtgDatasController@create']);

        /* Fuel Drops Routes */
        Route::get('/fueldrops/getlitreprice/{tankID}', ['as' => 'getLitrePrice', 'uses' => 'FuelDropsController@getLitrePrice']);
        Route::resource('fueldrops', 'FuelDropsController', ['except' => ['update', 'destroy']]);
        Route::post('/fueldrops/update/{id}', ['as' => 'updateFD', 'uses' => 'FuelDropsController@update']);
        Route::get('/fueldrops/delete/{id}', ['as' => 'deleteFD', 'uses' => 'FuelDropsController@delete']);
        Route::get('/fueldrops/create/{fueldropid}', ['as' => 'createFD', 'uses' => 'FuelDropsController@create']);

        /* Fuel Transfers Routes */
        Route::get('/fueltransfers/create', ['as' => 'createFT', 'uses' => 'FuelTransfersController@create']);
        Route::resource('fueltransfers', 'FuelTransfersController', ['except' => ['update', 'destroy']]);
        Route::post('/fueltransfers/update/{id}', ['as' => 'updateFT', 'uses' => 'FuelTransfersController@update']);
        Route::get('/fueltransfers/delete/{id}', ['as' => 'deleteFT', 'uses' => 'FuelTransfersController@delete']);

        /* Customer Transactions Routes */
        Route::resource('customertransactions', 'CustomerTransactionsController', ['except' => ['update', 'destroy']]);
        Route::post('/customertransactions/update/{id}', ['as' => 'updateCT', 'uses' => 'CustomerTransactionsController@update']);
        Route::get('/customertransactions/delete/{id}', ['as' => 'deleteCT', 'uses' => 'CustomerTransactionsController@delete']);
        Route::get('/customertransactions/create/{siteID}', ['as' => 'createCT', 'uses' => 'CustomerTransactionsController@create']);
        Route::get('/customertransactions/getdetailsfromhoseid/{hoseID}', ['as' => 'getDetailsFromHoseID', 'uses' => 'CustomerTransactionsController@getDetailsFromHoseID']);
        Route::get('/customertransactions/getdetailsbysiteid/{id}', ['as' => 'getTransactionsById', 'uses' => 'CustomerTransactionsController@getTransactionsById']);
        Route::get('/customertransactions/getTransactionsByIdWithDate/{id}', ['as' => 'getTransactionsByIdWithDate', 'uses' => 'CustomerTransactionsController@getTransactionsByIdWithDate']);
        Route::post('/Fuel_Report', ['as' => 'Fuel', 'uses' => 'CustomerTransactionsController@Fuel']);

        /* Payment Routes */
        Route::resource('payments', 'PaymentsController', ['except' => ['update', 'destroy']]);
        Route::post('/payments/update/{id}', ['as' => 'updatePayment', 'uses' => 'PaymentsController@update']);
        Route::get('/payments/delete/{id}', ['as' => 'deletePayment', 'uses' => 'PaymentsController@delete']);
        Route::get('/payments/create/{siteID}', ['as' => 'createPayment', 'uses' => 'PaymentsController@create']);

        /* Fuel Adjustment Routes */
        Route::resource('fueladjustments', 'FuelAdjustmentController', ['except' => ['update', 'destroy']]);
        Route::post('/fueladjustments/update/{id}', ['as' => 'updateFuelAdjustment', 'uses' => 'FuelAdjustmentController@update']);
        Route::get('/fueladjustments/delete/{id}', ['as' => 'deleteFuelAdjustment', 'uses' => 'FuelAdjustmentController@delete']);
        Route::get('/fueladjustments/create/{siteID}', ['as' => 'createFuelAdjustment', 'uses' => 'FuelAdjustmentController@create']);

	    /*Customer Report */
        Route::get('/customervehicles', ['as' => 'customerVehicles', 'uses' => 'Reports\CustomerReportController@customerVehicles']);
        Route::post('/customervehiclesreport', ['as' => 'customerVehiclesReport', 'uses' => 'Reports\CustomerReportController@customerVehiclesReport']);
        Route::post('/customervehicletransactionsreport', ['as' => 'customerVehicleTransactionsReport', 'uses' => 'Reports\CustomerReportController@customerVehicleTransactionsReport']);
        Route::post('/customerlistreport', ['as' => 'customerListReport', 'uses' => 'Reports\CustomerReportController@customerListReport']);
        Route::post('/customerstatementsreport', ['as' => 'customerStatementsReport', 'uses' => 'Reports\CustomerReportController@customerStatementsReport']);
        Route::post('/vehicletransactionsreport', ['as' => 'vehicletransactionsreport', 'uses' => 'Reports\CustomerReportController@vehicleTransactionsReport']);

        /* Staff Report */
        Route::get('/attendantslist', ['as' => 'attendants', 'uses' => 'Reports\StaffReportController@attendants']);
        Route::post('/attendantslistreport', ['as' => 'attendantsListReport', 'uses' => 'Reports\StaffReportController@attendantsListReport']);

        /* Supplier Report */
        Route::get('/supplierslist', ['as' => 'suppliers', 'uses' => 'Reports\SupplierReportController@suppliers']);
        Route::get('/supplierspurchaseslist', ['as' => 'suppliersPurchases', 'uses' => 'Reports\SupplierReportController@suppliersPurchases']);
        Route::post('/supplierslistreport', ['as' => 'suppliersListReport', 'uses' => 'Reports\SupplierReportController@suppliersListReport']);

        /* Stock Report */
        Route::get('/siteslist', ['as' => 'siteslist', 'uses' => 'Reports\StockReportController@sites']);
        Route::post('/gradeslistreport', ['as' => 'gradeslistreport', 'uses' => 'Reports\StockReportController@gradesListReport']);
        Route::post('/gradespricelistreport', ['as' => 'gradespricelistreport', 'uses' => 'Reports\StockReportController@gradesListReport']);
        Route::post('/stockadjustmentreport', ['as' => 'stockadjustmentreport', 'uses' => 'Reports\StockReportController@stockAdjustmentReport']);
        Route::post('/sitepumps', ['as' => 'sitepumps', 'uses' => 'Reports\StockReportController@sitePumps']);
        Route::post('/stocklevelreport', ['as' => 'stocklevelreport', 'uses' => 'Reports\StockReportController@stockLevelReport']);

        /* Financial Report */
        Route::post('/sitecustomersfinancial', ['as' => 'sitecustomersfinancial', 'uses' => 'Reports\FinancialReportController@sitecustomersfinancial']);
        Route::post('/sitesuppliersfinancial', ['as' => 'sitesuppliersfinancial', 'uses' => 'Reports\FinancialReportController@sitesuppliersfinancial']);
	    Route::post('/sitesalesfinancial', ['as' => 'sitesalesfinancial', 'uses' => 'Reports\FinancialReportController@sitesalesfinancial']);
	    Route::post('/custageanalysis', ['as' => 'custageanalysis', 'uses' => 'Reports\FinancialReportController@custAgeAnalysis']);
	    Route::post('/supplierageanalysis', ['as' => 'supplierageanalysis', 'uses' => 'Reports\FinancialReportController@supplierAgeAnalysis']);
        Route::post('/sales', ['as' => 'salesfinancial', 'uses' => 'Reports\FinancialReportController@sales']);
        Route::post('/voids', ['as' => 'voidsfinancial', 'uses' => 'Reports\FinancialReportController@voids']);
        Route::post('/financial-transaction', ['uses' => 'Reports\FinancialReportController@FinancialTransaction']);
        Route::post('/sars', ['uses' => 'Reports\FinancialReportController@sarsReport']);
        Route::post('/tankpumpsfromhose', ['as' => 'tankpumpsfromhose', 'uses' => 'Reports\FinancialReportController@tankpumpsfromhose']);
        Route::post('/tanksfromsite', ['as' => 'tanksfromsite', 'uses' => 'Reports\FinancialReportController@tanksfromsite']);
        Route::post('/gradesfromtanks', ['as' => 'gradesfromtanks', 'uses' => 'Reports\FinancialReportController@gradesfromtanks']);

        /* Fuel Report */
        Route::post('/getsitestanksgrades', ['as' => 'getsitestanksgrades', 'uses' => 'Reports\FuelReportController@getSitesTanksGrades']);
        Route::post('/getsitestanksgradessuppliers', ['as' => 'getsitestanksgradessuppliers', 'uses' => 'Reports\FuelReportController@getSitesTanksGradesSuppliers']);
        Route::post('/getsitesgrades', ['as' => 'getsitesgrades', 'uses' => 'Reports\FuelReportController@getSitesGrades']);
        Route::post('/getsiteshosestanksgrades', ['as' => 'getsiteshosestanksgrades', 'uses' => 'Reports\FuelReportController@getSitesHosesTanksGrades']);
        Route::post('/getsitesattendantsgrades', ['as' => 'getsitesattendantsgrades', 'uses' => 'Reports\FuelReportController@getSitesAttendantsGrades']);

        Route::post('/deliveriesperperiod', ['as' => 'deliveriesperperiod', 'uses' => 'Reports\FuelReportController@getDeliveriesPerPeriod']);
        Route::post('/getpumptransactions', ['as' => 'getpumptransactions', 'uses' => 'Reports\FuelReportController@getPumpTransactions']);
        Route::post('/getdispensedgrades', ['as' => 'getdispensedgrades', 'uses' => 'Reports\FuelReportController@getDispensedGrades']);
        Route::post('/getdispensedtime', ['as' => 'getdispensedtime', 'uses' => 'Reports\FuelReportController@getDispensedTime']);
        Route::post('/getdispensedhoses', ['as' => 'getdispensedhoses', 'uses' => 'Reports\FuelReportController@getDispensedHoses']);
        Route::post('/getdispensedtanks', ['as' => 'getdispensedtanks', 'uses' => 'Reports\FuelReportController@getDispensedTanks']);
        Route::post('/getdispensedattendants', ['as' => 'getdispensedattendants', 'uses' => 'Reports\FuelReportController@getDispensedAttendants']);
        Route::post('/getatgdata', ['as' => 'atgdata', 'uses' => 'Reports\FuelReportController@getAtgData']);
        Route::post('/getatgdatahourly', ['as' => 'atgdatahourly', 'uses' => 'Reports\FuelReportController@getAtgDataHourly']);
        Route::post('/dailytankrecon', ['as' => 'dailytankrecon', 'uses' => 'Reports\FuelReportController@dailyTankRecon']);

	    /* Dashboard Routes */
	    Route::get('dashboard2/c1/{site_id?}', 'DashboardController@chart1new');
	    Route::get('dashboard2/c2/{site_id?}', 'DashboardController@chart2');
	    Route::get('dashboard2/c3/vehicles', 'DashboardController@vehicles');
	    Route::get('dashboard2/c3/{site_id?}', 'DashboardController@chart3');
	    Route::get('dashboard2/{id?}', 'DashboardController@index2');
	    Route::resource('dashboard', 'DashboardController@index');
	    Route::resource('testdate', 'DashboardController@TestDate');

    });

});