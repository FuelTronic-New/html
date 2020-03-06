'use strict';

function isEmpty(value) {
    return angular.isUndefined(value) || value === '' || value === null || value !== value;
}
angular.module('app', [
    'ngAnimate',
    'duScroll',
    'toaster',
    'ngAria',
    'ngCookies',
    'ngMessages',
    'ngResource',
    'ngSanitize',
    'ngTouch',
    'ngStorage',
    'ui.router',
    'ui.bootstrap',
    'ui.bootstrap.datetimepicker',
    'ui.utils',
    'ui.load',
    'ui.jq',
    'smart-table',
    'oc.lazyLoad',
    'objectTable',
    'amChartsDirective',
    'pascalprecht.translate',
    'angularUtils.directives.dirPagination',

])
.directive('loading', ['$http', function ($http) {
   return {
       restrict: 'A',
       link: function (scope, elm, attrs) {
           scope.isLoading = function () {
               return $http.pendingRequests.length > 0;
           };

           scope.$watch(scope.isLoading, function (v) {
               if(v) {
                   elm.show();
               } else {
                   elm.hide();
               }
           });
       }
   };

}])
    .filter('localDateTime', function () {
      return function (date, format) {
          var stillUtc = moment.utc(date).toDate();
          return moment(stillUtc).local().format(format);
      }  
    })
	.filter('myCustomDatefilter',function(){
        return function(input)
        {
       var first_space= input.split(" ");
        return first_space[1];
        }
    })
    //.directive('loading', function () {
    //    return {
    //        restrict: 'E',
    //        replace: true,
    //        template: '<div class="loading"><img src="img/fueltronic2.gif"/></div>',
    //        link: function (scope, element, attr) {
    //            scope.$watch('loading', function (val) {
    //                if (val)
    //                {
    //                    $(element).show();
    //                }
    //                else
    //                {
    //                    setInterval(function(){
    //                        $(element).hide();
    //                    },1000);
    //                }
    //            });
    //
    //        }
    //    }
    //})
    .directive('numbersOnly', function(){
       return {
         require: 'ngModel',
         link: function(scope, element, attrs, modelCtrl) {
           modelCtrl.$parsers.push(function (inputValue) {
               if (inputValue == undefined) return ''
               var transformedInput = inputValue.replace(/[^0-9]/g, '');
               if (transformedInput!=inputValue) {
                  modelCtrl.$setViewValue(transformedInput);
                  modelCtrl.$render();
               }

               return transformedInput;
           });
         }
       };
    })
    .directive('onlyDigits', function () {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function (scope, element, attr, ctrl) {
                function inputValue(val) {
                    if (val) {
                        var digits = val.replace(/[^0-9.]/g, '');

                        if (digits.split('.').length > 2) {
                            digits = digits.substring(0, digits.length - 1);
                        }

                        if (digits !== val) {
                            ctrl.$setViewValue(digits);
                            ctrl.$render();
                        }
                        return parseFloat(digits);
                    }
                    return undefined;
                }

                ctrl.$parsers.push(inputValue);
            }
        };
    })
    .directive('ngMax', function () {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function (scope, elem, attr, ctrl) {
                scope.$watch(attr.ngMax, function () {
                    ctrl.$setViewValue(ctrl.$viewValue);
                });
                var maxValidator = function (value) {
                    var max = attr.ngMax || Infinity;
                    if (!isEmpty(value) && parseFloat(value) > max) {
                        ctrl.$setValidity('ngMax', false);
                        return undefined;
                    } else {
                        ctrl.$setValidity('ngMax', true);
                        return value;
                    }
                };
                ctrl.$parsers.push(maxValidator);
                ctrl.$formatters.push(maxValidator);
            }
        };
    })
    .directive("passwordVerify", function () {
        return {
            require: "ngModel",
            scope: {
                passwordVerify: '='
            },
            link: function (scope, element, attrs, ctrl) {
                scope.$watch(function () {
                    var combined;

                    if (scope.passwordVerify || ctrl.$viewValue) {
                        combined = scope.passwordVerify + '_' + ctrl.$viewValue;
                    }
                    return combined;
                }, function (value) {
                    if (value) {
                        ctrl.$parsers.unshift(function (viewValue) {
                            var origin = scope.passwordVerify;
                            if (origin != '' && origin !== viewValue) {
                                ctrl.$setValidity("passwordVerify", false);
                                return undefined;
                            } else {
                                ctrl.$setValidity("passwordVerify", true);
                                return viewValue;
                            }
                        });
                    }
                });
            }
        };
    })
    .constant('stConfig', {
        pagination: {
            template: 'snippets/pagination.html',
            itemsByPage: 10,
            displayedPages: 1
        },
        search: {
            delay: 400, // ms
            inputEvent: 'input'
        },
        select: {
            mode: 'single',
            selectedClass: 'st-selected'
        },
        sort: {
            ascentClass: 'st-sort-ascent',
            descentClass: 'st-sort-descent',
            descendingFirst: false,
            skipNatural: false,
            delay: 300
        },
        pipe: {
            delay: 100 //ms
        }
    })
    .filter('myStrictFilter', function ($filter) {
        return function (input, predicate) {
            return $filter('filter')(input, predicate, true);
        }
    })

    .filter('unique', function () {
        return function (arr, field) {
            var o = {}, i, l = arr.length, r = [];
            for (i = 0; i < l; i += 1) {
                o[arr[i][field]] = arr[i];
            }
            for (i in o) {
                r.push(o[i]);
            }
            return r;
        };
    })
    .filter('dateFormat1', function ($filter) {
        return function (input) {
            if (input == null) {
                return "";
            }
            var _date = $filter('date')(new Date(input), 'MMM-dd-yyyy');
            return _date.toUpperCase();
        };
    })
    .filter('propsFilter', function () {
        return function (items, props) {
            var out = [];

            if (angular.isArray(items)) {
                items.forEach(function (item) {
                    var itemMatches = false;

                    var keys = Object.keys(props);
                    for (var i = 0; i < keys.length; i++) {
                        var prop = keys[i];
                        var text = props[prop].toLowerCase();
                        if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
                            itemMatches = true;
                            break;
                        }
                    }
                    if (itemMatches) {
                        out.push(item);
                    }
                });
            } else {
                // Let the output be the input untouched
                out = items;
            }
            return out;
        };
    })

    .service('CRUD', function ($http, $localStorage, $rootScope) {
        var api_base = $rootScope.API_BASE_URL;

        var resource = {};

        resource.getAll = function (url) {
            var token = $localStorage.token || '';
            return $http({
                method: 'GET',
                url: api_base + url + '?token=' + token,
                withCredentials: true
            });
            return $http.get(api_base + url + '?token=' + token);
        }
        resource.findById = function (url, id) {
            var token = $localStorage.token || '';
            return $http.get(api_base + url + id + '?token=' + token);
        }

        resource.update = function (url, id, data) {
            var token = $localStorage.token || '';
            return $http({
                method: 'POST',
                url: api_base + url + id + '?token=' + token,
                data: data,
                withCredentials: true,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            });
        }
        resource.store = function (url, data) {
            var token = $localStorage.token || '';
            return $http({
                method: 'POST',
                url: api_base + url + '?token=' + token,
                data: data,
                withCredentials: true,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            });
        }
        resource.postWithData = function (url, data) {
            var token = $localStorage.token || '';
            return $http({
                method: 'POST',
                url: api_base + url + '?token=' + token,
                data: data,
                withCredentials: true,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            });
        }
        resource.postWithDataPage = function (url, search, data) {
            var token = $localStorage.token || '';
            return $http({
                method: 'POST',
                url: api_base + url +search+'&token=' + token,
                data: data,
                withCredentials: true,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            });
        }
        resource.storeWithoutToken = function (url, data) {
            var token = $localStorage.token || '';
            return $http({
                method: 'POST',
                url: api_base + url,
                data: data,
                withCredentials: true,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            });
        }
        resource.getWithData = function (url, data) {
            var token = $localStorage.token || '';
            return $http({
                method: 'GET',
                url: api_base + url + '?token=' + token,
                params: data,
                withCredentials: true
            });
        }
        resource.delete = function (url, id) {
            var token = $localStorage.token || '';
            return $http.get(api_base + url + id + '?token=' + token);
        }

        return resource;

    });
