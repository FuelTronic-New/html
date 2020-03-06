'use strict';

/* Controllers */
// signin controller
app
    .controller('AdminDashboardController', ['$scope', '$http', '$state', '$localStorage', 'CRUD','toaster', function ($scope, $http, $state, $localStorage, CRUD,toaster) {

        $scope.data = {};
        var url = 'admin/statistics';

        getBasic();

        function getBasic() {
            CRUD.getAll(url).then(success).catch(failed);

            function success(res) {
                $scope.data = res.data;
            }

            function failed(error) {
                toaster.pop('info', "Admin Statistics", " Whoops Something Wrong.");
            }
        }
    }]);