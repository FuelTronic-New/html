'use strict';

/* Controllers */
// supplier controller
app

    .controller('SupplierController', ['$scope', '$http', '$state', '$localStorage', 'CRUD', 'toaster', '$filter', '$location', '$rootScope', '$modal', function ($scope, $http, $state, $localStorage, CRUD, toaster, $filter, $location, $rootScope, $modal) {

        var listurl = 'suppliers';
        var viewurl = 'suppliers/'; //  user id required
        var saveurl = 'suppliers';
        var updateurl = 'suppliers/update/'; // user id required
        var deleteurl = 'suppliers/delete/'; // user id required

        $scope.isUpdateForm = false;
        $scope.rowCollection = [];
        $scope.selectedSite = '';
        $scope.sites = [];
        $scope.displayCollection = [];
        $scope.is_check_site = [];

////////////////////////////////////  Supplier Edit Form Data  ////////////////////////////////////////////////////////
        if ($state.is('app.suppliers_edit')) {
            if ($state.params.id != '') {
                $rootScope.loading = true;
                CRUD.findById(viewurl, $state.params.id).then(function (response) {
                    $scope.isUpdateForm = true;
                    var data = response.data.data;

                    $scope.supplierData = {
                        accountNumber: data.accountNumber,
                        status: data.status,
                        first_name: data.first_name,
                        last_name: data.last_name,
                        email_address: data.email_address,
                        phone: data.phone,
                        //usage: data.usage,
                        fax: data.fax,
                        mobile: data.mobile,
                        site_id: data.site_id,
                    };
                    $rootScope.loading = false;
                }, function (error) {
                    $rootScope.loading = false;
                });
            }
            $scope.supplierData = {};
        }

////////////////////////////////////////   Supplier Listing  //////////////////////////////////////////////////////////
        $scope.getAllSuppliers = function (site_id) {
            $rootScope.loading = true;
            CRUD.getAll(listurl).then(success).catch(failed);
            function success(res) {
                //$scope.sites = res.data;
                //        if ($scope.sites.data.length) {
                //            $scope.selectedSite = $scope.sites.data[0];
                //            $scope.rowCollection = $scope.selectedSite.suppliers;
                //            $scope.displayCollection = $scope.selectedSite.suppliers;
                //        }
                $scope.suppliers = res.data.data.suppliers;
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
                    for (var row in $scope.suppliers) {
                        if ($scope.suppliers[row].supplierSites.indexOf($scope.selectedSite) != -1) {
                            $scope.rowCollection.push($scope.suppliers[row]);
                            $scope.displayCollection.push($scope.suppliers[row]);
                        }
                    }
                }

                for (var row in $scope.suppliers) {
                    if (!$scope.is_check_site[$scope.suppliers[row].id]) {
                        $scope.is_check_site[$scope.suppliers[row].id] = [];
                    }
                    for (var key in $scope.sites) {
                        if (!$scope.is_check_site[$scope.suppliers[row].id][$scope.sites[key].id]) {
                            $scope.is_check_site[$scope.suppliers[row].id][$scope.sites[key].id] = false;
                        }
                        $scope.is_check_site[$scope.suppliers[row].id][$scope.sites[key].id] = ($scope.suppliers[row].supplierSites.indexOf($scope.sites[key].id) != -1);
                    }
                }
                $rootScope.loading = false;
            }

            function failed(error) {
                $rootScope.loading = false;
                //toaster.pop('error','',error.data.message);
            }
        };

///////////////////////////////////////////////  Create or Edit form data ///////////////////////////////////////////
        $scope.createSupplier = function (site_id) {
            $rootScope.site_id = site_id;
            $state.go('app.suppliers_new');
        }

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
            for (var row in $scope.suppliers) {
                if ($scope.suppliers[row].supplierSites.indexOf(site_id) != -1) {
                    $scope.rowCollection.push($scope.suppliers[row]);
                    $scope.displayCollection.push($scope.suppliers[row]);
                }
            }
            //$scope.rowCollection = $scope.selectedSite.suppliers;
            //$scope.displayCollection = $scope.selectedSite.suppliers;
        }

//////////////////////////////////////// Save New Or Update Supplier   ////////////////////////////////////////////////
        $scope.save = function (isValid) {

            if (isValid) {
                var data = $scope.supplierData;
                var filterData = {
                    accountNumber: data.accountNumber,
                    status: data.status,
                    first_name: data.first_name,
                    last_name: data.last_name,
                    email_address: data.email_address,
                    phone: data.phone,
                    //usage: data.usage,
                    fax: data.fax,
                    mobile: data.mobile,
                };
                if ($state.is('app.suppliers_edit')) {
                    $rootScope.loading = true;
                    CRUD.update(updateurl, $state.params.id, $.param(filterData)).then(function (response) {
                        toaster.pop('success', "Supplier", response.data.message);
                        $rootScope.loading = false;
                        $state.go('app.suppliers');
                    }, function (res) {
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                                $rootScope.loading = false;
                            });
                        }
                    });
                }
                else {
                    if ($rootScope.site_id) {
                        filterData.site_id = $rootScope.site_id;
                        $rootScope.loading = true;
                        CRUD.store(saveurl, $.param(filterData)).then(function (response) {

                            if (response.data.status == "success") {
                                toaster.pop('success', "Supplier", response.data.message);
                            }

                            $scope.supplierData = {};
                            $rootScope.loading = false;
                            $state.go('app.suppliers');

                        }, function (res) {
                            if (res.status == 400) {
                                angular.forEach(res.data.validation_errors, function (value, key) {
                                    toaster.pop('error', '', value[0]);
                                    $rootScope.loading = false;
                                });
                            }

                        });
                    } else {
                        toaster.pop('error', '', 'Whoops Something Wrong');
                    }
                }
            }
            //$rootScope.loading = false;
        }

///////////////////////////////////////////////  Modal Assign Site  //////////////////////////////////////////////////
        $scope.assignSite = function (ID, supplierName, siteId) {
            var modalInstance = $modal.open({
                templateUrl: 'updateSupplierSite.html',
                controller: ('sitesDetails', ['$scope', '$rootScope', '$localStorage', '$modalInstance', 'toaster', 'ID', 'supplierName', 'siteId', sitesDetails]),
                size: 'med',
                resolve: {
                    ID: function () {
                        return ID;
                    },
                    siteId: function () {
                        return siteId;
                    },
                    supplierName: function () {
                        return supplierName;
                    }
                }
            });

            modalInstance.result.then(function (siteID) {
                $scope.getAllSuppliers(siteID);
            });

        }
        function sitesDetails($scope, $rootScope, $localStorage, $modalInstance, toaster, ID, supplierName, siteId) {
            $rootScope.loading = true;
            CRUD.findById('suppliers/getsites/', ID).then(function (res) {
                $scope.sitesData = res.data.data;
                for (var row in $scope.sitesData.supplierSites) {
                    $scope.supplier_sites[$scope.sitesData.supplierSites[row]] = true;
                }
                $rootScope.loading = false;
            });
            $scope.supplier_name = supplierName;
            $scope.siteID = siteId;
            $scope.supplier_sites = [];
            $scope.user_site = [];
            $scope.saveUserSupplier = function (siteID) {
                //console.log($scope.userSites)

                angular.forEach($scope.supplier_sites, function (value, key) {
                    if (value) {
                        $scope.user_site.push(key);
                    }
                })

                var filterData = {
                    supplier_id: ID,
                    site_ids: $scope.user_site.toString(),
                };
                $rootScope.loading = true;
                var token = $localStorage.token || '';
                $http({
                    method: 'POST',
                    url: $rootScope.API_BASE_URL + 'suppliers/updatesitesofsupplier?token=' + token,
                    params: filterData,
                    withCredentials: true
                }).then(function (res) {
                    //console.log(res)
                    toaster.pop('success', 'Suppliers', res.data.message);
                    $modalInstance.close(siteID);
                    $rootScope.loading = false;
                }, function (error) {
                    angular.forEach(error.data.validation_errors, function (value, key) {
                        toaster.pop('error', 'Suppliers', value[0]);
                        $rootScope.loading = false;
                    });
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
                    $scope.deleteSupplier(data[0].id, data[0].site_id);
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

/////////////////////////////////////////  Delete Supplier  ///////////////////////////////////////////////////////////
        $scope.deleteSupplier = function (id,site_id) {
            $rootScope.loading = true;
            CRUD.delete(deleteurl, id).then(function (res) {
                if (res.data.status == "success") {
                    toaster.pop('success', "Supplier", res.data.message);
                    $rootScope.loading = false;
                    $scope.getAllSuppliers(site_id);
                }

            }, function (error) {
                toaster.pop('error', '', error.data.message);
                $rootScope.loading = false;
            });
        };

///////////////////////////////////////////////  Listing Assign Site  //////////////////////////////////////////////////
        $scope.siteAssign = function (site_id, supplier_id) {
            $rootScope.loading = true;
            $scope.user_site = [];

            angular.forEach($scope.is_check_site[supplier_id], function (value, key) {
                if (value) {
                    $scope.user_site.push(key);
                }
            })

            var filterData = {
                supplier_id: supplier_id,
                site_ids: $scope.user_site.toString(),
            };

            var token = $localStorage.token || '';
            $http({
                method: 'POST',
                url: $rootScope.API_BASE_URL + 'suppliers/updatesitesofsupplier?token=' + token,
                params: filterData,
                withCredentials: true
            }).then(function (res) {
                //console.log(res)
                toaster.pop('success', 'Suppliers', res.data.message);
                $rootScope.loading = false;
                $scope.getAllSuppliers(site_id);
            }, function (error) {
                angular.forEach(error.data.validation_errors, function (value, key) {
                    toaster.pop('error', 'Suppliers', value[0]);
                    $rootScope.loading = false;
                });
            });

        }

    }]);