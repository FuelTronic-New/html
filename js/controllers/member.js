'use strict';

/* Controllers */
// signin controller
app
    .controller('MemberController', ['$scope', '$http', '$state', '$localStorage', 'CRUD','toaster', function ($scope, $http, $state, $localStorage, CRUD,toaster) {




        var vm = this;
        vm.errorMessage = '';
        //vm.reset = reset;
        //vm.save = save;
        $scope.user = [];

        var member_url = 'user';

        $scope.getProfile=function () {
            CRUD.getAll(member_url).then(success).catch(failed);
            function success(res) {
                $scope.user = res.data;
            }
            function failed(error) {
                $location.reload();
            }
        }

        $scope.save = function (isValid) {
            if (isValid) {
                var data = $scope.user;
                delete data.email;

                if(data.password_verify !='' && data.password != data.password_verify)
                {
                    delete data.password;
                }

                CRUD.update('user/update', '', $.param(data)).then(function (res) {
                    $scope.getProfile();
                    toaster.pop('success', "User", "Updated");
                }).catch(function (res) {
                    toaster.pop('error', "", "Whoops Something Wrong.!");
                });
            }
        }
    }]);