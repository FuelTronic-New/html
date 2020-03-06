app.controller('FuelReportController', function ($scope, $rootScope, $http, CRUD, toaster, $state, $localStorage, $filter) {

    var listurl = 'siteslist';
    var tanklisturl = 'getsitestanksgrades';
    var dailytankreconurl = 'dailytankrecon';
    var deliverylisturl = 'getsitestanksgradessuppliers';
    var deliveryperiodurl = 'deliveriesperperiod';
    var pumplisturl = 'sitesalesfinancial';
    var pumptransactiondurl = 'getpumptransactions';
    var dispensegradelisturl = 'getsitesgrades';
    var dispensegradedurl = 'getdispensedgrades';
    var dispensehoselisturl = 'getsiteshosestanksgrades';
    var dispensehoseurl = 'getdispensedhoses';
    var dispensetanklisturl = 'getsiteshosestanksgrades';
    var dispensetankurl = 'getdispensedtanks';
    var dispenseattendantlisturl = 'getsitesattendantsgrades';
    var dispenseattendanturl = 'getdispensedattendants';
    var dispensetimelisturl = 'getsitesgrades';
    var dispensetimeurl = 'getdispensedtime';
    var atglisturl = 'getatgdata';
    var atglisthourlyurl = 'getatgdatahourly';

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;
    $scope.gradesData = [];
    $scope.tanksData = [];
    $scope.suppliersData = [];
    $scope.customersData = [];
    $scope.pumpsData = [];
    $scope.attendantsData = [];
    $scope.vehiclesData = [];
    $scope.hosesData = [];
    $scope.test = {};

    $scope.data = [];
    $scope.libraryTemp = {};
    $scope.totalItemsTemp = {};
    $scope.totalItems = 0;
    // $scope.searchText = '';

    $scope.currentDate = new Date();

    $scope.getAllSites = function () {
        CRUD.getAll(listurl).then(success).catch(failed);
        function success(res) {
            $scope.sites = res.data;
            if ($scope.sites.data.length) {
                if ($localStorage.site_id) {
                    angular.forEach($scope.sites.data, function (value, key) {
                        if (value.id == $localStorage.site_id) {
                            $scope.selectedSite = value;
                        }
                    });
                }
                else {
                    $rootScope.siteData = $scope.sites.data[0];
                    $scope.selectedSite = $scope.sites.data[0];
                    $localStorage.site_id = $scope.sites.data[0].id;
                }

                if ($state.is('app.daily_tank_recon')) {
                    $scope.changeSiteTankData();
                } else if ($state.is('app.deliveries_per_period')) {
                    $scope.changeSiteDeliveryData();
                } else if ($state.is('app.pump_transaction')) {
                    $scope.changeSitePumpTransactionsData();
                }else if ($state.is('app.dispensed_grade')) {
                    $scope.changeSiteDispenseGradeData();
                }else if ($state.is('app.dispensed_time')) {
                    $scope.changeSiteDispenseTimeData();
                }else if ($state.is('app.dispensed_hose')) {
                    $scope.changeSiteDispenseHoseData();
                }else if ($state.is('app.dispensed_tank')) {
                    $scope.changeSiteDispenseTankData();
                }else if ($state.is('app.dispensed_attendant')) {
                    $scope.changeSiteDispenseAttendantData();
                }else if ($state.is('app.atg_data_hourly') || $state.is('app.atg_data')) {
                    $scope.changeSiteTankData();
                }
            }
        }

        function failed(error) {
            //toaster.pop('error','',error.data.message);
        }
    };

    /****************************************  Daily Tank Recon  ***************************************************/
    $scope.changeSiteTankData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.tanksData = [];
        CRUD.postWithData(tanklisturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.tanksData = response.data.data.tanks;
        }

        function failed(error) {
        }
    }
    $scope.getTankList = function () {
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        if (!$scope.selectedTank){
            toaster.error('Fuel', 'Please select any tank.');
            return;
        }
        if (!$scope.selected_date){
            toaster.error('Fuel', 'Please select any date.');
            return;
        }

        var filterdata = {
            site_id: $scope.selectedSite.id,
            tanks: $scope.selectedTank.id,
            date: moment($scope.selected_date).format('YYYY-MM-DD'),
        };
        CRUD.postWithData(dailytankreconurl, $.param(filterdata)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
        }

        function failed(error) {
        }
    }
    /**************************************  ATG **************************************************/

    function getResultsPage(pageNumber, url) {
        $scope.tankIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
        var filterdata = {
            site_id: $scope.selectedSite.id,
            tank_ids: $scope.tankIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        $(document).find('#atg_hourly').DataTable().destroy();
        $(document).find('#atg_hourly').DataTable({
                "processing": true,
                "serverSide": true,
                "searchable": true,
                "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                "ajax": {
                    "url": $rootScope.API_BASE_URL + url + '?token=' + $localStorage.token,
                    "type": "POST",
                    "data": filterdata,
                    complete: function() {
                        $(document).find('.cloneTable').html($('.clonableTable').html());
                    }
                },
                "columns": [
                    {data: 'name', name: 'name'},
                    {data: 'tank_name', name: 'tank_name'},
                    {data: 'date', name: 'date'},
                    {data: 'time', name: 'time'},
                    {data: null, name: 'cm',render:function (data) {
                        return $filter('number')(data.cm, 2);
                    }},
                    {data: null, name: 'liters',render:function (data) {
                        return $filter('number')(data.liters, 2);
                    }}
                ]
            });



       /* if(! $.isEmptyObject($scope.libraryTemp)){
            CRUD.postWithDataPage(url, '?search='+$scope.searchText, $.param(filterdata)).then(success).catch(failed);
        }else{
            CRUD.postWithDataPage(url, '?page='+pageNumber, $.param(filterdata)).then(success).catch(failed);
        }
        function success(res) { console.log('res.data',res.data)
            $scope.data = res.data.data;
            $scope.totalItems = res.data.total;
            // toaster.pop('success', "ATG", 'Requested ATG listed successfully!');
        }

        function failed(error) {
        }*/
    }
    $scope.pageChanged = function(newPage) {
      getResultsPage(newPage, atglisturl);
    };
    $scope.getATGList = function () {
        // getResultsPage(1, atglisturl);

        $scope.tankIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
        var filterdata = {
            site_id: $scope.selectedSite.id,
            tank_ids: $scope.tankIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        $(document).find('#atg_report').DataTable().destroy();
        $(document).find('#atg_report').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "searchable": true,
                    "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                    "ajax": {
                        "url": $rootScope.API_BASE_URL + atglisturl + '?token=' + $localStorage.token,
                        "type": "POST",
                        "data": filterdata,
                        complete: function() {
                            $(document).find('.cloneTable').html($('.clonableTable').html());
                        }
                    },
                    "columns": [
                        {data: 'atg_name', name: 'atg_name'},
                        {data: 'tank_name', name: 'tank_name'},
                        {data: 'date', name: 'date'},
                        {data: 'time', name: 'time'},
                        {data: null, name: 'cm',render:function (data) {
                            return $filter('number')(data.cm, 2);
                        }},
                        {data: null, name: 'liters',render:function (data) {
                            return $filter('number')(data.liters, 2);
                        }}
                    ]
                });



        // $scope.tankIds = [];
        // $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
        // var filterdata = {
        //     site_id: $scope.selectedSite.id,
        //     tank_ids: $scope.tankIds.toString(),
        //     start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
        //     end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
        //     // date_range: $scope.date_range,
        // };
        // CRUD.postWithData(atglisturl, $.param(filterdata)).then(success).catch(failed);
        //
        //     function success(res) {
        //         $scope.rowCollection = res.data.data;
        //         $scope.displayCollection = res.data.data;
        //         toaster.pop('success', "ATG", res.data.message);
        //     }
        //
        //     function failed(error) {
        //     }
    }
    $scope.getATGListHourly = function () {
        getResultsPage(1, atglisthourlyurl);
    }
    $scope.pageChangedATGListHourly = function(newPage) {
      getResultsPage(newPage, atglisthourlyurl);
    };

    /**************************************  Deliveries per Period **************************************************/
    $scope.changeSiteDeliveryData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.gradesData = [];
        $scope.tanksData = [];
        $scope.suppliersData = [];
        CRUD.postWithData(deliverylisturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.gradesData = response.data.data.grades;
            $scope.tanksData = response.data.data.tanks;
            $scope.suppliersData = response.data.data.suppliers;
        }

        function failed(error) {
        }
    }
    $scope.getDeliveriesPeriodList = function (pageNumber) {
        $scope.tankIds = [];
        $scope.gradeIds = [];
        $scope.supplierIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];
        $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
        $rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);
        $rootScope.getMultiSelectIds($scope.test.selectedSuppliers, $scope.supplierIds);

        var filterdata = {
            site_id: $scope.selectedSite.id,
            tanks: $scope.tankIds.toString(),
            grades: $scope.gradeIds.toString(),
            suppliers: $scope.supplierIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        $(document).find('#deliveries_per_period').DataTable().destroy();
        $(document).find('#deliveries_per_period').DataTable({
                 "processing": true,
                 "serverSide": true,
                 "searchable": true,
                 "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                 "ajax": {
                     "url": $rootScope.API_BASE_URL + deliveryperiodurl + '?token=' + $localStorage.token,
                     "type": "POST",
                     "data": filterdata,
                     complete: function() {
                         $(document).find('.cloneTable').html($('.clonableTable').html());
                     }
                 },
                 "columns": [
                     {data: 'purchase_date', name: 'purchase_date'},
                     {data: null, name: 'supplier.name',render:function (data) {
                           if (data.supplier != null){
                               return (data.supplier.name != null) ? data.supplier.name :'';
                           }else{ return '';}
                       }},
                     {data: null, name: 'id',render:function (data) {
                         // NOTE: Not available from query so make it null temporary
                         return null;
                     }},
                     {data: 'id', name: 'id'},
                     {data: null, name: 'tank.name',render:function (data) {
                        if (data.tank != null){
                            return (data.tank.name != null) ? data.tank.name :'';
                        }else{ return '';}
                    }},
                     {data: null, name: 'litres',render:function (data) {
                         return $filter('number')(data.litres, 2);
                     }},
                     {data: null, name: 'tot_inc_vat',render:function (data) {
                         return $filter('number')(data.tot_inc_vat, 2);
                     }}
                 ]
             });

        // if(! $.isEmptyObject($scope.libraryTemp)){
        //     CRUD.postWithDataPage(deliveryperiodurl, '?search='+$scope.searchText, $.param(filterdata)).then(success).catch(failed);
        // }else{
        //     CRUD.postWithDataPage(deliveryperiodurl, '?page='+pageNumber, $.param(filterdata)).then(success).catch(failed);
        // }
        // function success(res) {
        //     $scope.data = res.data.data;
        //     $scope.totalItems = res.data.total;
        //     // toaster.pop('success', "Deliveries", res.data.message);
        // }
        //
        // function failed(error) {
        // }
    }
    /**************************************  Pump Transactions **************************************************/
    $scope.changeSitePumpTransactionsData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.customersData = [];
        $scope.pumpsData = [];
        $scope.attendantsData = [];
        $scope.hosesData = [];
        $scope.vehiclesData = [];
        $scope.gradesData = [];
        $scope.tanksData = [];
        CRUD.postWithData(pumplisturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.customersData = response.data.data.customers;
            $scope.pumpsData = response.data.data.pumps;
            $scope.attendantsData = response.data.data.attendants;
            $scope.hosesData = response.data.data.hoses;
            $scope.vehiclesData = response.data.data.vehicles;
            $scope.gradesData = response.data.data.grades;
            $scope.tanksData = response.data.data.tanks;
        }

        function failed(error) {
        }
    }
    $scope.getPumpTransactionList = function () {
        $scope.customerIds = [];
        $scope.pumpIds = [];
        $scope.gradeIds = [];
        $scope.hoseIds = [];
        $scope.tankIds = [];
        $scope.vehicleIds = [];
        $scope.attendantIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        var multiselectData = [{'selected': $scope.test.selectedPumps, 'ids': $scope.pumpIds},
            {'selected': $scope.test.selectedCustomers, 'ids': $scope.customerIds},
            {'selected': $scope.test.selectedAttendants, 'ids': $scope.attendantIds},
            {'selected': $scope.test.selectedHoses, 'ids': $scope.hoseIds},
            {'selected': $scope.test.selectedVehicles, 'ids': $scope.vehicleIds},
            {'selected': $scope.test.selectedGrades, 'ids': $scope.gradeIds},
            {'selected': $scope.test.selectedTanks, 'ids': $scope.tankIds},
        ];

        angular.forEach(multiselectData, function (value, key) {
            $rootScope.getMultiSelectIds(value.selected, value.ids);
        });

        var filterdata = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            pumps: $scope.pumpIds.toString(),
            attendants: $scope.attendantIds.toString(),
            hoses: $scope.hoseIds.toString(),
            vehicles: $scope.vehicleIds.toString(),
            grades: $scope.gradeIds.toString(),
            tanks: $scope.tankIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        CRUD.postWithData(pumptransactiondurl, $.param(filterdata)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Pump Transaction", res.data.message);
        }

        function failed(error) {
        }
    }
    /**************************************  Dispensed Grade **************************************************/
    $scope.changeSiteDispenseGradeData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.gradesData = [];
        CRUD.postWithData(dispensegradelisturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.gradesData = response.data.data.grades;
        }

        function failed(error) {
        }
    }
    $scope.getDispenseGradeList = function (pageNumber) {
        $scope.gradeIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        $rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);

        var filterdata = {
            site_id: $scope.selectedSite.id,
            grades: $scope.gradeIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        $('#dispense_grade').DataTable().destroy();
        $(document).find('#dispense_grade').DataTable({
            "processing": true,
            "serverSide": true,
            "searchable": true,
            "ajax": {
                "url": $rootScope.API_BASE_URL + dispensegradedurl + '?token='+$localStorage.token,
                "type": "POST",
                "data": filterdata,
                complete: function() {
                    $('.cloneTable').html($('.clonableTable').html());
                }
            },
            "columns": [
                {data: 'grade_name', name: 'grade_name'},
                {data: 'total_litres', name: 'total_litres'},
                {data: 'total_cost', name: 'total_cost'},
                {data: 'trans_count', name: 'trans_count'}
            ]
        });
        // if(! $.isEmptyObject($scope.libraryTemp)){
        //     CRUD.postWithDataPage(dispensegradedurl, '?search='+$scope.searchText, $.param(filterdata)).then(success).catch(failed);
        // }else{
        //     CRUD.postWithDataPage(dispensegradedurl, '?page='+pageNumber, $.param(filterdata)).then(success).catch(failed);
        // }
        // function success(res) {
        //     $scope.data = res.data.data;
        //     $scope.totalItems = res.data.total;
        //     // toaster.pop('success', "Dispensed Grade", res.data.message);
        // }
        //
        // function failed(error) {
        // }
    }
    /**************************************  Dispensed Time **************************************************/
    $scope.changeSiteDispenseTimeData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.gradesData = [];
        CRUD.postWithData(dispensetimelisturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.gradesData = response.data.data.grades;
        }

        function failed(error) {
        }
    }
    $scope.getDispenseTimeList = function () {
        $scope.gradeIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        $rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);
        if (!$scope.dateRangeStart) {
            toaster.pop('error', 'Dispensed Time', 'Please select Start Date.');
            return;
        }
        if (!$scope.dateRangeEnd) {
            toaster.pop('error', 'Dispensed Time', 'Please select End Date.');
            return;
        }
        var filterdata = {
            site_id: $scope.selectedSite.id,
            grades: $scope.gradeIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        CRUD.postWithData(dispensetimeurl, $.param(filterdata)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Dispensed Time", res.data.message);
        }

        function failed(error) {
        }
    }
    /**************************************  Dispensed Hose **************************************************/
    $scope.changeSiteDispenseHoseData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.gradesData = [];
        $scope.tanksData = [];
        $scope.hosesData = [];
        CRUD.postWithData(dispensehoselisturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.gradesData = response.data.data.grades;
            $scope.tanksData = response.data.data.tanks;
            $scope.hosesData = response.data.data.hoses;
        }

        function failed(error) {
        }
    }
    $scope.getDispenseHoseList = function () {
        $scope.gradeIds = [];
        $scope.tankIds = [];
        $scope.hoseIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        $rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);
        $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
        $rootScope.getMultiSelectIds($scope.test.selectedHoses, $scope.hoseIds);

        var filterdata = {
            site_id: $scope.selectedSite.id,
            grades: $scope.gradeIds.toString(),
            tanks: $scope.tankIds.toString(),
            hoses: $scope.hoseIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        $(document).find('#dispensed_hose').DataTable().destroy();
        $(document).find('#dispensed_hose').DataTable({
                "processing": true,
                "serverSide": true,
                "searchable": true,
                "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                "ajax": {
                    "url": $rootScope.API_BASE_URL + dispensehoseurl + '?token=' + $localStorage.token,
                    "type": "POST",
                    "data": filterdata,
                    complete: function() {
                        $(document).find('.cloneTable').html($('.clonableTable').html());
                    }
                },
                "columns": [
                    {data: 'hose_name', name: 'hose_name'},
                    {data: 'tank_name', name: 'tank_name'},
                    {data: 'trans_count', name: 'trans_count'},
                    {data: null, name: 'total_litres',render:function (data) {
                        return $filter('number')(data.total_litres, 2);
                    }},
                    {data: null, name: 'total_cost',render:function (data) {
                        return $filter('number')(data.total_cost, 2);
                    }}
                ]
            });

        // CRUD.postWithData(dispensehoseurl, $.param(filterdata)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Dispensed Hose", res.data.message);
        // }
        //
        // function failed(error) {
        // }
    }
    /**************************************  Dispensed Tank **************************************************/
    $scope.changeSiteDispenseTankData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        CRUD.postWithData(dispensetanklisturl, $.param(filterdata)).then(success).catch(failed);
        $scope.gradesData = [];
        $scope.tanksData = [];
        $scope.hosesData = [];
        function success(response) {
            $scope.gradesData = response.data.data.grades;
            $scope.tanksData = response.data.data.tanks;
            $scope.hosesData = response.data.data.hoses;
        }

        function failed(error) {
        }
    }
    $scope.getDispenseTankList = function () {
        $scope.gradeIds = [];
        $scope.tankIds = [];
        $scope.hoseIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        $rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);
        $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
        $rootScope.getMultiSelectIds($scope.test.selectedHoses, $scope.hoseIds);

        var filterdata = {
            site_id: $scope.selectedSite.id,
            grades: $scope.gradeIds.toString(),
            tanks: $scope.tankIds.toString(),
            hoses: $scope.hoseIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        $(document).find('#dispensed_tank').DataTable().destroy();
        $(document).find('#dispensed_tank').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "searchable": true,
                    "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                    "ajax": {
                        "url": $rootScope.API_BASE_URL + dispensetankurl + '?token=' + $localStorage.token,
                        "type": "POST",
                        "data": filterdata,
                        complete: function() {
                            $(document).find('.cloneTable').html($('.clonableTable').html());
                        }
                    },
                    "columns": [
                        {data: 'tank_name', name: 'tank_name'},
                        {data: 'grade_name', name: 'grade_name'},
                        {data: 'trans_count', name: 'trans_count'},
                        {data: null, name: 'total_litres',render:function (data) {
                            return $filter('number')(data.total_litres, 2);
                        }},
                        {data: null, name: 'total_cost',render:function (data) {
                            return $filter('number')(data.total_cost, 2);
                        }}
                    ]
                });

        // CRUD.postWithData(dispensetankurl, $.param(filterdata)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Dispensed Tank", res.data.message);
        // }
        //
        // function failed(error) {
        // }
    }
    /**************************************  Dispensed Attendant **************************************************/
    $scope.changeSiteDispenseAttendantData = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.gradesData = [];
        $scope.attendantsData = [];
        CRUD.postWithData(dispenseattendantlisturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.gradesData = response.data.data.grades;
            $scope.attendantsData = response.data.data.attendants;
        }

        function failed(error) {
        }
    }
    $scope.getDispenseAttendantList = function () {
        $scope.gradeIds = [];
        $scope.attendantIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        $rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);
        $rootScope.getMultiSelectIds($scope.test.selectedAttendants, $scope.attendantIds);

        var filterdata = {
            site_id: $scope.selectedSite.id,
            attendants: $scope.attendantIds.toString(),
            grades: $scope.gradeIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        $(document).find('#dispense_attendant').DataTable().destroy();
        $(document).find('#dispense_attendant').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "searchable": true,
                    "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                    "ajax": {
                        "url": $rootScope.API_BASE_URL + dispenseattendanturl + '?token=' + $localStorage.token,
                        "type": "POST",
                        "data": filterdata,
                        complete: function() {
                            $(document).find('.cloneTable').html($('.clonableTable').html());
                        }
                    },
                    "columns": [
                        {data: 'attendant_name', name: 'attendant_name'},
                        {data: 'trans_count', name: 'trans_count'},
                        {data: null, name: 'total_litres',render:function (data) {
                            return $filter('number')(data.total_litres, 2);
                        }},
                        {data: null, name: 'total_cost',render:function (data) {
                            return $filter('number')(data.total_cost, 2);
                        }}
                    ]
                });

        // CRUD.postWithData(dispenseattendanturl, $.param(filterdata)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Dispensed Attendant", res.data.message);
        // }
        //
        // function failed(error) {
        // }
    }
    $scope.format = 'dd-MMMM-yyyy';
       $scope.dateTimeNow = function () {
           // $scope.end_date = new Date();
           // $scope.start_date = new Date();
       };
       $scope.dateTimeNow();

        $scope.toggleMinDate = function() {
          $scope.minDate = $scope.minDate ? null : new Date();
        };

        $scope.maxDate = new Date('2014-06-22');
        $scope.toggleMinDate();

        $scope.dateOptions = {
          showWeeks: false
        };

        // Disable weekend selection
        // $scope.disabled = function(calendarDate, mode) {
        //   return mode === 'day' && ( calendarDate.getDay() === 0 || calendarDate.getDay() === 6 );
        // };

        $scope.open = function($event,opened) {
          $event.preventDefault();
          $event.stopPropagation();
          $scope.dateOpened = true;
          console.log('opened');
        };
        $scope.open2 = function($event,opened) {
          $event.preventDefault();
          $event.stopPropagation();
          $scope.dateOpened2 = true;
        };

        $scope.dateOpened = false;
        $scope.hourStep = 1;
        $scope.minuteStep = 1;

        $scope.timeOptions = {
          hourStep: [1, 2, 3],
          minuteStep: [1, 5, 10, 15, 25, 30]
        };

        $scope.showMeridian = true;
       $scope.timeToggleMode = function() {
         $scope.showMeridian = !$scope.showMeridian;
       };

    $scope.searchDB = function(url){
        if($scope.searchText.length >= 3){
            if($.isEmptyObject($scope.libraryTemp)){
                $scope.libraryTemp = $scope.data;
                $scope.totalItemsTemp = $scope.totalItems;
                $scope.data = {};
            }
            if (url == 'd_grade'){
                $scope.getDispenseGradeList(1);
            }else {
                getResultsPage(1, url);
            }
        }else{
            if(! $.isEmptyObject($scope.libraryTemp)){
                $scope.data = $scope.libraryTemp ;
                $scope.totalItems = $scope.totalItemsTemp;
                $scope.libraryTemp = {};
            }
        }
    }
});