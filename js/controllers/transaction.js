'use strict';

/* Controllers */
// signin controller
app
    .controller('TransactionController', ['$scope','$rootScope', '$http', '$state', '$localStorage', 'CRUD','$modal', function ($scope,$rootScope, $http, $state, $localStorage, CRUD,$modal) {

        $scope.account={};

        var listurl = 'admin/transactions';
        var viewurl = 'admin/transaction/'; //  id required
        var view_by_userurl = 'usertrasactions'; //  id required

        $scope.rowCollection = [];
        $scope.displayCollection = [];

        $scope.fromurl=listurl;
        $scope.getAllTransaction = function () {
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                 $scope.rowCollection = res.data;
                $scope.displayCollection=res.data;
            }

            function failed(error) {
                //vm.errorMessage = error.message;
            }
        };


        $scope.openModalView = function (transaction) {
            console.log(transaction);
            $scope.transactionData=transaction;
            var modalInstance = $modal.open({
                templateUrl: 'views/transactionModal.html',
                controller: ('transactionDetails', ['$scope','transactionData', transactionDetails]),
                size: 'med',
                resolve:{
                    transactionData:function(){
                        return transaction;
                    }
                }
            });
        };

        $scope.getTransactionByCurrentUser = function () {

            CRUD.getAll(view_by_userurl).then(success).catch(failed);
            function success(res) {
                 $scope.rowCollection = res.data;
                 $scope.displayCollection=res.data;
            }

            function failed(error) {

            }


        }
        function transactionDetails($scope,transactionData) {
            $scope.transactionData=transactionData;
        };

    }]);
