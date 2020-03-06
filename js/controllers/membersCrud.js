'use strict';

/* Controllers */
// signin controller
app
    .controller('MembersCrudController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster','$filter','$location', function ($scope, $http, $state, $localStorage, CRUD, toaster,$filter,$location) {

        var listurl = 'admin/users';
        var viewurl = 'admin/user/'; //  user id required
        var saveurl = 'admin/user/create';
        var updateurl = 'admin/user/update/'; // user id required
        var deleteurl = 'admin/user/delete/'; // user id required
        var update_status_url = 'admin/user/updatestatus/'; // user id required

        $scope.getStatus=function(status){
            if(status == 200 || status == 100)
            {
                return 'btn-primary';
            }
            else {
                return 'btn-danger';
            }
        };

        $scope.isUpdateForm=false;

        if($state.is('app.member_update')) {

            if($state.params.id != ''){
                CRUD.findById(viewurl,$state.params.id).then(function(response){
                    $scope.isUpdateForm=true;
                    var data=response.data;
                    $scope.userData = {
                        Cell: data.Cell,
                        fname: data.FullName,
                        MerchantNumber: data.MerchantNumber,
                        regemail: data.email,
                        password: '',
                        cpassword: ''
                    };

                },function(){});
            }
            $scope.userData={};
        }

        $scope.rowCollection = [];
        $scope.displayCollection = [];

        $scope.predicates = ['ID', 'FullName', 'username', 'Cell', 'MerchantNumber', 'isActive'];
        $scope.selectedPredicate = $scope.predicates[0];

        $scope.getAllMembers = function () {
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.rowCollection = res.data;
            }

            function failed(error) {
                //vm.errorMessage = error.message;
            }
        };

        $scope.save = function (isValid) {
            if(isValid)
            {
                var data=$scope.userData;
                var filterData = {
                     Cell: data.Cell,
                     fname: data.fname,
                     MerchantNumber: data.MerchantNumber,
                     regemail: data.regemail,
                     regpassword: $scope.userData.regpassword
                 };

                if($state.is('app.member_update'))
                {
                    CRUD.update(updateurl, $state.params.id,  $.param(filterData)).then(function (response) {
                        toaster.pop('success', "User", "Updated.");
                    }, function (res) {
                        if(res.status == 400)
                        {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                        toaster.pop('error', '', value[0]);
                                    });
                        }
                        toaster.pop('error', "User", "Whoops Something Wrong");
                    });
                }
                else{

                    CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                        if(response.status == "success")
                        {
                            toaster.pop('success', "User", "Created Successfully.");
                        }

                        $scope.userData={};

                    }, function (res) {
                        if(res.status == 400)
                        {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                        toaster.pop('error', '', value[0]);
                                    });
                        }

                    });
                }
            }


        }

        $scope.changeStatus = function(ID,IsActive) {

            var status=0;

            if(IsActive == 100)
            {
                status=200;
            }
            else if(IsActive == 200)
            {
                status=0;
            }
            else if(IsActive == 0)
            {
                status=100;
            }
            else{
                status=0;
            }
            CRUD.update(update_status_url, ID, $.param({IsActive:status})).then(function (response) {
                $scope.getAllMembers();
                toaster.pop('success', "User", "Status Updated.");
            }, function () {
                toaster.pop('error', "User", "Whoops,Status Not Updated.");
            });

        };


        $scope.deleteMember = function (id) {

            CRUD.delete(deleteurl, id).then(function (res) {
                if(res.data.status == "success")
                {
                    toaster.pop('success', "User", "Deleted Successfully.");
                    $scope.getAllMembers();
                }

            }, function () {

            });
        };
    }]);