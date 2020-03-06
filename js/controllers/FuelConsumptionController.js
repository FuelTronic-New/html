app.controller('FuelConsumptionController', function ($scope, $rootScope, $http, CRUD, toaster,$filter, $localStorage) {

    var listurl = 'customervehicles';
    var customervehiclesreporturl = 'customervehiclesreport';
    var getVehicleTransactionListurl = 'customervehicletransactionsreport';
    var customerlistreporturl = 'Fuel_Report';
    var customerstatementsreporturl = 'customerstatementsreport';
    var vehicletransactionsreporturl = 'vehicletransactionsreport';

    $scope.disabled = undefined;
    $scope.searchEnabled = undefined;
    $scope.person = {};
    $scope.vehiclesData = [];
    $scope.customersData = [];
    $scope.test = {};
     $scope.cust_id=[];
$scope.vel=[];
         $scope.currentDate = new Date();


    $scope.getAllSites = function () {
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
                $scope.customersData = $scope.selectedSite.customers;
                $scope.vehiclesData = $scope.selectedSite.vehicles;
            }
        }

        function failed(error) {
            //toaster.pop('error','',error.data.message);
        }
    };


    $scope.changeSite = function () {
        //$scope.selectedCustomers.selected = undefined;
        $scope.test = {};
        $rootScope.siteData = $scope.selectedSite;
        $localStorage.site_id = $scope.selectedSite.id;
        $scope.customersData = $scope.selectedSite.customers;
        $scope.vehiclesData = $scope.selectedSite.vehicles;
//        console.log($scope.vehiclesData);
    }

/*    $scope.cust=[];
    $rootScope.getMultiSelectIds($scope.test.selectedCustomers,$scope.cust);
    alert($scope.cust);*/
     $scope.GetValue = function () {
    $scope.cust_id.length=0;
    $scope.vel.length=0;  
    var fruitId = $scope.test.selectedCustomers;
  fruitId.forEach(function(element) {
  $scope.cust_id.push(element.id);

}
);
//console.log($scope.cust_id);
        for(var i=0;i<$scope.cust_id.length;i++){
  $scope.vehiclesData.forEach(function(element) {
if(element.customer_id==$scope.cust_id[i]){
 $scope.vel.push(element);
}
});

        }
 //console.log($scope.vel);  

              }
    $scope.getVehicleList = function () {
           
        $scope.customerIds = [];
        $scope.vehicleIds = [];
        $rootScope.getMultiSelectIds($scope.test.selectedCustomers, $scope.customerIds);
        $rootScope.getMultiSelectIds($scope.test.selectedVehicles, $scope.vehicleIds);

        var filterData = {
            site_id: $scope.selectedSite.id,
            customers: $scope.customerIds.toString(),
            vehicles: $scope.vehicleIds.toString(),
            start_date: ($scope.dateRangeStart) ? moment($scope.dateRangeStart).format('YYYY-MM-DD HH:mm:ss') : '',
            end_date: ($scope.dateRangeEnd) ? moment($scope.dateRangeEnd).format('YYYY-MM-DD HH:mm:ss') : '',
             date_range: $scope.date_range,
        };
          $(document).find('#fuel_consumption_report').DataTable().destroy();
          $(document).find('#fuel_consumption_report').DataTable({
                   "processing": true,
              "searchable": true,
              "order": [[ 8, "desc" ]],
              // "lengthMenu": [ [10, 100, 500], [10, 100, 500] ],
              "lengthMenu": [ [1000, 500, 100], [1000, 500, 100] ],
              "dom": 'Bfrtip',
              "buttons": [
                'csv'
               ],

              "ajax": {
                  "url": $rootScope.API_BASE_URL + customerlistreporturl + '?token=' + $localStorage.token,
                  "type": "POST",
                  "data": filterData,
                  complete: function() {
                        $(document).find('.cloneTable').html($('.clonableTable').html());
                  }
              },
            "columns": [
                  {data:null,name:'vehicle',render:function(data){
                    if(data.vehicle==null || data.vehicle==""){
                      return '-';
                    }
                    else{
                      return data.vehicle;                      
                    }
                  }},
                 {data:null,name:'litre',render:function(data){
                    if(data.litre==null || data.litre==""){
                      return '-';
                    }
                    else{
                      return data.litre;                      
                    }
                  }},
                 
                  {data:null,name:'CostPer100Km',render:function(data){
                 
                    if(data.CostPer100Km==null || data.CostPer100Km==""){
                      return '-';
                    }
                    else{
                      return data.CostPer100Km;                      
                    }
                  }},
          {data:null,name:'kmL',render:function(data){
                    if(data.kmL==null || data.kmL==""){
                      return '-';
                    }
                    else{
                      return data.kmL;                      
                    }
                  }},
                
                {data:null,name:'current_cost',render:function(data){
                    if(data.current_cost==null || data.current_cost==""){
                      return '-';
                    }
                    else{
                      return data.current_cost;                      
                    }
                  }}, 
                  {data:null,name:'current_litre',render:function(data){
                    if(data.current_litre==null || data.current_litre==""){
                      return '-';
                    }
                    else{
                      return data.current_litre;                      
                    }
                  }}, 
                  
            {data:null,name:'KmTraveled',render:function(data){
                    if(data.KmTraveled==null || data.KmTraveled==""){
                      return '-';
                    }
                    else{
                      return data.KmTraveled;                      
                    }
                  }}, 
         
                   {data: null, name: 'total_cost',render:function (data) {
                      var num=data.CostPer100Km/100*data.KmTraveled;
                     if(num==""){
                      return '-';
                     }
                     else{
                      return num.toFixed(2);
                    }
                  }},
                 
                  {data:'date',name:'date'}


            ]
        });

        //console.log(filterData);
/*        CRUD.postWithData(customervehiclesreporturl, $.param(filterData)).then(success).catch(failed);
        function success(res) {
            $scope.rowCollection = res.data.data;
            $scope.displayCollection = res.data.data;
            toaster.pop('success', "Vehicles", res.data.message);
        }

        function failed(error) {
        }
  */  }

   
  

 $scope.format = 'dd-MMMM-yyyy';
    $scope.dateTimeNow = function () {
        // $scope.end_date = new Date();
        // $scope.start_date = new Date();
    };
    $scope.dateTimeNow();

     $scope.toggleMinDate = function() {
       $scope.minDate = $scope.minDate ? null : new Date();
     };

     $scope.maxDate = new Date('2014-06-22');
     $scope.toggleMinDate();

     $scope.dateOptions = {
       showWeeks: false
     };

     // Disable weekend selection
     // $scope.disabled = function(calendarDate, mode) {
     //   return mode === 'day' && ( calendarDate.getDay() === 0 || calendarDate.getDay() === 6 );
     // };

     $scope.open = function($event,opened) {
       $event.preventDefault();
       $event.stopPropagation();
       $scope.dateOpened = true;
       console.log('opened');
     };
     $scope.open2 = function($event,opened) {
       $event.preventDefault();
       $event.stopPropagation();
       $scope.dateOpened2 = true;
     };

     $scope.dateOpened = false;
     $scope.hourStep = 1;
     $scope.minuteStep = 1;

     $scope.timeOptions = {
       hourStep: [1, 2, 3],
       minuteStep: [1, 5, 10, 15, 25, 30]
     };

     $scope.showMeridian = true;
    $scope.timeToggleMode = function() {
      $scope.showMeridian = !$scope.showMeridian;
    };
});