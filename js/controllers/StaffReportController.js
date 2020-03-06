app.controller('StaffReportController', function ($scope, $rootScope, $http, CRUD, toaster, $localStorage) {

    var listurl = 'attendantslist';
    var attendantslistreportturl = 'attendantslistreport';

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;
    $scope.person = {};
    $scope.vehiclesData = [];
    $scope.attendantsData = [];
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
                $scope.attendantsData = $scope.selectedSite.attendants;
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
        $scope.attendantsData = $scope.selectedSite.attendants;
    }

    $scope.getCustomerList = function () {
        $scope.attendantIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedAttendants, $scope.attendantIds);
        var filterData = {
            site_id: $scope.selectedSite.id,
            attendants: $scope.attendantIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
            // date_range: $scope.date_range,
        };
        $(document).find('#attendant_list_report').DataTable().destroy();
        $(document).find('#attendant_list_report').DataTable({
              "processing": true,
              "serverSide": true,
              "searchable": true,
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],

              "ajax": {
                  "url": $rootScope.API_BASE_URL + attendantslistreportturl + '?token=' + $localStorage.token,
                  "type": "POST",
                  "data": filterData,
                  complete: function() {
                      $(document).find('.cloneTable').html($('.clonableTable').html());
                  }
              },
              "columns": [
                  {data: 'id', name: 'id'},
                  {data: 'name', name: 'name'},
                  {data: 'surname', name: 'surname'},
                  {data: null, name: 'tag.id',render:function (data) {
                      if (data.tag != null){
                          return (data.tag.id != null) ? data.tag.id :'';
                      }else{ return '';}
                  }},
                  {data: null, name: 'tag.name',render:function (data) {
                      if (data.tag != null){
                          return (data.tag.name != null ) ? data.tag.name :'';
                      }else{ return '';}
                  }},
                  {data: 'cell', name: 'cell'},
              ]
          });



        // CRUD.postWithData(attendantslistreportturl, $.param(filterData)).then(success).catch(failed);
        // function success(res) {
        //     $scope.rowCollection = res.data.data;
        //     $scope.displayCollection = res.data.data;
        //     toaster.pop('success', "Attendants", res.data.message);
        // }
        //
        // function failed(error) {
        //     //toaster.pop('error', '', error.data.message);
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