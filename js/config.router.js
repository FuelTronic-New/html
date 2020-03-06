'use strict';

/**
 * Config for the router
 */
angular.module('app')
    .service('authMiddleware', ['$localStorage', '$state', 'toaster', function ($localStorage, $state, toaster) {
        var authMiddleware = this;
        authMiddleware.run = function (event) {
            if (angular.isUndefined($localStorage.token) || angular.isUndefined($localStorage.user)) {
                event.preventDefault();
                toaster.pop('info', '', 'You are not logged in, so you cant browse this');
                $state.go('access.signin');
            }
        };
        return {
            run: authMiddleware.run
        };
    }])

    .run(
        ['$rootScope', '$state', '$stateParams', '$localStorage', '$injector',
            function ($rootScope, $state, $stateParams, $localStorage, $injector) {
                $rootScope.isAdminPanelShow = function () {
                    if ($localStorage.user) {
                        return $localStorage.user.IsActive == 200 ? true : false;
                    }
                    return false;
                };
                $rootScope.isUserPanelShow = function () {
                    if ($localStorage.user) {
                        return true;
                    }
                    return false;
                };
                $rootScope.username = function () {
                    if ($localStorage.user) {
                        return $localStorage.user.FullName;
                    }
                    return 'N/A';
                };

                $rootScope.getRole = function (id) {
                    if (id == 1) {
                        return 'Super Admin';
                    }
                    else if (id == 2) {
                        return 'Admin';
                    }
                    else if (id == 3) {
                        return 'User';
                    }
                    else {
                        return 'N/A';
                    }
                }
                $rootScope.getMultiSelectIds = function (selected, Ids) {

                    angular.forEach(selected, function (value, key) {
                        if (value) {
                             Ids.push(value.id);
                        }
                    });
                }

                $rootScope.$state = $state;
                $rootScope.$stateParams = $stateParams;

                $rootScope.$on('$stateChangeStart', function (event, toState, toParams) {
                    var currentState = toState;
                    callMiddlewares(event, currentState);
                    function callMiddlewares(event, state) {
                        if (state && state.hasOwnProperty('middleware')) {
                            if (typeof currentState.middleware === 'object') {
                                angular.forEach(state.middleware, function (middleWare) {
                                    callMiddleware(middleWare, event);
                                });
                                return;
                            }
                        }
                    }

                    function callMiddleware(middleWare, event) {
                        try {
                            $injector.get(middleWare).run(event);
                        } catch (e) {
                            console.log('the factory : ' + middleWare + ' does not exist');
                        }
                    };
                });
            }
        ]
    )
    .config(
        ['$httpProvider', '$stateProvider', '$urlRouterProvider', 'JQ_CONFIG', 'MODULE_CONFIG',
            function ($httpProvider, $stateProvider, $urlRouterProvider, JQ_CONFIG, MODULE_CONFIG) {

                $httpProvider.interceptors.push(['$q', '$location', '$localStorage', 'toaster', function ($q, $location, $localStorage, toaster) {
                    return {
                        'request': function (config) {
                            config.headers = config.headers || {};
                            return config;
                        },
                        'responseError': function (response) {
                            console.log(response);
                            if (response.status === 400 && response.data.error && (response.data.error == 'token_invalid' || response.data.error == 'token_expired' || response.data.error == 'token_not_provided')) {
                                delete $localStorage.user;
                                delete $localStorage.token;
                                toaster.pop('error', '', 'You are logged out.');
                                $location.path('/access/signin');
                            } else if (response.status === 401 && response.data === "unauthorized") {
                                if ($localStorage.user) {
                                    $location.path('/app/dashboard');
                                } else {
                                    $location.path('/access/signin');
                                }
                            } else if (response.data != null && (response.data.error === "token_expired" || response.data.error === "token_invalid")) {
                                delete $localStorage.user;
                                delete $localStorage.token;
                                $location.path('/access/signin');
                            }
                            return $q.reject(response);
                        }
                    };
                }]);

                var layout = "tpl/app.html";
                if (window.location.href.indexOf("material") > 0) {
                    $urlRouterProvider
                        .otherwise('/app/profile');
                } else {

                    if (angular.isUndefined(localStorage.token) || angular.isUndefined(localStorage.user)) {
                        $urlRouterProvider.otherwise('/access/signin');
                    }
                    else {
                        $urlRouterProvider.otherwise('/app/member');
                    }

                }

                $stateProvider
                    .state('app', {
                        abstract: true,
                        url: '/app',
                        templateUrl: layout,
                        controller: 'MainAppController',
                        resolve: load(['js/controllers/mainapp.js'])
                    })
                    .state('app.dashboard', {
                        url: '/dashboard',
                        templateUrl: 'views/dashboard.html',
                        resolve: load(['js/controllers/dashboard.js', 'ui.select'])
                    })
                    // .state('app.dashboard2', {
                    //     url: '/dashboard2',
                    //     templateUrl: 'views/dashboard2.html',
                    //     resolve: load(['ui.select', 'js/controllers/dashboard2.js'])
                    // })
                    .state('app.userProfile', {
                        url: '/profile',
                        templateUrl: 'views/profile.html',
                        resolve: load(['js/controllers/user-profile.js'])
                    })
                    .state('app.sites', {
                        url: '/sites',
                        templateUrl: 'views/site/index.html',
                        resolve: load(['js/controllers/SiteController.js'])
                    })
                    .state('app.sites_new', {
                        url: '/sites/new',
                        templateUrl: 'views/site/form.html',
                        resolve: load(['js/controllers/SiteController.js'])
                    })
                    .state('app.sites_edit', {
                        url: '/sites/edit/:id',
                        templateUrl: 'views/site/form.html',
                        resolve: load(['js/controllers/SiteController.js'])
                    })
                    .state('app.users', {
                        url: '/users',
                        templateUrl: 'views/user/index.html',
                        resolve: load(['js/controllers/UserController.js'])
                    })
                    .state('app.users_new', {
                        url: '/users/new',
                        templateUrl: 'views/user/form.html',
                        resolve: load(['js/controllers/UserController.js'])
                    })
                    .state('app.users_edit', {
                        url: '/users/edit/:id',
                        templateUrl: 'views/user/form.html',
                        resolve: load(['js/controllers/UserController.js'])
                    })
                    .state('app.attendants', {
                        url: '/attendants',
                        templateUrl: 'views/attendant/index.html',
                        resolve: load(['angularFileUpload', 'js/controllers/AttendantController.js'])
                    })
                    .state('app.attendants_new', {
                        url: '/attendants/new',
                        templateUrl: 'views/attendant/form.html',
                        resolve: load(['angularFileUpload', 'js/controllers/AttendantController.js'])
                    })
                    .state('app.attendants_edit', {
                        url: '/attendants/edit/:id',
                        templateUrl: 'views/attendant/form.html',
                        resolve: load(['angularFileUpload', 'js/controllers/AttendantController.js'])
                    })
                    .state('app.jobs', {
                        url: '/jobs',
                        templateUrl: 'views/job/index.html',
                        resolve: load(['js/controllers/JobController.js'])
                    })
                    .state('app.jobs_new', {
                        url: '/jobs/new',
                        templateUrl: 'views/job/form.html',
                        resolve: load(['js/controllers/JobController.js'])
                    })
                    .state('app.jobs_edit', {
                        url: '/job/edit/:id',
                        templateUrl: 'views/job/form.html',
                        resolve: load(['js/controllers/JobController.js'])
                    })
                    .state('app.locations', {
                        url: '/locations',
                        templateUrl: 'views/location/index.html',
                        resolve: load(['js/controllers/LocationController.js'])
                    })
                    .state('app.locations_new', {
                        url: '/locations/new',
                        templateUrl: 'views/location/form.html',
                        resolve: load(['js/controllers/LocationController.js'])
                    })
                    .state('app.locations_edit', {
                        url: '/locations/edit/:id',
                        templateUrl: 'views/location/form.html',
                        resolve: load(['js/controllers/LocationController.js'])
                    })
                    .state('app.contacts', {
                        url: '/contacts',
                        templateUrl: 'views/contacts/index.html',
                        resolve: load(['js/controllers/ContactController.js'])
                    })
                    .state('app.contacts_new', {
                        url: '/contacts/new',
                        templateUrl: 'views/contacts/form.html',
                        resolve: load(['js/controllers/ContactController.js']),
                    })
                    .state('app.contacts_edit', {
                        url: '/contacts/edit/:id',
                        templateUrl: 'views/contacts/form.html',
                        resolve: load(['js/controllers/ContactController.js']),
                    })
                    .state('app.suppliers', {
                        url: '/suppliers',
                        templateUrl: 'views/suppliers/index.html',
                        resolve: load(['js/controllers/supplierController.js'])
                    })
                    .state('app.suppliers_new', {
                        url: '/suppliers/new',
                        templateUrl: 'views/suppliers/form.html',
                        resolve: load(['js/controllers/supplierController.js']),
                    })
                    .state('app.suppliers_edit', {
                        url: '/suppliers/edit/:id',
                        templateUrl: 'views/suppliers/form.html',
                        resolve: load(['js/controllers/supplierController.js']),
                    })
                    .state('app.grades', {
                        url: '/grades',
                        templateUrl: 'views/grade/index.html',
                        resolve: load(['js/controllers/GradeController.js'])
                    })
                    .state('app.grades_new', {
                        url: '/grades/new',
                        templateUrl: 'views/grade/form.html',
                        resolve: load(['js/controllers/GradeController.js']),
                    })
                    .state('app.grades_edit', {
                        url: '/grades/edit/:id',
                        templateUrl: 'views/grade/form.html',
                        resolve: load(['js/controllers/GradeController.js']),
                    })
                    .state('app.tags', {
                        url: '/tags',
                        templateUrl: 'views/tag/index.html',
                        resolve: load(['js/controllers/TagController.js'])
                    })
                    .state('app.tags_new', {
                        url: '/tags/new',
                        templateUrl: 'views/tag/form.html',
                        resolve: load(['js/controllers/TagController.js'])
                    })
                    .state('app.tags_edit', {
                        url: '/tags/edit/:id',
                        templateUrl: 'views/tag/form.html',
                        resolve: load(['js/controllers/TagController.js'])
                    })
                    .state('app.hoses', {
                        url: '/hoses',
                        templateUrl: 'views/hose/index.html',
                        resolve: load(['js/controllers/HosesController.js'])
                    })
                    .state('app.hoses_new', {
                        url: '/hoses/new',
                        templateUrl: 'views/hose/form.html',
                        resolve: load(['js/controllers/HosesController.js'])
                    })
                    .state('app.hoses_edit', {
                        url: '/hoses/edit/:id',
                        templateUrl: 'views/hose/form.html',
                        resolve: load(['js/controllers/HosesController.js'])
                    })
                    .state('app.pumps', {
                        url: '/pumps',
                        templateUrl: 'views/pump/index.html',
                        resolve: load(['js/controllers/PumpController.js'])
                    })
                    .state('app.pumps_new', {
                        url: '/pumps/new',
                        templateUrl: 'views/pump/form.html',
                        resolve: load(['js/controllers/PumpController.js'])
                    })
                    .state('app.pumps_edit', {
                        url: '/pumps/edit/:id',
                        templateUrl: 'views/pump/form.html',
                        resolve: load(['js/controllers/PumpController.js'])
                    })
                    .state('app.vehicles', {
                        url: '/vehicles',
                        templateUrl: 'views/vehicle/index.html',
                        resolve: load(['angularFileUpload', 'js/controllers/VehicleController.js'])
                    })
                    .state('app.vehicles_new', {
                        url: '/vehicles/new',
                        templateUrl: 'views/vehicle/form.html',
                        resolve: load(['angularFileUpload', 'js/controllers/VehicleController.js'])
                    })
                    .state('app.vehicles_edit', {
                        url: '/vehicles/edit/:id',
                        templateUrl: 'views/vehicle/form.html',
                        resolve: load(['angularFileUpload', 'js/controllers/VehicleController.js'])
                    })
                    .state('app.tanks', {
                        url: '/tanks',
                        templateUrl: 'views/tank/index.html',
                        resolve: load(['js/controllers/TankController.js'])
                    })
                    .state('app.tanks_new', {
                        url: '/tanks/new',
                        templateUrl: 'views/tank/form.html',
                        resolve: load(['js/controllers/TankController.js'])
                    })
                    .state('app.tanks_edit', {
                        url: '/tanks/edit/:id',
                        templateUrl: 'views/tank/form.html',
                        resolve: load(['js/controllers/TankController.js'])
                    })
                    .state('app.transactions', {
                        url: '/transactions',
                        templateUrl: 'views/transaction/index.html',
                        resolve: load(['js/controllers/TransactionController.js'])
                    })
                    .state('app.transactions_new', {
                        url: '/transactions/new',
                        templateUrl: 'views/transaction/form.html',
                        resolve: load(['js/controllers/TransactionController.js'])
                    })
                    .state('app.transactions_edit', {
                        url: '/transactions/edit/:id',
                        templateUrl: 'views/transaction/form.html',
                        resolve: load(['js/controllers/TransactionController.js'])
                    })
                    .state('app.atg_readings', {
                        url: '/manual-tank-readings',
                        templateUrl: 'views/atg_readings/index.html',
                        resolve: load(['js/controllers/ATGReadingsController.js'])
                    })
                    .state('app.atg_readings_new', {
                        url: '/manual-tank-readings/new',
                        templateUrl: 'views/atg_readings/form.html',
                        resolve: load(['js/controllers/ATGReadingsController.js'])
                    })
                    .state('app.atg_readings_edit', {
                        url: '/manual-tank-readings/edit/:id',
                        templateUrl: 'views/atg_readings/form.html',
                        resolve: load(['js/controllers/ATGReadingsController.js'])
                    })
                    .state('app.atg', {
                        url: '/atg',
                        templateUrl: 'views/ATG/index.html',
                        resolve: load(['js/controllers/ATGController.js'])
                    })
                    .state('app.atg_new', {
                        url: '/atg/new',
                        templateUrl: 'views/ATG/form.html',
                        params: {
                          site_id: null,
                        },
                        resolve: load(['js/controllers/ATGController.js'])
                    })
                    .state('app.atg_edit', {
                        url: '/atg/edit/:id',
                        templateUrl: 'views/ATG/form.html',
                        resolve: load(['js/controllers/ATGController.js'])
                    })
                    .state('app.fuel_drop', {
                        url: '/fuel-drop',
                        templateUrl: 'views/fuel_drops/index.html',
                        resolve: load(['js/controllers/FuelDropController.js'])
                    })
                    .state('app.fuel_drop_new', {
                        url: '/fuel-drop/new',
                        templateUrl: 'views/fuel_drops/form.html',
                        resolve: load(['js/controllers/FuelDropController.js'])
                    })
                    .state('app.fuel_drop_edit', {
                        url: '/fuel-drop/edit/:id',
                        templateUrl: 'views/fuel_drops/form.html',
                        resolve: load(['js/controllers/FuelDropController.js'])
                    })
                    .state('app.fuel_transfer', {
                        url: '/fuel-transfer',
                        templateUrl: 'views/fuel_transfer/index.html',
                        resolve: load(['js/controllers/FuelTransferController.js'])
                    })
                    .state('app.fuel_transfer_new', {
                        url: '/fuel-transfer/new',
                        templateUrl: 'views/fuel_transfer/form.html',
                        resolve: load(['js/controllers/FuelTransferController.js'])
                    })
                    .state('app.fuel_transfer_edit', {
                        url: '/fuel-transfer/edit/:id',
                        templateUrl: 'views/fuel_transfer/form.html',
                        resolve: load(['js/controllers/FuelTransferController.js'])
                    })
                    .state('app.payments', {
                        url: '/payment',
                        templateUrl: 'views/payments/index.html',
                        resolve: load(['js/controllers/PaymentController.js'])
                    })
                    .state('app.payments_new', {
                        url: '/payment/new',
                        templateUrl: 'views/payments/form.html',
                        resolve: load(['js/controllers/PaymentController.js'])
                    })
                    .state('app.payments_edit', {
                        url: '/payment/edit/:id',
                        templateUrl: 'views/payments/form.html',
                        resolve: load(['js/controllers/PaymentController.js'])
                    })
                    .state('app.fuel_adjustment', {
                        url: '/fuel-adjustment',
                        templateUrl: 'views/fuel_adjustment/index.html',
                        resolve: load(['js/controllers/FuelAdjustmentController.js'])
                    })
                    .state('app.fuel_adjustment_new', {
                        url: '/fuel-adjustment/new',
                        templateUrl: 'views/fuel_adjustment/form.html',
                        resolve: load(['js/controllers/FuelAdjustmentController.js'])
                    })
                    .state('app.fuel_adjustment_edit', {
                        url: '/fuel-adjustment/edit/:id',
                        templateUrl: 'views/fuel_adjustment/form.html',
                        resolve: load(['js/controllers/FuelAdjustmentController.js'])
                    })
                    .state('app.vehicle_list', {
                        url: '/vehicle-list',
                        templateUrl: 'views/report/vehicle_list.html',
                        resolve: load(['ui.select', 'js/controllers/CustomerReportController.js'])
                    })
                    .state('app.vehicle_transaction', {
                        url: '/vehicle-transaction',
                        templateUrl: 'views/report/vehicle_transaction.html',
                        resolve: load(['ui.select', 'js/controllers/CustomerReportController.js'])
                    })
                    .state('app.customer_list', {
                        url: '/customer-list',
                        templateUrl: 'views/report/customer_list.html',
                        resolve: load(['ui.select', 'js/controllers/CustomerReportController.js'])
                    })

                    .state('app.fuel_consumption_report', {
                        url: '/fuel-consumption-report',
                        templateUrl: 'views/report/FuelConsumptionReports.html',
                        resolve: load(['ui.select', 'js/controllers/FuelConsumptionController.js'])
                    })
                    .state('app.customer_statements', {
                        url: '/customer-statements',
                        templateUrl: 'views/report/customer_statements.html',
                        resolve: load(['ui.select', 'js/controllers/CustomerReportController.js'])
                    })
                    .state('app.vehicle_transaction_summery', {
                        url: '/vehicle-transaction-summery',
                        templateUrl: 'views/report/vehicle_transaction_summery.html',
                        resolve: load(['ui.select', 'js/controllers/CustomerReportController.js'])
                    })
                    .state('app.attendant_list', {
                        url: '/attendant-list',
                        templateUrl: 'views/report/staff/attendant_list.html',
                        resolve: load(['ui.select', 'js/controllers/StaffReportController.js'])
                    })
                    .state('app.supplier_list', {
                        url: '/supplier-list',
                        templateUrl: 'views/report/supplier/supplier_list.html',
                        resolve: load(['ui.select', 'js/controllers/SupplierReportController.js'])
                    })
                    .state('app.supplier_purchase', {
                        url: '/supplier-purchase',
                        templateUrl: 'views/report/supplier/supplier_purchase.html',
                        resolve: load(['ui.select', 'js/controllers/SupplierReportController.js'])
                    })
                    .state('app.stock_adjustment', {
                        url: '/stock-adjustment',
                        templateUrl: 'views/report/stock/stock_adjustment.html',
                        resolve: load(['ui.select', 'js/controllers/StockReportController.js'])
                    })
                    .state('app.stock_level_report', {
                        url: '/fuel-level-report',
                        templateUrl: 'views/report/stock/stock_level_report.html',
                        resolve: load(['ui.select', 'js/controllers/StockReportController.js'])
                    })
                    .state('app.customer_age_analysis', {
                        url: '/customer-age-analysis',
                        templateUrl: 'views/report/financial/customer_age_analysis.html',
                        resolve: load(['ui.select', 'js/controllers/FinancialReportController.js'])
                    })
                    .state('app.supplier_age_analysis', {
                        url: '/supplier-age-analysis',
                        templateUrl: 'views/report/financial/supplier_age_analysis.html',
                        resolve: load(['ui.select', 'js/controllers/FinancialReportController.js'])
                    })
                    .state('app.sales', {
                        url: '/sales',
                        templateUrl: 'views/report/financial/sales.html',
                        resolve: load(['ui.select', 'js/controllers/FinancialReportController.js'])
                    })
                    .state('app.refunds', {
                        url: '/refunds',
                        templateUrl: 'views/report/financial/refunds.html',
                        resolve: load(['ui.select', 'js/controllers/FinancialReportController.js'])
                    })
                    .state('app.voids', {
                        url: '/voids',
                        templateUrl: 'views/report/financial/voids.html',
                        resolve: load(['ui.select', 'js/controllers/FinancialReportController.js'])
                    })
                    .state('app.financial_transaction', {
                        url: '/financial-transaction',
                        templateUrl: 'views/report/financial/transaction.html',
                        resolve: load(['ui.select', 'js/controllers/FinancialReportController.js'])
                    })
                    .state('app.sars_report', {
                        url: '/sars-report',
                        templateUrl: 'views/report/financial/sars.html',
                        resolve: load(['ui.select', 'js/controllers/FinancialReportController.js'])
                    })
                    .state('app.daily_tank_recon', {
                        url: '/daily-tank-recon',
                        templateUrl: 'views/report/fuel/daily_tank_recon.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.atg_data', {
                        url: '/fuel/atg',
                        templateUrl: 'views/report/fuel/atg.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.atg_data_hourly', {
                        url: '/fuel/atg-hourly',
                        templateUrl: 'views/report/fuel/atg_hourly.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.deliveries_per_period', {
                        url: '/deliveries',
                        templateUrl: 'views/report/fuel/deliveries.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.pump_transaction', {
                        url: '/pump-transaction',
                        templateUrl: 'views/report/fuel/pump_transaction.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.dispensed_grade', {
                        url: '/dispensed-grade',
                        templateUrl: 'views/report/fuel/dispensed_grade.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.dispensed_time', {
                        url: '/dispensed-time',
                        templateUrl: 'views/report/fuel/dispensed_time.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.dispensed_hose', {
                        url: '/dispensed-hose',
                        templateUrl: 'views/report/fuel/dispensed_hose.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.dispensed_tank', {
                        url: '/dispensed-tank',
                        templateUrl: 'views/report/fuel/dispensed_tank.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })
                    .state('app.dispensed_attendant', {
                        url: '/dispensed-attendant',
                        templateUrl: 'views/report/fuel/dispensed_attendant.html',
                        resolve: load(['ui.select', 'js/controllers/FuelReportController.js'])
                    })


                    .state('app.members', {
                        url: '/members',
                        middleware: ['authMiddleware'],
                        templateUrl: 'views/members.html',
                        resolve: load(['js/controllers/membersCrud.js'])
                    })
                    .state('app.member_update', {
                        url: '/member/update/:id',
                        middleware: ['authMiddleware'],
                        templateUrl: 'views/member_form.html',
                        resolve: load(['js/controllers/membersCrud.js'])
                    })
                    .state('app.admin_dashboard', {
                        url: '/admin-dashboard',
                        middleware: ['authMiddleware'],
                        templateUrl: 'views/admin_dashboard.html',
                        resolve: load(['js/controllers/admin_dashboard.js'])
                    })
                    .state('app.dashboard-v1', {
                        url: '/dashboard-v1',
                        templateUrl: 'tpl/app_dashboard_v1.html',
                        resolve: load(['js/controllers/chart.js'])
                    })
                    .state('access', {
                        url: '/access',
                        template: '<div ui-view class="fade-in-right-big smooth"></div>'
                    })
                    .state('access.signin', {
                        url: '/signin',
                        templateUrl: 'tpl/page_signin.html',
                        resolve: load(['js/controllers/signin.js'])
                    })
                    .state('access.signup', {
                        url: '/signup',
                        templateUrl: 'tpl/page_signup.html',
                        resolve: load(['js/controllers/signup.js'])
                    })
                    .state('access.forgotpwd', {
                        url: '/forgotpwd',
                        templateUrl: 'tpl/page_forgotpwd.html',
                        resolve: load(['js/controllers/password.js'])
                    })
                    .state('access.resetpassword', {
                        url: '/reset/password',
                        templateUrl: 'tpl/page_resetpwd.html',
                        resolve: load(['js/controllers/password.js'])
                    })
                    .state('access.404', {
                        url: '/404',
                        templateUrl: 'tpl/page_404.html'
                    })
                    .state('app.profile', {
                        url: '/profile',
                        templateUrl: 'tpl/front/profile_page.html',
                        resolve: load(['js/controllers/front/profile.js'])
                    })

                    .state('layout', {
                        abstract: true,
                        url: '/layout',
                        templateUrl: 'tpl/layout.html'
                    })
                    .state('layout.fullwidth', {
                        url: '/fullwidth',
                        views: {
                            '': {
                                templateUrl: 'tpl/layout_fullwidth.html'
                            },
                            'footer': {
                                templateUrl: 'tpl/layout_footer_fullwidth.html'
                            }
                        },
                        resolve: load(['js/controllers/vectormap.js'])
                    })
                    .state('layout.mobile', {
                        url: '/mobile',
                        views: {
                            '': {
                                templateUrl: 'tpl/layout_mobile.html'
                            },
                            'footer': {
                                templateUrl: 'tpl/layout_footer_mobile.html'
                            }
                        }
                    })
                    .state('layout.app', {
                        url: '/app',
                        views: {
                            '': {
                                templateUrl: 'tpl/layout_app.html'
                            },
                            'footer': {
                                templateUrl: 'tpl/layout_footer_fullwidth.html'
                            }
                        },
                        resolve: load(['js/controllers/tab.js'])
                    })
                function load(srcs, callback) {
                    return {
                        deps: ['$ocLazyLoad', '$q',
                            function ($ocLazyLoad, $q) {
                                var deferred = $q.defer();
                                var promise = false;
                                srcs = angular.isArray(srcs) ? srcs : srcs.split(/\s+/);
                                if (!promise) {
                                    promise = deferred.promise;
                                }
                                angular.forEach(srcs, function (src) {
                                    promise = promise.then(function () {
                                        if (JQ_CONFIG[src]) {
                                            return $ocLazyLoad.load(JQ_CONFIG[src]);
                                        }
                                        angular.forEach(MODULE_CONFIG, function (module) {
                                            if (module.name == src) {
                                                name = module.name;
                                            } else {
                                                name = src;
                                            }
                                        });
                                        return $ocLazyLoad.load(name);
                                    });
                                });
                                deferred.resolve();
                                return callback ? promise.then(function () {
                                    return callback();
                                }) : promise;
                            }]
                    }
                }
            }
        ]
    );
