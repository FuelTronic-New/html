// config

var app =
angular.module('app')

    .run(['$rootScope', '$injector','$state','$localStorage','toaster','$http', function($rootScope,$injector,$state,$localStorage,toaster,$http) {
       
	   $rootScope.API_BASE_URL='http://laravel.fueltronic.co.za/api/';
	   //$rootScope.API_BASE_URL='http://35.176.250.38/FuelTronicLaravelAPI/public/api/';
        //$rootScope.API_BASE_URL='http://ecistoreapi.test4you.in/api/';
        var date=new Date();
        $rootScope.currentYear=date.getFullYear();

        $rootScope.logout=function(){
            $http.get($rootScope.API_BASE_URL+'logout?token='+ $localStorage.token,{withCredentials:false}).then(function(res){
                if(res.data.status == 'true'){
                    toaster.pop('success','','Logout Successfully.');
                    delete $localStorage.user;
                    delete $localStorage.token;
                    $state.go('access.signin',{},{reload:true});
                }
            },function(){});
            return;
        }

        $rootScope.token= '';

        if($localStorage.user)
        {
            $rootScope.isLoggedIn= true;
            $rootScope.user=$localStorage.user;
        }
        $rootScope.token=$localStorage.token;


        $rootScope.userData=(!!$localStorage.user) ? $localStorage.user : '';
        $rootScope.jwttoken=($localStorage.token) ? $localStorage.token : '';

    }])
    .config(function($httpProvider,$provide){
      $httpProvider.defaults.useXDomain = true;
      $httpProvider.defaults.withCredentials = false;
    })

  .config(
    [        '$controllerProvider', '$compileProvider', '$filterProvider', '$provide',
    function ($controllerProvider,   $compileProvider,   $filterProvider,   $provide) {

        // lazy controller, directive and service
        app.controller = $controllerProvider.register;
        app.directive  = $compileProvider.directive;
        app.filter     = $filterProvider.register;
        app.factory    = $provide.factory;
        app.service    = $provide.service;
        app.constant   = $provide.constant;
        app.value      = $provide.value;
    }
  ])
  .config(['$translateProvider', function($translateProvider){
    // Register a loader for the static files
    // So, the module will search missing translation tables under the specified urls.
    // Those urls are [prefix][langKey][suffix].
    $translateProvider.useStaticFilesLoader({
      prefix: 'l10n/',
      suffix: '.js'
    });
    // Tell the module what language to use by default
    $translateProvider.preferredLanguage('en');
    // Tell the module to store the language in the local storage
    $translateProvider.useLocalStorage();
  }]);

