'use strict';

/* Controllers */
// signin controller
app
    .controller('PaymentController', ['$scope', '$http', '$state', 'CRUD', 'toaster', '$rootScope', '$modal', '$localStorage', function ($scope, $http, $state, CRUD, toaster, $rootScope, $modal, $localStorage) {

        var listurl = 'payments';
        var createurl = 'payments/create';
        var viewurl = 'payments/'; //  user id required
        var saveurl = 'payments';
        var updateurl = 'payments/update/'; // user id required
        var deleteurl = 'payments/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];

        if ($state.is('app.payments_edit')) {
            $rootScope.loading = true;
            if ($state.params.id != '') {
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;
                    $scope.paymentData = {
                        amount: data.amount,
                        created_by: $rootScope.user.id,
                        site_id: data.site_id,
                    };
                    if(data.supplier_id != 0){
                        $scope.paymentData.Type='supplier';
                        $scope.paymentData.supplier_id=data.supplier_id;
                    }
                    if(data.customer_id != 0){
                        $scope.paymentData.Type='customer';
                        $scope.paymentData.customer_id=data.customer_id;
                    }
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.paymentData = {};

        }

        $scope.getAllPayments = function () {
            $rootScope.loading = true;
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
                    $scope.rowCollection = $scope.selectedSite.payments;
                    $scope.displayCollection = $scope.selectedSite.payments;
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
                //vm.errorMessage = error.message;
            }

        };

        $scope.changeSite = function () {
            $rootScope.siteData = $scope.selectedSite;
            $localStorage.site_id = $scope.selectedSite.id;
            $scope.rowCollection = $scope.selectedSite.payments;
            $scope.displayCollection = $scope.selectedSite.payments;
        }

        $scope.createPayment = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.payments_new');
        }

        $scope.create = function () {
            $rootScope.loading = true;
            if ($state.is('app.payments_edit')) {
                CRUD.getWithData('payments/' + $state.params.id + '/edit').then(success).catch(failed);
            }
            else {
                CRUD.getWithData(createurl + '/' + $rootScope.site_id).then(success).catch(failed);
            }
            function success(res) {
                $scope.customers = res.data.data.sites[0].customers;
                $scope.suppliers = res.data.data.sites[0].suppliers;
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error', '', error.data.message);
            }

        }

        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.paymentData;
                var filterData = {
                    amount: data.amount,
                    created_by: $rootScope.user.id,
                };
                if ($scope.paymentData.Type == 'customer') {
                    filterData.mode = '+';
                    filterData.customer_id = data.customer_id;
                    filterData.supplier_id = 0;
                }
                else {
                    filterData.mode = '-';
                    filterData.supplier_id = data.supplier_id;
                    filterData.customer_id = 0;
                }
                if ($state.is('app.payments_edit')) {
                    filterData.site_id = data.site_id;
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Payment", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.payments');
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
                                toaster.pop('success', "Payment", response.data.message);
                            }
                            $scope.paymentData = {};
                            $rootScope.loading = false;
                            $state.go('app.payments');

                        }, function (res) {
                            if (res.status == 400) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                            }
                            $rootScope.loading = false;
                        });
                    }
                }
            }

        }

        $scope.deleteConfirm = function (ID) {
            var modalInstance = $modal.open({
                templateUrl: 'deleteFileModal.html',
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
                    $scope.deletePayment(data);
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

        $scope.deletePayment = function (id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Payment", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllPayments();
                }

            }, function (error) {
                $rootScope.loading = false;
                toaster.pop('error', '', error.data.message);
            });

        };

    }]);