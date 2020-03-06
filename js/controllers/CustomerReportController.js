app.controller('CustomerReportController', function ($scope, $rootScope, $http, CRUD, toaster,$filter, $localStorage) {

    var listurl = 'customervehicles';
    var customervehiclesreporturl = 'customervehiclesreport';
    var getVehicleTransactionListurl = 'customervehicletransactionsreport';
    var customerlistreporturl = 'customerlistreport';
    var customerstatementsreporturl = 'customerstatementsreport';
    var vehicletransactionsreporturl = 'vehicletransactionsreport';

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;
    $scope.person = {};
    $scope.vehiclesData = [];
    $scope.customersData = [];
    $scope.test = {};

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
                $scope.customersData = $scope.selectedSite.customers;
                $scope.vehiclesData = $scope.selectedSite.vehicles;
            }
        }

        function failed(error) {
            //toaster.pop('error','',error.data.message);
        }
    };

    $scope.changeSite = function () {
        //$scope.selectedCustomers.selected = undefined;
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.customersData = $scope.selectedSite.customers;
        $scope.vehiclesData = $scope.selectedSite.vehicles;
    }

    $scope.getVehicleList = function () {
        $scope.customerIds = [];
        $scope.vehicleIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);
        $rootScope.getMultiSelectIds($scope.test.selectedVehicles, $scope.vehicleIds);

        var filterData = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            vehicles: $scope.vehicleIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        //console.log(filterData);
        CRUD.postWithData(customervehiclesreporturl, $.param(filterData)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Vehicles", res.data.message);
        }

        function failed(error) {
        }
    }

    $scope.getVehicleTransactionList = function () {
        $scope.customerIds = [];
        $scope.vehicleIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);
        $rootScope.getMultiSelectIds($scope.test.selectedVehicles, $scope.vehicleIds);
        var filterData = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            vehicles: $scope.vehicleIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        //console.log(filterData);
        CRUD.postWithData(getVehicleTransactionListurl, $.param(filterData)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Vehicle Transactions", res.data.message);
        }

        function failed(error) {
            $rootScope.loading = false;
        }
    }

    $scope.getCustomerList = function () {
        $scope.customerIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);

        var filterData = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        $(document).find('#customer_list_report').DataTable().destroy();
        $(document).find('#customer_list_report').DataTable({
              "processing": true,
              "serverSide": true,
              "searchable": true,
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

              "ajax": {
                  "url": $rootScope.API_BASE_URL + customerlistreporturl + '?token=' + $localStorage.token,
                  "type": "POST",
                  "data": filterData,
                  complete: function() {
                      $(document).find('.cloneTable').html($('.clonableTable').html());
                  }
              },
            "columns": [
                {data: 'name', name: 'name'},
                {data: 'email_address', name: 'email_address'},
                {data: 'mobile', name: 'mobile'},
                {data: 'fax', name: 'fax'}
            ]
        });

        // CRUD.postWithData(customerlistreporturl, $.param(filterData)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Customer", res.data.message);
        // }
        //
        // function failed(error) {
        //     //toaster.pop('error', '', error.data.message);
        // }
    }

    $scope.getCustomerStatementsList = function () {
        $scope.customerIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);

        var filterData = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        //console.log(filterData);
        CRUD.postWithData(customerstatementsreporturl, $.param(filterData)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Customer Statements", res.data.message);
        }

        function failed(error) {
            //toaster.pop('error', '', error.data.message);
        }
    }

    $scope.getVehicleTransactionSummary = function () {
        $scope.customerIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);

        var filterData = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        $(document).find('#vehicle_trans_summery').DataTable().destroy();
        $(document).find('#vehicle_trans_summery').DataTable({
              "processing": true,
              "serverSide": true,
              "searchable": true,
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

              "ajax": {
                  "url": $rootScope.API_BASE_URL + vehicletransactionsreporturl + '?token=' + $localStorage.token,
                  "type": "POST",
                  "data": filterData,
                  complete: function() {
                      $(document).find('.cloneTable').html($('.clonableTable').html());
                  }
              },
              "columns": [
                  {data: 'customer_name', name: 'customer_name'},
                  {data: 'vehicle_name', name: 'vehicle_name'},
                  {data: 'trans_count', name: 'trans_count'},
                  {data: null, name: 'total_litres',render:function (data) {
                      return $filter('number')(data.total_litres, 2);
                  }},
                  {data: null, name: 'total_cost',render:function (data) {
                      return $filter('number')(data.total_cost, 2);
                  }}
              ]
          });

        // CRUD.postWithData(vehicletransactionsreporturl, $.param(filterData)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Vehicle Transaction summary", res.data.message);
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
});