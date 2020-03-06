app.controller('SupplierReportController', function ($scope, $rootScope, $http, toaster, CRUD, $localStorage) {

    var listurl = 'supplierslist';
    var supplierslistreporturl = 'supplierslistreport';
    var supplierspurchaseslisturl = 'supplierspurchaseslist';

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;
    $scope.person = {};
    $scope.suppliersData = [];
    $scope.gradesData = [];
    $scope.test = {};

    $scope.currentDate = new Date();
    $scope.enable = function () {
        $scope.disabled = false;
    };

    $scope.disable = function () {
        $scope.disabled = true;
    };

    $scope.enableSearch = function () {
        $scope.searchEnabled = true;
    }

    $scope.disableSearch = function () {
        $scope.searchEnabled = false;
    }

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
                $scope.suppliersData = $scope.selectedSite.suppliers;
            }
        }

        function failed(error) {
            //toaster.pop('error','',error.data.message);
        }
    };

    $scope.supplierspurchases = function () {
        CRUD.getAll(supplierspurchaseslisturl).then(success).catch(failed);
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
                $scope.suppliersData = $scope.selectedSite.suppliers;
                $scope.gradesData = $scope.selectedSite.grades;
            }
        }

        function failed(error) {
            //toaster.pop('error','',error.data.message);
        }
    };

    $scope.changeSite = function () {
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.suppliersData = $scope.selectedSite.suppliers;
    }

    $scope.getSupplierList = function () {
        $scope.supplierIds = [];
        $scope.gradeIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedSupplier, $scope.supplierIds);
        $rootScope.getMultiSelectIds($scope.test.selectedGrade, $scope.gradeIds);

        var filterData = {
            site_id: $scope.selectedSite.id,
            suppliers: $scope.supplierIds.toString(),
            grades: $scope.gradeIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };

        $(document).find('#supplier_list_report').DataTable().destroy();
        $(document).find('#supplier_list_report').DataTable({
              "processing": true,
              "serverSide": true,
              "searchable": true,
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

              "ajax": {
                  "url": $rootScope.API_BASE_URL + supplierslistreporturl + '?token=' + $localStorage.token,
                  "type": "POST",
                  "data": filterData,
                  complete: function() {
                      $(document).find('.cloneTable').html($('.clonableTable').html());
                  }
              },
              "columns": [
                  {data: 'accountNumber', name: 'accountNumber'},
                  {data: 'name', name: 'name'},
                  {data: 'mobile', name: 'mobile'},
                  {data: 'phone', name: 'phone'}
                  ]
          });


        /*CRUD.postWithData(supplierslistreporturl, $.param(filterData)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Supplier", res.data.message);
        }

        function failed(error) {
            //toaster.pop('error', '', error.data.message);
        }*/
    }
    $scope.getSupplierPurchase = function () {
        $scope.supplierIds = [];
        $scope.gradeIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedSupplier, $scope.supplierIds);
        $rootScope.getMultiSelectIds($scope.test.selectedGrade, $scope.gradeIds);

        var filterData = {
            site_id: $scope.selectedSite.id,
            suppliers: $scope.supplierIds.toString(),
            grades: $scope.gradeIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        CRUD.postWithData(supplierslistreporturl, $.param(filterData)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Supplier", res.data.message);
        }

        function failed(error) {
            //toaster.pop('error', '', error.data.message);
        }
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