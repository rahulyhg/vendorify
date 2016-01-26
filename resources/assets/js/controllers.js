
// ---- controllers ---- //
angular.module('wc')
.controller('SettingsModalCtrl', function ($rootScope, $scope, $modalInstance, $http, $location) {
  
    // Settings modal
    $scope.close = function () {
        $modalInstance.dismiss();
        delete $scope.apiKey;
    };

    // Show users api key
    $scope.showKey = function(){
        $rootScope.loading = true;
        $http.get('/api/key').success(function(res){
           $rootScope.loading = false;
           $scope.apiKey = res.key;
        });
    };

    // Sync google contacts (vendors)
    $scope.sync = function(){
        $rootScope.loading = true;
        $http.get('/api/sync/google').success(function(res){
            window.location.reload();
        }).error(function(err){
            $rootScope.loading = false;
            console.log(err);

            // redirect to signin
            window.location = '/oauth/google';
        });
    };

    // Sync google contacts (vendors)
    $scope.disconnect = function(){
        $rootScope.loading = true;
        $http.get('/api/disconnect/google').success(function(res){
            window.location.reload();
        });
    };

})
.controller('ReportModalCtrl', function ($rootScope, $scope, $modalInstance, $http, $location) {
    $scope.start = new Date();
    $scope.end = new Date();
    $scope.rent = false;
    $scope.success = '';
    $scope.warning = false;

    // Settings modal
    $scope.close = function () {
        $modalInstance.dismiss();
        $scope.success = '';
    };

    // Check report timeframe
    $scope.check = function(){
        var s = $scope.start.getTime()/1000;
        var e = $scope.end.getTime()/1000;
        var max = 86400*60; // 60 days
        if((e-s) > max) {
            $scope.warning = true;
        } else {
            $scope.warning = false;
        }
    };

    // Generate a report
    $scope.generate = function() {

        $rootScope.loading = true;
        $http.post('/api/generate/report', {
            start : $scope.start,
            end : $scope.end,
            rent : $scope.rent
        }).success(function(res){
           $rootScope.loading = false;
           $rootScope.$emit('new-report', res);
           $scope.success = 'Report ID '+ res.report.id +' successfully generated.';
        });

    };

})
.controller('SendReportModalCtrl', function ($rootScope, $scope, $modalInstance, vendorIdx) {
    
    $scope.send = function(){
        $rootScope.$emit('sendReport',{
            vendorIdx : vendorIdx,
            message : $scope.message,
            cc : $scope.cc
        });
        $modalInstance.close();
    };

})
.controller("VendorsCtrlr", ["$rootScope", "$scope", "$routeParams","$location","Vendor",
    function($rootScope, $scope, $routeParams, $location, Vendor) {
        $rootScope.loading = true;

        // TODO sorting / filtering / pagination?
        $scope.vendors = Vendor.query(function(){
            $rootScope.loading = false;
        });

        // show a vendor
        $scope.showVendor = function(id) {
            $location.path('/vendor/'+id);
        };

    }

])
.controller("VendorCtrlr", ["$q", "$rootScope", "$scope", "$routeParams", "$location", "Vendor", "Transaction",
    function($q, $rootScope, $scope, $routeParams, $location, Vendor, Transaction) {
        $rootScope.loading = true;
        $scope.showEdit = false;

        // get vendor
        if($routeParams.vendorId) {
            $scope.vendor = Vendor.get({vendorId:$routeParams.vendorId}, function(){
                $rootScope.loading = false;
            });
        } else {
            $scope.vendor = {
                status : 'pending',
                codes : [],
                transactions : []
            };
            $scope.showEdit = true;
            $rootScope.loading = false;
        }

        // codes
        $scope.code = '';
        $scope.removeCode = function(idx){
            $scope.vendor.codes.splice(idx,1);
        };
        $scope.addCode = function(){
            if(!$scope.code) return;
            $scope.vendor.codes.push({
                vendor_id : $scope.vendor.id,
                name : $scope.code
            });
            $scope.code = '';
        };

        // delete vendor
        $scope.delete = function(){
            if(!confirm('Are you sure you want to delete this vendor?')) return false;

            $rootScope.loading = true;
            $scope.vendor.$remove(function(res){
                $rootScope.loading = false;
                $location.path('/vendors');
            });
        };

        // save vendor
        $scope.save = function(){
            $rootScope.loading = true;

            if($scope.vendor.id) {
                $scope.vendor.$update(function(vendor){
                    $rootScope.loading = false;
                    $scope.showEdit = false;
                });
            } else {
                Vendor.save($scope.vendor, function(vendor){
                    $rootScope.loading = false;
                    $scope.showEdit = false;
                    $scope.vendor = vendor;
                });
            }

        };

        // new transaction
        $scope.newTransaction = function(){

            var date = new Date();
            date.setDate(date.getDate() - 1);

            $scope.vendor.transactions.unshift({
                vendor_id : $scope.vendor.id,
                payment_id : 0,
                code : $scope.vendor.codes[0].name || '',
                quantity : 1,
                gross : 0,
                net : 0,
                discounts : 0,
                custom : 1,
                processed_at : date,
                edit : true
            });

        };

        // save a transaction
        $scope.saveTransaction = function(idx){
            $rootScope.loading = true;
            var transaction = $scope.vendor.transactions[idx];
            delete transaction.edit;

            if(transaction.id) {
                // update tx
                Transaction.update({vendorId: $scope.vendor.id, transactionId: transaction.id}, transaction, function(res){
                    $rootScope.loading = false;
                    if(res.transaction.vendor_id != $scope.vendor.id) {
                        $scope.vendor.transactions.splice(idx, 1);
                    } else {
                        $scope.vendor.transactions[idx] = res.transaction;
                    }
                });
            } else {
                // create tx
                transaction.custom = 1;
                transaction.gross = transaction.net;
                Transaction.save({vendorId: $scope.vendor.id}, transaction, function(res){
                    $rootScope.loading = false;
                    $scope.vendor.transactions[idx] = res.transaction;
                });
            }
            
        };

        // delete a transaction
        $scope.deleteTransaction = function(idx){
            $rootScope.loading = true;
            var transaction = $scope.vendor.transactions[idx];
            
            if(!transaction.custom) return false;
            if(transaction.id) {
                if(!confirm('Are you sure?')) return false;
            }

            if(transaction.id) {
                Transaction.remove({vendorId: $scope.vendor.id, transactionId : transaction.id}, function(){
                    $rootScope.loading = false;
                    $scope.vendor.transactions.splice(idx, 1);
                });
            } else {
                $scope.vendor.transactions.splice(idx, 1);
            }

        };

        // sort transactions
        $scope.transactionDate = new Date();
        $scope.transactionSort = function(){
            $rootScope.loading = true;
            
            var date = $scope.transactionDate ? Math.floor($scope.transactionDate.getTime()/1000) : null;
            var req = {
                vendorId: $scope.vendor.id
            };
            if(date) { req.date = date; }
            $scope.vendor.transactions = Transaction.query(req, function(res){
                $rootScope.loading = false;
            });
        };

    }
])
.controller("ReportsCtrlr", ["$q", "$rootScope", "$scope", "$routeParams","$location","$modal","Report",
    function($q, $rootScope, $scope, $routeParams,$location,$modal,Report) {
        $rootScope.loading = true;
        $scope.reports = [];

        $scope.reports = Report.query(function(res){
            $rootScope.loading = false;
        });

        // generate report modal
        $scope.openModal = function(){
            $modal.open({
              templateUrl: 'templates/modal/reportModal.html',
              controller: 'ReportModalCtrl',
              backdrop: false,
              windowTemplateUrl: 'templates/modal/window.html',
              animation: false
            });
        };

        // report created
        var reportUnbind = $rootScope.$on('new-report', function(e, res){
            var report = res.report;
            //report.data = JSON.parse(report.data); 
            $scope.reports.unshift(report);
        });

        // view report
        $scope.view = function(id) {
            $location.path('/report/'+id);
        };

        // cleanup scope events
        $scope.$on('$destroy', function(){
            reportUnbind();
        });
    }
])
.controller("ReportCtrlr", ["$q", "$rootScope", "$scope", "$routeParams","$http","$modal","$location","Report",
    function($q,$rootScope,$scope,$routeParams,$http,$modal,$location,Report) {
        $rootScope.loading = true;
        $scope.tab = 'vendor';

        $scope.report = Report.get({reportId:$routeParams.reportId}, function(report){
            $rootScope.loading = false;
        });

        $scope.collapse = function(){

            $scope.report.data.vendors.forEach(function(vendor, i){
                if($scope.collapsed) {
                    $scope.report.data.vendors[i].collapsed = false;
                } else {
                    $scope.report.data.vendors[i].collapsed = true;
                }
            });

        };

        $scope.save = function(){
            $rootScope.loading = true;

            Report.update(
                {reportId:$scope.report.id},
                {message: $scope.report.message}, 
                function(report){
                    $rootScope.loading = false;
            });
        };

         // delete report
        $scope.delete = function(){
            if(!confirm('Are you sure you want to delete this report?')) return false;

            $rootScope.loading = true;
            Report.remove({reportId : $scope.report.id}, function(res){
                $rootScope.loading = false;
                $location.path('/reports');
            });
        };

        // generate report modal
        $scope.openModal = function(vendorIdx){
            $modal.open({
              templateUrl: 'templates/modal/sendReportModal.html',
              controller: 'SendReportModalCtrl',
              backdrop: false,
              windowTemplateUrl: 'templates/modal/window.html',
              animation: false,
              resolve : {
                vendorIdx : function(){
                    return vendorIdx;
                }
              }
            });
        };

        // send report
        $scope.send = function(e, data) {

            // confirm!
            if($rootScope.loading || !confirm('Are you sure? Report(s) will send immediately!')) {
                return false;
            }

            // loading indicators
            $rootScope.loading = true;
            if(data !== undefined) {
                $scope.report.data.vendors[data.vendorIdx].loading = true;
            } else {
                $scope.report.loading = true;
            }

            // do send request
            $http.post('/api/send/report', {
                reportId : $scope.report.id,
                vendorId : (data!==undefined) ? $scope.report.data.vendors[data.vendorIdx].vendor.id : null,
                message : (data!==undefined) ? data.message : $scope.report.message,
                cc : (data!==undefined) ? data.cc : false
            }).success(function(res){
               $rootScope.loading = false;
               angular.element(e.target).remove();

               if(data !== undefined) {
                    delete $scope.report.data.vendors[data.vendorIdx].loading;
                    $scope.report.data.vendors[data.vendorIdx].sent = true;
                } else {
                    delete $scope.report.loading;
                }

            });
        };

        // send report on event
        var reportUnbind = $rootScope.$on('sendReport', $scope.send);

        // cleanup scope events
        $scope.$on('$destroy', function(){
            reportUnbind();
        });

    }
]);