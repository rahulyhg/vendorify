
// ---- run ---- //
angular.module('wc').run(["$rootScope", "$location", "$window","$modal",
    function($rootScope, $location, $window, $modal){
        
        // user settings modal
        $rootScope.openSettings = function(){
            $modal.open({
              templateUrl: 'settingsModal.html',
              controller: 'SettingsModalCtrl',
              backdrop: false,
              windowTemplateUrl: 'templates/modal/window.html',
              animation: false
            });
        };

        // global errors
        $rootScope.error = {};
        $rootScope.$on('error', function(e, data){
            console.log(data.data);
            $rootScope.error.message = data.data.msg || data.statusText;
            $rootScope.error.show = true;
            $rootScope.loading = false;
        });

        $rootScope.dismissError = function(){
            $rootScope.error.show = false;
        };

        // route events
        $rootScope.$on("$routeChangeStart", function(e, data) {
            $rootScope.loading = true;
        });

        $rootScope.$on("$routeChangeSuccess", function(e, data) {
            $rootScope.loading = false;
            if (data.controller) $rootScope.controller = data.controller.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
        });

    }
]);

// ---- routes ---- //
angular.module('wc').config(["$routeProvider",
    function($routeProvider) {

        $routeProvider.when("/", {
            templateUrl: "/templates/vendors.html",
            controller: "VendorsCtrlr"
        }).when("/vendors", {
            templateUrl: "/templates/vendors.html",
            controller: "VendorsCtrlr"
        }).when("/vendor/:vendorId", {
            templateUrl: "/templates/vendor.html",
            controller: "VendorCtrlr"
        }).when("/vendor", {
            templateUrl: "/templates/vendor.html",
            controller: "VendorCtrlr"
        }).when("/reports", {
            templateUrl: "/templates/reports.html",
            controller: "ReportsCtrlr"
        }).when("/report/:reportId", {
            templateUrl: "/templates/report.html",
            controller: "ReportCtrlr"
        }).otherwise({
            templateUrl: "/templates/404.html",
        });
    }
]);