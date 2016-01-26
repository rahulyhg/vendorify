
// ---- config ---- //
angular.module('wc').config(["$httpProvider", function($httpProvider, $rootScope) {

     // ajax global error handler
    $httpProvider.interceptors.push(
        function($q, $rootScope) { 
            return {
                'responseError': function(rejection) {
                   $rootScope.$emit('error', rejection);
                   return $q.reject(rejection);
                }
            };
        });

}]);