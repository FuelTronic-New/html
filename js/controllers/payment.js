'use strict';

/* Controllers */
// signin controller
app
    .controller('PaymentController', ['$scope', '$http', '$state', '$localStorage', 'CRUD','$location','toaster', function ($scope, $http, $state, $localStorage, CRUD,$location,toaster) {

        $scope.payment={};
        var url='paywithpayeasy';
        var invoice_url='paywithpayeasy';

        $scope.queryData = {
            shortCode: $location.search().shortCode,
            referrer: $location.search().referrer,
            currency: $location.search().currency,
            invoiceNo: $location.search().invoiceNo,
            amount: $location.search().amount,
        };
        $scope.payment.AmountDue = 0;

        $scope.makePaymentFlag = 1;


        $scope.getBasic = function() {
            console.log('firstdataapi/' + $location.search().referrer + '/' + $location.search().invoiceNo);
            CRUD.getAll('firstdataapi/' + $location.search().referrer + '/' + $location.search().invoiceNo).then(function (res) {
                $scope.payment = res.data.apiData;
                $scope.payment.AmountDue = res.data.invoice.invoice.AmountDue;
                $scope.payment.amount=$scope.queryData.amount;
            }, function (res) {
                if (res.status == 400 || res.status == 401) {
                    $scope.makePaymentFlag = 0;
                }
            });
        }
        $scope.getInvoice = function() {

            $scope.OnlineInvoiceUrl =$localStorage.invoiceData.invoiceUrl.invoiceUrl.OnlineInvoices.OnlineInvoice.OnlineInvoiceUrl;
            $scope.invoiceStatus=$localStorage.invoiceData ? true : false;
            $scope.invoiceData=$localStorage.invoiceData || '';


            $scope.showPaymentRow=false;

            if($scope.invoiceData.invoice == 'false')
            {
                $scope.showPaymentRow=true;
            }


            $scope.payeasy=$scope.invoiceData.payeasy;
            $scope.invoice=$scope.invoiceData.invoice ? $scope.invoiceData.invoice : {};

        }
        $scope.reset=function (){
            $scope.payment={};
        };

        $scope.save=function (isVailid){
            if (isVailid) {

                var data = $scope.payment;

                var dataFilter = {
                    shortCode: $location.search().shortCode,
                    referrer: $location.search().referrer,
                    currency: $location.search().currency,
                    invoiceNo: $location.search().invoiceNo,
                    GatewayID: data.GatewayID,
                    Password: data.Password,
                    amount: data.amount,
                    account: data.card_number,
                    expMonth: data.expMonth,
                    expYear: data.expYear,
                    cvv: data.cvv,
                    firstName: data.firstName,
                    lastName: data.lastName,
                    card_street: data.card_street,
                    card_city: data.card_city,
                    card_state: data.card_state,
                    card_zip: data.card_zip
                };


                CRUD.store(url, $.param(dataFilter)).then(function (res) {
                    console.log(res);
                    toaster.pop('success', "", 'Form Submitted.');
                    $localStorage.invoiceData = res.data;
                    $state.go('app1.paymentsuccess', {}, {reload: true});

                },function(res){
                        if (res.status == 400) {
                            angular.forEach(res.data.validation_errors, function (value, key) {
                                toaster.pop('error', '', value[0]);
                            });
                        }
                    }


                );
            }
            else{
                console.log(paymentForm.$error);
                toaster.pop('error','validation fails');
            }
        };


    }]);