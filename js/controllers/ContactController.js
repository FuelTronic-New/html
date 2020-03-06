'use strict';

/* Controllers */
// signin controller
app

    .controller('ContactController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', '$modal', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope, $modal) {

        var listurl = 'customers';
        var createurl = 'customers/create';
        var viewurl = 'customers/'; //  user id required
        var saveurl = 'customers';
        var updateurl = 'customers/update/'; // user id required
        var deleteurl = 'customers/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.is_check_site = [];

////////////////////////////////////  Customer Edit Form Data  ////////////////////////////////////////////////////////
        if ($state.is('app.contacts_edit')) {

            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.customerData = {
                        accountNumber: data.accountNumber,
                        status: data.status,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        email_address: data.email_address,
                        phone: data.phone,
                        usage: data.usage,
                        fax: data.fax,
                        mobile: data.mobile,
                        fuel_price: data.fuel_price,
                        site_id: data.site_id,
                        address_line_1: data.address_line_1,
                        address_line_2: data.address_line_2,
                        address_line_3: data.address_line_3,
                        address_line_4: data.address_line_4,
                    };
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                    //toaster.pop('error','',error.data.message);
                });
            }
            $scope.customerData = {};
        }

////////////////////////////////////////   Customers Listing  ///////////////////////////////////////////////////////
        $scope.getAllCustomers = function (site_id) {
            $rootScope.loading = true;
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                $scope.customers = res.data.data.customers;
                $scope.rowCollection = [];
                $scope.displayCollection = [];
                $scope.sites = res.data.data.sites;

                if ($scope.sites.length) {
                    if ($localStorage.site_id) {
                        angular.forEach($scope.sites, function (value, key) {
                            if (value.id == $localStorage.site_id) {
                                $scope.selectedSite = value.id;
                            }
                        });
                    }
                    else if (site_id) {
                        $scope.selectedSite = site_id;
                        $localStorage.site_id = site_id;
                    }
                    else {
                        $rootScope.siteData = $scope.sites[0];
                        $scope.selectedSite = $scope.sites[0].id;
                        $localStorage.site_id = $scope.sites[0].id;
                    }
                    //$scope.selectedSite = (site_id) ? site_id : $scope.sites[0].id;
                    for (var row in $scope.customers) {
                        if ($scope.customers[row].customerSites.indexOf($scope.selectedSite) != -1) {
                            $scope.rowCollection.push($scope.customers[row]);
                            $scope.displayCollection.push($scope.customers[row]);
                        }
                    }
                }

                for (var row in $scope.customers) {
                    if (!$scope.is_check_site[$scope.customers[row].id]) {
                        $scope.is_check_site[$scope.customers[row].id] = [];
                    }
                    for (var key in $scope.sites) {
                        if (!$scope.is_check_site[$scope.customers[row].id][$scope.sites[key].id]) {
                            $scope.is_check_site[$scope.customers[row].id][$scope.sites[key].id] = false;
                        }
                        $scope.is_check_site[$scope.customers[row].id][$scope.sites[key].id] = ($scope.customers[row].customerSites.indexOf($scope.sites[key].id) != -1);
                    }
                }
                $rootScope.loading = false;
                //$scope.sites = res.data;
                //if ($scope.sites.data.length) {
                //    $scope.selectedSite = $scope.sites.data[0];
                //    $scope.rowCollection = $scope.selectedSite.customers;
                //    $scope.displayCollection = $scope.selectedSite.customers;
                //}

            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }

        };

//////////////////////////////////////////  Site Change  //////////////////////////////////////////////////////////////
        $scope.changeSite = function (site_id) {
            $localStorage.site_id = site_id;
            $scope.rowCollection = [];
            $scope.displayCollection = [];
            angular.forEach($scope.sites, function (value, key) {
                if (value.id == site_id) {
                    $rootScope.siteData = value;
                }
            });
            for (var row in $scope.customers) {
                if ($scope.customers[row].customerSites.indexOf(site_id) != -1) {
                    $scope.rowCollection.push($scope.customers[row]);
                    $scope.displayCollection.push($scope.customers[row]);
                }
            }
            //$scope.rowCollection = $scope.selectedSite.customers;
            //$scope.displayCollection = $scope.selectedSite.customers;
        }

///////////////////////////////////////////////  Create or Edit form data  /////////////////////////////////////////////
        $scope.createCustomer = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.contacts_new');
        }

//////////////////////////////////////// Save Or Update Customer   ////////////////////////////////////////////////
        $scope.save = function (isValid) {
            if (isValid) {
                var data = $scope.customerData;
                var filterData = {
                    accountNumber: data.accountNumber,
                    status: data.status,
                    first_name: data.first_name,
                    last_name: data.last_name,
                    email_address: data.email_address,
                    phone: data.phone,
                    usage: data.usage,
                    fax: data.fax,
                    mobile: data.mobile,
                    fuel_price: data.fuel_price,
                    address_line_1: data.address_line_1,
                    address_line_2: data.address_line_2,
                    address_line_3: data.address_line_3,
                    address_line_4: data.address_line_4,
                };
                if ($state.is('app.contacts_edit')) {
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Customer", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.contacts');
                    }, function (res) {
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                            });
                            $rootScope.loading = false;
                        }
                    });
                }
                else {
                    if ($rootScope.site_id) {
                        filterData.site_id = $rootScope.site_id;
                        $rootScope.loading = true;
                        CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                            if (response.data.status == "success") {
                                toaster.pop('success', "Customer", response.data.message);
                            }
                            $rootScope.loading = false;
                            $scope.customerData = {};
                            $state.go('app.contacts');

                        }, function (res) {
                            if (res.status == 400) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                });
                                $rootScope.loading = false;
                            }

                        });
                    } else {
                        toaster.pop('error', '', 'Whoops Something Wrong.');
                        $state.go('app.contacts');
                    }
                }
            }
        }

///////////////////////////////////////////////  Modal Assign Site  //////////////////////////////////////////////////
        $scope.assignSite = function (ID, customerName, siteId) {
            var modalInstance = $modal.open({
                templateUrl: 'updateCustomerSite.html',
                controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'customerName', 'siteId', sitesDetails]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    siteId: function () {
                        return siteId;
                    },
                    customerName: function () {
                        return customerName;
                    }
                }
            });

            modalInstance.result.then(function (siteID) {
                $scope.getAllCustomers(siteID);
            });

        }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, customerName, siteId) {
            $rootScope.loading = true;
            CRUD.findById('customers/getsites/', ID).then(function (res) {
                $scope.sitesData = res.data.data;
                for (var row in $scope.sitesData.customerSites) {
                    $scope.customer_sites[$scope.sitesData.customerSites[row]] = true;
                }
            });
            $scope.customer_name = customerName;
            $scope.siteID = siteId;
            $scope.customer_sites = [];
            $scope.user_site = [];

            $scope.saveUserCustomer = function (siteID) {
                //console.log($scope.userSites)

                angular.forEach($scope.customer_sites, function (value, key) {
                    if (value) {
                        $scope.user_site.push(key);
                    }
                })

                var filterData = {
                    customer_id: ID,
                    site_ids: $scope.user_site.toString(),
                };

                var token = $localStorage.token || '';
                $rootScope.loading = true;
                $http({
                    method: 'POST',
                    url: $rootScope.API_BASE_URL + 'customers/updatesitesofcustomer?token=' + token,
                    params: filterData,
                    withCredentials: true
                }).then(function (res) {
                    //console.log(res)
                    toaster.pop('success', 'Customer', res.data.message);
                    $rootScope.loading = false;
                    $modalInstance.close(siteID);

                }, function (error) {
                    angular.forEach(error.data.validation_errors, function (value, key) {
                        toaster.pop('error', 'Customer', value[0]);
                    });
                    $rootScope.loading = false;
                });
            }

            $scope.closeModal = function (siteID) {
                $modalInstance.close(siteID);
            }
        }

////////////////////////////////////////  Delete Confirmation Modal  //////////////////////////////////////////////////
        $scope.deleteConfirm = function (ID, site_id) {
            var modalInstance = $modal.open({
                templateUrl: 'deleteFileModal.html',
                controller: ('remove', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'site_id', remove]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    site_id: function () {
                        return site_id;
                    },

                }
            });

            modalInstance.result.then(function (data) {
                if (data) {
                    $scope.deleteCustomer(data[0].id, data[0].site_id);
                }
            });

        }
        function remove($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, site_id) {

            $scope.confirm = function () {
                var ids = [];
                ids.push({id: ID, site_id: site_id});
                $modalInstance.close(ids);
            }
            $scope.closeModal = function () {
                $modalInstance.close();
            }
        }

/////////////////////////////////////////  Delete Customer  ///////////////////////////////////////////////////////////
        $scope.deleteCustomer = function (id,site_id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Customer", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllCustomers(site_id);
                }

            }, function (error) {
                toaster.pop('error', '', error.data.message);
                $rootScope.loading = false;
            });

        };

///////////////////////////////////////////////  Listing Assign Site  //////////////////////////////////////////////////
        $scope.siteAssign = function (site_id, customer_id) {
            $rootScope.loading = true;
            $scope.user_site = [];

            angular.forEach($scope.is_check_site[customer_id], function (value, key) {
                if (value) {
                    $scope.user_site.push(key);
                }
            })

            var filterData = {
                customer_id: customer_id,
                site_ids: $scope.user_site.toString(),
            };

            var token = $localStorage.token || '';
            $http({
                method: 'POST',
                url: $rootScope.API_BASE_URL + 'customers/updatesitesofcustomer?token=' + token,
                params: filterData,
                withCredentials: true
            }).then(function (res) {
                //console.log(res)
                toaster.pop('success', 'Customers', res.data.message);
                $rootScope.loading = false;
                $scope.getAllCustomers(site_id);
            }, function (error) {
                angular.forEach(error.data.validation_errors, function (value, key) {
                    toaster.pop('error', 'Customers', value[0]);
                });
                $rootScope.loading = false;
            });
        }

    }]);