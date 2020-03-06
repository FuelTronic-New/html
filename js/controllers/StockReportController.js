app.controller('StockReportController', function ($scope, $rootScope, $http, toaster, CRUD, $state, $localStorage,$filter) {

    var listurl = 'siteslist';
    var sitestockadjustmentsurl = 'stockadjustmentreport';
    var sitepumpurl = 'sitepumps';
    var stocklevelreporturl = 'stocklevelreport';
    $scope.pumpsData = [];
    $scope.gradesData = [];
    $scope.test = {};

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
                if ($state.is('app.stock_level_report')) {
                    $scope.changeSiteStockLevelData();
                }
            }
        }

        function failed(error) {
            //toaster.pop('error','',error.data.message);
        }
    };

    $scope.changeSiteStockAdjustmentData = function () {
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
    }
    $scope.changeSiteStockLevelData = function () {
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.test = {};
        var filterdata = {
            site_id: $scope.selectedSite.id
        };
        CRUD.postWithData(sitepumpurl, $.param(filterdata)).then(success).catch(failed);
        function success(response) {
            $scope.pumpsData = response.data.data.pumps;
            $scope.gradesData = response.data.data.grades;
        }

        function failed(error) {
        }
    }

    $scope.getStockAdjustmentList = function () {
        var filterData = {
            site_id: $scope.selectedSite.id,
        };

        $(document).find('#stock_adjustment').DataTable().destroy();
        $(document).find('#stock_adjustment').DataTable({
                "processing": true,
                "serverSide": true,
                "searchable": true,
                "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                "ajax": {
                    "url": $rootScope.API_BASE_URL + sitestockadjustmentsurl + '?token=' + $localStorage.token,
                    "type": "POST",
                    "data": filterData,
                    complete: function() {
                        $(document).find('.cloneTable').html($('.clonableTable').html());
                    }
                },
                "columns": [
                    {data:'tank.name',name:'tank.name'},
                    {data: null, name: 'litres',render:function (data) {
                        return $filter('number')(data.litres, 2);
                    }},
                    {data: 'created_at', name: 'created_at'},
                ]
            });

        // CRUD.postWithData(sitestockadjustmentsurl, $.param(filterData)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data.fuel_adjustments;
        //     $scope.displayCollection = res.data.data.fuel_adjustments;
        //     toaster.pop('success', "Fuel Adjustment", res.data.message);
        // }
        //
        // function failed(error) {
        //     //toaster.pop('error','',error.data.message);
        // }
    }

    $scope.getStockLevelReportList = function () {
        $scope.gradeIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedGrade, $scope.gradeIds);
        var filterData = {
            site_id: $scope.selectedSite.id,
            grade_ids: $scope.gradeIds.toString()
        };

        $(document).find('#stock_level_report').DataTable().destroy();
        $(document).find('#stock_level_report').DataTable({
                "processing": true,
                "serverSide": true,
                "searchable": true,
                "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

                "ajax": {
                    "url": $rootScope.API_BASE_URL + stocklevelreporturl + '?token=' + $localStorage.token,
                    "type": "POST",
                    "data": filterData,
                    complete: function() {
                        $(document).find('.cloneTable').html($('.clonableTable').html());
                    }
                },
                "columns": [
                    {data: 'grade_name', name: 'grade_name'},
                    {data: null, name: 'price',render:function (data) {
                        return $filter('number')(data.price, 2);
                    }},
                    {data: null, name: 'current_level_stock',render:function (data) {
                        return $filter('number')(data.current_level_stock, 2);
                    }},
                    {data: null, name: 'last_dip_reading',render:function (data) {
                        return $filter('number')(data.last_dip_reading, 2);
                    }},
                    {data: null, name: 'cur_atg_level',render:function (data) {
                        return $filter('number')(data.cur_atg_level, 2);
                    }}
                ]
            });

        // CRUD.postWithData(stocklevelreporturl, $.param(filterData)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Fuel Level", res.data.message);
        // }
        //
        // function failed(error) {
        //     //toaster.pop('error','',error.data.message);
        // }
    }
});