'use strict';

/* Controllers */
// signin controller
app.controller('TransactionController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', '$modal', '$timeout', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope, $modal, $timeout) {
  //this.myDate = new Date();
//  this.isOpen = false;
        var listurl = 'customertransactions';
        var createurl = 'customertransactions/create/';
        var viewurl = 'customertransactions/'; //  user id required
        var saveurl = 'customertransactions';
        var updateurl = 'customertransactions/update/'; // user id required
        var deleteurl = 'customertransactions/delete/'; // user id required
        var bysiteid = 'customertransactions/getdetailsbysiteid/'; // site id required
        var bysiteidWithDate = 'customertransactions/getTransactionsByIdWithDate/'; // site id required

        $scope.isEditable = false;
        $scope.format = 'dd-MMMM-yyyy';
        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.dateTimeNow = function () {
            $scope.end_date = new Date();
            $scope.start_date = new Date();
        };

        $scope.dateTimeNow();

        $scope.toggleMinDate = function () {
            $scope.minDate = $scope.minDate ? null : new Date();
        };

$scope.date_wise_filter= function () {
   //alert($scope.start_date_search);
   //alert($scope.end_date_search);

var start=$scope.start_date_search;
var end=$scope.end_date_search;
var start_date= $filter('localDateTime')(start, 'YYYY-MM-DD HH:mm:ss');
var end_date=  $filter('localDateTime')(end, 'YYYY-MM-DD HH:mm:ss');      
              $(document).find('#transactions_report').DataTable().destroy();
              $(document).find('#transactions_report').DataTable({
             "dom": 'Bfrtip',
        "buttons": [
          'csv'
         ],
              "processing": true,
             // "serverSide": true,
              "searchable": true,
              "order": [[ 9, "desc" ]],
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],


              "ajax": {
                  "url": $rootScope.API_BASE_URL + bysiteidWithDate+ $scope.selectedSite.id + '?start_date='+start_date+'&end_date='+end_date+'&token=' + $localStorage.token,
                  "type": "GET",
                 
              },
              "columns": [
                  {data: null, name: 'view',render:function (data) {
                      var transactionId = data.id;

                      return ' <button data-transactionId="'+transactionId+'" class="btn btn-sm btn-icon btn-green view">' +
                      '<i class="fa fa-eye"></i></button>';
                  }},
                  {data:'grade_name',name:'grade_name'},
                  {data:'pump_name',name:'pump_name'},
                  {data: null, name: 'attendant_name',render:function (data) {
                      return data.attendant_name = !null ? data.attendant_name : '-';
                  }},
                  {data: null, name: 'litres',render:function (data) {
                      if (data.litres != null ){
                          return $filter('number')(data.litres, 2);
                      }else{
                          return '-';
                      }
                  }},
                  {data: null, name: 'vehicle_name',render:function (data) {
				return data.vehicle_name != null ? data.vehicle_name : '-';
                                 }},
                  {data: null, name: 'driver',render:function (data) {
                      return data.driver != null ? data.driver : '-';
                  //return '-';
                  }},
				  {data: null, name: 'order_number',render:function (data) {
                      return data.order_number != null ? data.order_number : '-';
                  //return '-';
                  }},
                  {data: null, name: 'odo_meter',render:function (data) {
                   return data.odo_meter != null ? data.odo_meter : '-';
                 }},
                  {data: null, name: 'id',render:function (data) {
                      return data.id != null ? data.id : '';
                  }},
				  {data: null, name: 'total_cost',render:function (data) {
                      return data.total_cost != null ? data.total_cost : '';
                  }},
                  {data: null, name: 'start_date',render:function (data) {
                      return data.start_date;
                  }},
                  {data: null, name: 'end_date',render:function (data) {
                      return data.end_date;
                  }},
                  {data: null, name: 'edit-delete',render:function (data) {
                      var transactionId = data.id;
                      return '<button data-transactionId="'+transactionId+'" class="btn btn-sm btn-icon btn-green edit"' +
                          'type="button" data-toggle="tooltip" tooltip="View" tooltip-placement="right"' +
                          'data-toggle="tooltip" tooltip="Edit" tooltip-placement="right">' +
                          '<i class="fa fa-pencil"></i></button>'+
                          '<button data-transactionId="'+transactionId+'" class="btn btn-sm btn-icon btn-warning delete"' +
                          'data-toggle="tooltip" style="margin-left:5px" tooltip="Delete" tooltip-placement="left">' +
                          '<i class="fa fa-trash"></i></button>';
                  }}
              ]
          });
          }
      
        $(document).unbind().on('click','.view',function () {
            CRUD.findById(viewurl, $(this).attr('data-transactionId')).then(function (response) {
               var data = response.data.data;
               $scope.transactionData = {
                   name: data.name,
                   hose_id: data.hose_id,
                   attendant_id: (data.attendant_id == 0) ? '' : data.attendant_id,
                   vehicle_id: (data.vehicle_id == 0) ? '' : data.vehicle_id,
                   job_id: (data.job_id == 0) ? '' : data.job_id,
                   litres: data.litres,
                   site_id: data.site_id,
                   odo_meter: data.odo_meter,
                   grade_name: data.grade_name,
                   pump_name: data.pump_name,
                   attendant_name: data.attendant_name,
                   pin: data.pin,
                   cost_exc_vat: parseFloat(data.cost_exc_vat).toFixed(2),
                   vat: parseFloat(data.vat).toFixed(2),
                   total_cost: parseFloat(data.total_cost).toFixed(2),
                   start_date: new Date(data.start_date),
                   end_date: new Date(data.end_date),
                   pump_id: data.pump_id,
               };
               $scope.displayOtherInfo($scope.transactionData);
           }, function (error) {
               $rootScope.loading = false;
               toaster.pop('error', '', error.data.message);
           });

        });
        $(document).on('click','.edit',function () {
            $state.go('app.transactions_edit',{ id: $(this).attr('data-transactionId') });
        });
        $(document).on('click','.delete',function () {
            $scope.deleteConfirm($(this).attr('data-transactionId'));
        });
        $scope.maxDate = new Date('2014-06-22');
        $scope.toggleMinDate();

        $scope.dateOptions = {
            showWeeks: false
        };

        // Disable weekend selection
        $scope.disabled = function (calendarDate, mode) {
            return mode === 'day' && ( calendarDate.getDay() === 0 || calendarDate.getDay() === 6 );
        };

        $scope.open = function ($event, opened) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.dateOpened = true;
            console.log('opened');
        };

        $scope.dateOpened = false;
        $scope.startDateOpend = false;
        $scope.hourStep = 1;
        $scope.minuteStep = 1;

        $scope.timeOptions = {
            hourStep: [1, 2, 3],
            minuteStep: [1, 5, 10, 15, 25, 30]
        };

        $scope.showMeridian = true;
        $scope.timeToggleMode = function () {
            $scope.showMeridian = !$scope.showMeridian;
        };

        $scope.$watch("end_date", function (date) {
            $scope.end_date = date;

            // read date value
        }, true);

        $scope.resetHours = function () {
            $scope.end_date.setHours(1);
        };
        $scope.$watch("start_date", function (date) {
            $scope.start_date = date;

            // read date value
        }, true);

        $scope.resetHours = function () {
            $scope.start_date.setHours(1);
        };

        if ($state.is('app.transactions_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.transactionData = {
                        name: data.name,
                        hose_id: data.hose_id,
                        attendant_id: (data.attendant_id == 0) ? '' : data.attendant_id,
                        vehicle_id: (data.vehicle_id == 0) ? '' : data.vehicle_id,
                        job_id: (data.job_id == 0) ? '' : data.job_id,
                        location_id: data.location_id,
                        litres: data.litres,
                        site_id: data.site_id,
                        odo_meter: data.odo_meter,
                        pin: data.pin,
                        cost_exc_vat: parseFloat(data.cost_exc_vat).toFixed(2),
                        vat: parseFloat(data.vat).toFixed(2),
                        total_cost: parseFloat(data.total_cost).toFixed(2),
                        start_date: new Date(data.start_date),
                        end_date: new Date(data.end_date),
                        pump_id: data.pump_id,

                    };
                    $scope.start_date = new Date($filter('localDateTime')(data.start_date, 'YYYY-MM-DD HH:mm:ss'));
                    $scope.end_date = new Date($filter('localDateTime')(data.end_date, 'YYYY-MM-DD HH:mm:ss'));
                    $scope.isEditable = true;
                    $scope.create();
                }, function (error) {
                    $rootScope.loading = false;
                    toaster.pop('error', '', error.data.message);
                });
            }
            $scope.transactionData = {};
        }

        $scope.getAllTransactions = function () {

         CRUD.getAll(listurl).then(success).catch(failed);
         function success(res) {
         $scope.sites = res.data;
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
              renderDatatable();
          }
         }
         function failed(error) {
             $rootScope.loading = false;
            //vm.errorMessage = error.message;
            //toaster.pop('error', '', error.data.message);
           }




            /* previous code commented due to use of server side datatables */
            // CRUD.getAll(listurl).then(success).catch(failed);
            // function success(res) {
            //     $scope.sites = res.data;
            //     if ($scope.sites.data.length) {
            //         if ($localStorage.site_id) {
            //             angular.forEach($scope.sites.data, function (value, key) {
            //                 if (value.id == $localStorage.site_id) {
            //                     $scope.selectedSite = value;
            //                 }
            //             });
            //         }
            //         else {
            //             $rootScope.siteData = $scope.sites.data[0];
            //             $scope.selectedSite = $scope.sites.data[0];
            //             $localStorage.site_id = $scope.sites.data[0].id;
            //         }
            //         var customer_transactions = [];
            //         angular.forEach($scope.selectedSite.customer_transaction, function (val) {
            //             if(val.litres != 0)
            //             {
            //                 customer_transactions.push(val);
            //             }
            //         });
            //         $scope.rowCollection = customer_transactions;
            //         $scope.displayCollection = customer_transactions;
            //     }
            //
            // }
            //
            // function failed(error) {
            //     $rootScope.loading = false;
            //     //vm.errorMessage = error.message;
            //     //toaster.pop('error', '', error.data.message);
            // }

        };

///////////////////////////////////////////////  Modal Assign Site  //////////////////////////////////////////////////
        $scope.displayOtherInfo = function (data) {
            var modalInstance = $modal.open({
                templateUrl: 'otherInfoModal.html',
                controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'data', sitesDetails]),
                size: 'med',
                resolve: {
                    data: function () {
                        return data;
                    },

                },
            });
            modalInstance.result.then(function () {
                // $scope.getAllTransactions(); //commented due to after view there is no actions required.
            });

        }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster, data) {
            $scope.columnlist=['Transaction Name','Attendant Name'];
            $scope.values=[data.name,data.attendant_name];
            $scope.otherInfo = data;
            var date = $filter('date')(data.start_date,'yyyy-MM-dd HH:mm:ss');
            $scope.otherInfo.start_date = $filter('localDateTime')(date, 'YYYY-MM-DD HH:mm:ss');
            $scope.closeModal = function () {
                $modalInstance.close();
            }

        }
        var filterData = {
            site_id: $scope.selectedSite.id
        };






        function renderDatatable() {
              $(document).find('#transactions_report').DataTable().destroy();
              $(document).find('#transactions_report').DataTable({
             "dom": 'Bfrtip',
        "buttons": [
          'csv'
         ],
              "processing": true,
             // "serverSide": true,
              "searchable": true,
              "order": [[ 9, "desc" ]],
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],


              "ajax": {
                  "url": $rootScope.API_BASE_URL + bysiteid+ $scope.selectedSite.id + '?token=' + $localStorage.token,
                  "type": "GET",
                  "data": filterData
              },
              "columns": [
                  {data: null, name: 'view',render:function (data) {
                      var transactionId = data.id;

                      return ' <button data-transactionId="'+transactionId+'" class="btn btn-sm btn-icon btn-green view">' +
                      '<i class="fa fa-eye"></i></button>';
                  }},
                  {data:'grade_name',name:'grade_name'},
                  {data:'pump_name',name:'pump_name'},
                  {data: null, name: 'attendant_name',render:function (data) {
                      return data.attendant_name = !null ? data.attendant_name : '-';
                  }},
                  {data: null, name: 'litres',render:function (data) {
                      if (data.litres != null ){
                          return $filter('number')(data.litres, 2);
                      }else{
                          return '-';
                      }
                  }},
                  {data: null, name: 'vehicle_name',render:function (data) {
					return data.vehicle_name != null ? data.vehicle_name : '-';
                   }},
                 {data: null, name: 'driver',render:function (data) {
                      return data.driver != "" ? data.driver : '-';
                  //return '-';
                  }},
				  {data: null, name: 'order_number',render:function (data) {
                      return data.order_number != "" ? data.order_number : '-';
                  //return '-';
                  }},
                  {data: null, name: 'odo_meter',render:function (data) {
                    return data.odo_meter != null ? data.odo_meter : '-';
                  }},
                  {data: null, name: 'id',render:function (data) {
                      return data.id != null ? data.id : '';
                  }},
				  {data: null, name: 'total_cost',render:function (data) {
                      return data.total_cost != null ? data.total_cost : '';
                  }},
                  {data: null, name: 'start_date',render:function (data) {
                      return data.start_date;
                  }},
                  {data: null, name: 'end_date',render:function (data) {
                      return data.end_date;
                  }},
                  {data: null, name: 'edit-delete',render:function (data) {
                      var transactionId = data.id;
                      return '<button data-transactionId="'+transactionId+'" class="btn btn-sm btn-icon btn-green edit"' +
                          'type="button" data-toggle="tooltip" tooltip="View" tooltip-placement="right"' +
                          'data-toggle="tooltip" tooltip="Edit" tooltip-placement="right">' +
                          '<i class="fa fa-pencil"></i></button>'+
                          '<button data-transactionId="'+transactionId+'" class="btn btn-sm btn-icon btn-warning delete"' +
                          'data-toggle="tooltip" style="margin-left:5px" tooltip="Delete" tooltip-placement="left">' +
                          '<i class="fa fa-trash"></i></button>';
                  }}
              ]
          });
          }

        $scope.changeSite = function () {
            $rootScope.siteData = $scope.selectedSite;
            $localStorage.site_id = $scope.selectedSite.id;
            $scope.rowCollection = $scope.selectedSite.customer_transaction;
            $scope.displayCollection = $scope.selectedSite.customer_transaction;

            // datatable render on change site.
            renderDatatable();

        }
        $scope.createTransaction = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.transactions_new');
        }

        $scope.create = function () {
            $rootScope.loading = true;
            if ($state.is('app.transactions_edit') && $scope.isEditable == true) {
                CRUD.getWithData('customertransactions/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl + $localStorage.site_id).then(success).catch(failed);
            }
            function success(res) {
                if (res.data.data.sites[0]) {
                    $scope.pumps = res.data.data.sites[0].pumps;
                    $scope.vehicles = res.data.data.sites[0].vehicles;
                    $scope.attendants = res.data.data.sites[0].attendants;
                    $scope.jobs = res.data.data.sites[0].jobs;
                    $scope.locations = res.data.data.sites[0].locations;
                    if ($state.is('app.transactions_edit')) {
                        $scope.pumpChange();
                    }
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }

        }

        $scope.save = function (isValid) {
            // $scope.startTime = $filter('date')($scope.start_date,"yyyy-MM-dd H:m:s");
            // $scope.endTime = $filter('date')($scope.end_date,"yyyy-MM-dd H:m:s");
            if (isValid) {
                var data = $scope.transactionData;
                var filterData = {
                    name: data.name,
                    hose_id: data.hose_id,
                    pump_id: data.pump_id,
                    location_id: data.location_id,
                    attendant_id: (data.attendant_id) ? data.attendant_id : 0,
                    vehicle_id: (data.vehicle_id) ? data.vehicle_id : 0,
                    job_id: (data.job_id) ? data.job_id : 0,
                    litres: data.litres,
                    odo_meter: data.odo_meter,
                    pin: data.pin,
                    cost_exc_vat: data.cost_exc_vat,
                    vat: data.vat,
                    total_cost: data.total_cost,
                    start_date: moment($scope.start_date).utc().format("YYYY-MM-DD HH:mm:ss"),
                    end_date: moment($scope.end_date).utc().format("YYYY-MM-DD HH:mm:ss"),
                };

                if ($state.is('app.transactions_edit')) {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Transaction", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.transactions');
                    }, function (res) {
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                            });
                        }
                        $rootScope.loading = false;
                    });
                }
                else {
                    if ($rootScope.site_id) {
                        filterData.site_id = $rootScope.site_id;
                        $rootScope.loading = true;
                        CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                            if (response.data.status == "success") {
                                toaster.pop('success', "Transaction", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.transactionData = {};
                            $state.go('app.transactions');

                        }, function (res) {
                            if (res.status == 400) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }
                            $rootScope.loading = false;
                        });
                    } else {
                        toaster.pop('error', '', 'Whoops Something Wrong.');
                        $state.go('app.transactions');
                    }
                }
            }
        }

        $scope.deleteConfirm = function (ID) {
            var modalInstance = $modal.open({
                templateUrl: 'uploadSIGNFileModal.html',
                controller: ('remove', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', remove]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                }
            });

            modalInstance.result.then(function (data) {
                if (data) {
                    $scope.deleteTransaction(data);
                }
            });
        }
        function remove($scope, $rootScope, $localStorage, $modalInstance, toaster, ID) {
            $scope.confirm = function () {
                $modalInstance.close(ID);
            }
            $scope.closeModal = function () {
                $modalInstance.close();
            }
        }
        $scope.pumpChange = function () {
            $rootScope.loading = true;
            angular.forEach($scope.pumps ,function (value,key) {
                if (value.id == $scope.transactionData.pump_id){
                    $scope.hoses = value.hoses;
                }
            });
            $timeout(function () {
                if ($state.is('app.transactions_edit')) {
                    $scope.hoseChange();
                }
            },2000);
            $rootScope.loading = false;
        }
        $scope.hoseChange = function () {
            if ($scope.transactionData.hose_id){
                $rootScope.loading = true;
                CRUD.getWithData('customertransactions/getdetailsfromhoseid/' + $scope.transactionData.hose_id).then(success).catch(failed);
            }

            function success(response) {console.log($scope.transactionData.hose_id)
                $scope.hoseData = response.data.data;
                if ($state.is('app.transactions_new')) {
                    $scope.transactionData.odo_meter = response.data.data.odo_meter;
                    $scope.transactionData.pin = response.data.data.pin;
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
            }

        }

        $scope.totalcostvat = function () {

            $scope.transactionData.cost_exc_vat = parseFloat(parseFloat($scope.transactionData.litres) * $scope.hoseData.grade_price).toFixed(2);
            $scope.transactionData.vat = parseFloat((parseFloat($scope.transactionData.cost_exc_vat) * $scope.hoseData.grade_vat) / 100).toFixed(2);
            $scope.transactionData.total_cost = parseFloat(parseFloat($scope.transactionData.cost_exc_vat) + parseFloat($scope.transactionData.vat)).toFixed(2);
        }

        $scope.deleteTransaction = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Transaction", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllTransactions();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });

        };

    }]);