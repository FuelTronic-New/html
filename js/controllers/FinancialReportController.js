app.controller('FinancialReportController', function ($scope, $rootScope, $http, CRUD, toaster,$state, $filter, $localStorage) {

    var listurl = 'siteslist';
    var sitecustomersfinancialturl = 'sitecustomersfinancial';
    var siteuppliersfinancialturl = 'sitesuppliersfinancial';
    var sitesalesfinancialurl = 'sitesalesfinancial';
    var custageanalysisturl = 'custageanalysis';
    var supplierageanalysisturl = 'supplierageanalysis';
    var salesturl = 'sales';
    var voidsurl = 'voids';
    var tankpumpsfromhoseurl = 'tankpumpsfromhose';
    var gradesfromtanksurl = 'gradesfromtanks';
    var transactionurl = 'financial-transaction';
    var tanksfromsite = 'tanksfromsite';

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;
    $scope.person = {};
    $scope.customersData = [];
    $scope.suppliersData = [];
    $scope.pumpsData = [];
    $scope.attendantsData = [];
    $scope.hosesData = [];
    $scope.gradesData = [];
    $scope.vehiclesData = [];
    $scope.tanksData = [];
    $scope.locationsData = [];
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
                //$scope.changeSiteData();
                if($state.is('app.supplier_age_analysis')){
                    $scope.changeSiteSupplier();
                }else if($state.is('app.customer_age_analysis')){
                    $scope.changeSiteCustomer();
                }else if($state.is('app.sales') || $state.is('app.refunds') || $state.is('app.voids') || $state.is('app.financial_transaction')){
                    $scope.changeSiteData();
                }else if($state.is('app.sars_report')){
                    $scope.getSiteTanksData();
                }
            }
        }

        function failed(error) {
            //toaster.pop('error','',error.data.message);
        }
    };

    /********************************** site change data for sales,voids,refunds,transactions ***********************/
    $scope.changeSiteData = function () {
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.test = {};
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.customersData = [];
        $scope.attendantsData = [];
        $scope.hosesData = [];
        $scope.vehiclesData = [];
        CRUD.postWithData(sitesalesfinancialurl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.customersData = response.data.data.customers;
            $scope.attendantsData = response.data.data.attendants;
            $scope.hosesData = response.data.data.hoses;
            $scope.vehiclesData = response.data.data.vehicles;
        }

        function failed(error) {
        }
    }
    $scope.getSiteTanksData = function () {
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.test = {};
        $scope.test.selectedTanks = {};
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.tanksData = [];
        CRUD.postWithData(tanksfromsite, $.param(filterdata)).then(function (response) {
            $scope.tanksData = response.data.data;
            $scope.locationsData = response.data.locations;
        }).catch(function (error) {
            
        });
    }
    
    $scope.tankChange = function () {
        $scope.tankIds = [];
        if ($scope.selectedSite) {
            $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
            if ($scope.tankIds != '') {
                var filterdata = {
                    site_id: $scope.selectedSite.id,
                    tanks: $scope.tankIds.toString()
                };
                $scope.gradesData = [];
                CRUD.postWithData(gradesfromtanksurl, $.param(filterdata)).then(success).catch(failed);
                function success(response) {
                    $scope.gradesData = response.data.data.grades;
                }

                function failed(error) {
                }
            } else {
                $scope.gradesData = [];
                $scope.test.selectedGrades = {};
            }
        }
    }
    $scope.hoseChange = function () {

        $scope.hoseIds = [];
        if ($scope.selectedSite) {
            $scope.tanksData = [];
            $scope.pumpsData = [];
            $rootScope.getMultiSelectIds($scope.test.selectedHoses, $scope.hoseIds);
            if ($scope.hoseIds != '') {
                var filterdata = {
                    site_id: $scope.selectedSite.id,
                    hoses: $scope.hoseIds.toString()
                };
                CRUD.postWithData(tankpumpsfromhoseurl, $.param(filterdata)).then(success).catch(failed);
                function success(response) {
                    $scope.test.selectedTanks = {};
                    $scope.test.selectedPumps = {};
                    $scope.tanksData = response.data.data.tanks;
                    $scope.pumpsData = response.data.data.pumps;
                }

                function failed(error) {
                }
            } else {
                $scope.gradesData = [];
                $scope.test.selectedTanks = {};
                $scope.test.selectedGrades = {};
                $scope.test.selectedPumps = {};
            }
        }
    }
    /*************************************  Customer Age Analysis  ***************************************************/
    $scope.changeSiteCustomer = function () {
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.test = {};
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.customersData = [];
        CRUD.postWithData(sitecustomersfinancialturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.customersData = response.data.data.customers;
        }

        function failed(error) {
        }
    }

    $scope.getCustomerAgeList = function () {
        $scope.customerIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];
        $rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);

        var filterdata = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        CRUD.postWithData(custageanalysisturl, $.param(filterdata)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Customer Age Analysis", res.data.message);
        }

        function failed(error) {
        }
    }

    /*************************************  Supplier Age Analysis  ***************************************************/
    $scope.changeSiteSupplier = function () {
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.test = {};
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        $scope.suppliersData = [];
        CRUD.postWithData(siteuppliersfinancialturl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.suppliersData = response.data.data.suppliers;
        }

        function failed(error) {
        }
    }
    $scope.getSupplierAgeList = function () {
        $scope.supplierIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];
        $rootScope.getMultiSelectIds($scope.test.selectedSuppliers, $scope.supplierIds);

        var filterdata = {
            site_id: $scope.selectedSite.id,
            suppliers: $scope.supplierIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        $(document).find('#supplier_age_analysis').DataTable().destroy();
          $(document).find('#supplier_age_analysis').DataTable({
                  "processing": true,
                  "serverSide": true,
                  "searchable": true,
                  "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                  "ajax": {
                      "url": $rootScope.API_BASE_URL + supplierageanalysisturl + '?token=' + $localStorage.token,
                      "type": "POST",
                      "data": filterdata,
                      complete: function() {
                          $(document).find('.cloneTable').html($('.clonableTable').html());
                      }
                  },
                  "columns": [
                      {data: 'name', name: 'name'},
                      {data: null, name: 'amount',render:function (data) {
                          if (data.payments.length > 0){
                              return $filter('number')(data.payments[0].amount, 2);
                          }else{
                              return '';
                          }
                      }}
                  ]
              });

        // CRUD.postWithData(supplierageanalysisturl, $.param(filterdata)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Supplier Age Analysis", res.data.message);
        // }
        //
        // function failed(error) {
        // }
    }

    /***********************************************  Sales  *******************************************************/

    $scope.getSalesList = function () {
        $scope.customerIds = [];
        $scope.pumpIds = [];
        $scope.gradeIds = [];
        $scope.hoseIds = [];
        $scope.tankIds = [];
        $scope.vehicleIds = [];
        $scope.attendantIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        //$rootScope.getMultiSelectIds($scope.test.selectedPumps, $scope.pumpIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedAttendants, $scope.attendantIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedHoses, $scope.hoseIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedVehicles, $scope.vehicleIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);

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


        //if ($scope.customerIds == '') {
        //    toaster.pop('error', 'Customer List', 'Please select any Customer.');
        //    return;
        //}
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

      $(document).find('#sales_report').DataTable().destroy();
      $(document).find('#sales_report').DataTable({
              "processing": true,
              "serverSide": true,
              "searchable": true,
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

              "ajax": {
                  "url": $rootScope.API_BASE_URL + salesturl + '?token=' + $localStorage.token,
                  "type": "POST",
                  "data": filterdata,
                  complete: function() {
                      $(document).find('.cloneTable').html($('.clonableTable').html());
                  }
              },
              "columns": [
                  {data: 'created_at', name: 'created_at'},
                  {data: null, name: 'customer.name',render:function (data) {
                      if (data.customer != null){
                          return (data.customer.name != null) ? data.customer.name : '';
                  }else{return '';}
                  }},
                  {data: null, name: 'attendant.name',render:function (data) {
                      if (data.attendant != null){
                          return (data.attendant.name != null) ? data.attendant.name : '';
                  }else{return '';}
                  }},
                  {data: null, name: 'total_cost',render:function (data) {
                      return $filter('number')(data.total_cost, 2);
                  }},
                  {data: null, name: 'litres',render:function (data) {
                      return $filter('number')(data.litres, 2);
                  }},
                  {data: null, name: 'hose.pump.name',render:function (data) {
                    if (data.hose != null){
                        if (data.hose.pump != null){
                            return (data.hose.pump.name != null) ? data.hose.pump.name : '';
                        }else{return ''; }
                    }else{return '';}
                  }}
              ]
          });
        // CRUD.postWithData(salesturl, $.param(filterdata)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection=[];
        //     $scope.displayCollection=[];
        //     angular.forEach(res.data.data,function(value,key){
        //         if(value.hose){
        //             $scope.rowCollection.push(value);
        //             $scope.displayCollection.push(value);
        //         }
        //     });
        //     toaster.pop('success', "Financial", res.data.message);
        //         //$scope.rowCollection = res.data.data;
        //         //$scope.displayCollection = res.data.data;
        // }
        //
        // function failed(error) {
        // }
    }

    /***********************************************  Voids  *******************************************************/

    $scope.getVoidsList = function () {
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

        $(document).find('#voids_report').DataTable().destroy();
        $(document).find('#voids_report').DataTable({
              "processing": true,
              "serverSide": true,
              "searchable": true,
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

              "ajax": {
                  "url": $rootScope.API_BASE_URL + voidsurl + '?token=' + $localStorage.token,
                  "type": "POST",
                  "data": filterdata,
                  complete: function() {
                      $(document).find('.cloneTable').html($('.clonableTable').html());
                  }
              },
              "columns": [
                  {data: 'created_at', name: 'created_at'},
                  {data: null, name: 'customer.name',render:function (data) {
                      if (data.customer != null){
                          return (data.customer.name != null) ? data.customer.name : '';
                      }else{return '';}
                  }},
                  {data: null, name: 'attendant.name',render:function (data) {
                      if (data.attendant != null){
                          return (data.attendant.name != null) ? data.attendant.name : '';
                      }else{return '';}
                  }},
                  {data: null, name: 'total_cost',render:function (data) {
                      return $filter('number')(data.total_cost, 2);
                  }},
                  {data: null, name: 'litres',render:function (data) {
                      return $filter('number')(data.litres, 2);
                  }},
                  {data: null, name: 'hose.pump.name',render:function (data) {
                      if (data.hose != null){
                          if (data.hose.pump != null){
                              return (data.hose.pump.name != null) ? data.hose.pump.name : '';
                          }else{return ''; }
                      }else{return '';}
                  }}
              ]
          });
        // CRUD.postWithData(voidsurl, $.param(filterdata)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection=[];
        //     $scope.displayCollection=[];
        //     angular.forEach(res.data.data,function(value,key){
        //         if(value.hose){
        //             $scope.rowCollection.push(value);
        //             $scope.displayCollection.push(value);
        //         }
        //     });
        //     toaster.pop('success', "Financial", res.data.message);
        //         //$scope.rowCollection = res.data.data;
        //         //$scope.displayCollection = res.data.data;
        // }
        //
        // function failed(error) {
        // }

    }

    /***********************************************  Sales  *******************************************************/

    $scope.getRefundsList = function () {

    }

    /********************************************  Transaction ******************************************************/

    $scope.getTransactionsList = function () {

        $scope.customerIds = [];
        $scope.pumpIds = [];
        $scope.gradeIds = [];
        $scope.hoseIds = [];
        $scope.tankIds = [];
        $scope.vehicleIds = [];
        $scope.attendantIds = [];
        $scope.rowCollection = [];
        $scope.displayCollection = [];

        //$rootScope.getMultiSelectIds($scope.test.selectedPumps, $scope.pumpIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedAttendants, $scope.attendantIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedHoses, $scope.hoseIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedVehicles, $scope.vehicleIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedGrades, $scope.gradeIds);
        //$rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);

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


        //if ($scope.customerIds == '') {
        //    toaster.pop('error', 'Customer List', 'Please select any Customer.');
        //    return;
        //}
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
        CRUD.postWithData(transactionurl, $.param(filterdata)).then(success).catch(failed);
        function success(res) {
            var filterData = $filter('orderBy')(res.data.data, 'transaction_date');
            console.log(filterData);
            var tempTotal = 0;
            var tempLitreTotal = 0;
            angular.forEach(filterData, function (trans, transKey) {
                tempTotal += parseFloat(trans.amount.replace(/,/g, ''));
                tempLitreTotal += parseFloat(trans.liters.replace(/,/g, ''));
                $scope.rowCollection.push({
                    transaction_date: trans.transaction_date,
                    type: trans.type,
                    supplier: trans.supplier,
                    tank_name: trans.tank_name,
                    attendant_name: trans.attendant_name,
                    amount: trans.amount,
                    amount_balance: tempTotal.toFixed(2),
                    liters: trans.liters,
                    liters_balance: tempLitreTotal.toFixed(2),
                    pump_name: trans.pump_name,
                });
            });
            $scope.displayCollection=$scope.rowCollection;
            toaster.pop('success', "Financial", res.data.message);
        }

        function failed(error) {
        }
    }
    /********************************************  SARS Report ******************************************************/

    $scope.getSARS = function () {
        $scope.rowCollection = [];
        $scope.displayCollection = [];
        $scope.tankIds = [];
        $scope.locationIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedTanks, $scope.tankIds);
        $rootScope.getMultiSelectIds($scope.test.selectedLocations, $scope.locationIds);
        if (!$scope.dateRangeStart){
            toaster.error('SARS', 'please select start date.');
            return;
        }
        if (!$scope.dateRangeEnd){
            toaster.error('SARS', 'please select end date.');
            return;
        }
        var filterdata = {
            site_id: $scope.selectedSite.id,
            tank_ids: $scope.tankIds.toString(),
            location_ids: $scope.locationIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        CRUD.postWithData('sars', $.param(filterdata)).then(function (response) {
            $scope.tanks = response.data.data;
            angular.forEach($scope.tanks, function (val, key) {
                $scope.tanks[key].total_eligible_litres = 0;
                angular.forEach(val.logbook, function (logVal, logKey) {
                    $scope.tanks[key].total_eligible_litres += parseFloat(logVal.E5);
                });
            });
            // $scope.displayCollection=response.data.data;
            toaster.pop('success', "SARS", response.data.message);
        }).catch(function (error) {
            toaster.pop('error', "SARS", error.data.message);
        });
    }

    $scope.format = 'dd-MMMM-yyyy';
    $scope.dateTimeNow = function() {
       // $scope.dateRangeStart = new Date();
       // $scope.dateRangeEnd = new Date(moment().add(1, 'days'));
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