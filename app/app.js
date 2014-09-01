var app = angular.module('mgcrea.ngStrapDocs', ['ngRoute', 'ngAnimate', 'ngSanitize', 'mgcrea.ngStrap']);
app.factory("services", ['$http', function($http) {
        var serviceBase = '/bom/services/';
        var obj = {};
        obj.getParts = function() {
            return $http.get(serviceBase + 'parts');
        };
        obj.getPart = function(id) {
            return $http.get(serviceBase + 'part?id=' + id);
        };

        obj.createPart = function(model) {
            return $http.post(serviceBase + 'createPart', model).then(function(results) {
                return results;
            });
        };

        obj.updatePart = function(id, model) {
            return $http.post(serviceBase + 'updatePart', {id: id, part: model}).then(function(status) {
                return status.data;
            });
        };

        obj.deletePart = function(id) {
            return $http.delete(serviceBase + 'deletePart?id=' + id).then(function(status) {
                return status.data;
            });
        };

        obj.getBoms = function() {
            return $http.get(serviceBase + 'boms');
        };

        obj.getBom = function(id) {
            return $http.get(serviceBase + 'bom?id=' + id);
        };

        obj.createBom = function(model) {
            return $http.post(serviceBase + 'createBom', model).then(function(results) {
                return results;
            });
        };

        obj.updateBom = function(id, model) {
            return $http.post(serviceBase + 'updateBom', {id: id, bom: model}).then(function(status) {
                return status.data;
            });
        };

        obj.deleteBom = function(id) {
            return $http.delete(serviceBase + 'deleteBom?id=' + id).then(function(status) {
                return status.data;
            });
        };
        
        obj.addBomItem = function(model) {
            return $http.post(serviceBase + 'updateBomItem', model).then(function(results) {
                return results;
            });
        };


        return obj;
    }]);

app.controller('partListCtrl', function($scope, services) {
    refresh();
    function refresh() {
        services.getParts().then(function(resp) {
            $scope.partList = resp.data;
        });
    }

    $scope.deletePart = function(data) {

        if (confirm("Are you sure to delete part code: " + data.itemCode) == true) {

            services.deletePart(data.id).then(function() {
                refresh();
            });
        }
    };
});


app.controller('bomAddPartsCtrl', function($scope, $filter, $routeParams, services) {
    refresh();
    function refresh() {
        services.getParts().then(function(resp) {
            $scope.partList = resp.data;
        });
    }
    
    $scope.selectedParts = [];
    
    $scope.addPart = function(data){
        data.qty = "";
        $scope.selectedParts.push(angular.copy(data));
        ($filter('filter')($scope.partList, {id:data.id})[0]).selected = true;
        
    };

    $scope.removePart = function(data){
        
        var partRemove = $filter('filter')($scope.selectedParts, {id:data.id})[0];
        $scope.selectedParts.splice(partRemove, 1);
        
        ($filter('filter')($scope.partList, {id:data.id})[0]).selected = false;
    };
    
    $scope.submitClick = function(){
        var data = {
            bomId : $routeParams.bomId,
            partList : $scope.selectedParts
        };
        services.addBomItem(data).then(function() {
            
        });
    };
});

app.controller('partDetailCtrl', function($scope, $rootScope, $location, $routeParams, services, part) {

    var partId = ($routeParams.partId) ? parseInt($routeParams.partId) : 0;
    $rootScope.title = (partId > 0) ? 'Edit Part' : 'Add Part';
    $scope.buttonText = (partId > 0) ? 'Update' : 'Create';

    var original = part.data;
    original._id = partId;
    $scope.part = angular.copy(original);
    $scope.part._id = partId;

    $scope.isClean = function() {
        return angular.equals(original, $scope.part);
    };

    $scope.savePart = function(part) {
        if (partId <= 0) {
            services.createPart(part).then(function() {
                $location.path('/part-list');
            });
        }
        else {
            services.updatePart(partId, part).then(function() {
                $location.path('/part-list');
            });
        }

    };
});

app.controller('bomListCtrl', function($scope, services) {
    refresh();
    function refresh() {
        services.getBoms().then(function(resp) {
            $scope.bomList = resp.data;
        });
    }

    $scope.deleteBom = function(data) {
        if (confirm("Are you sure to delete bom : " + data.name) == true) {
            services.deleteBom(data.id).then(function() {
                refresh();
            });
        }
    };



});

app.controller('bomDetailCtrl', function($scope, $rootScope, $location, $routeParams, services,bom) {
    var bomId = ($routeParams.bomId) ? parseInt($routeParams.bomId) : 0;
    $rootScope.title = (bomId > 0) ? 'Edit BOM' : 'Create New BOM';
    $scope.buttonText = (bomId > 0) ? 'Update' : 'Create';
    
    var original = bom.data;
    original._id = bomId;
    $scope.bom = angular.copy(original);
    $scope.bom._id = bomId;

    

    $scope.isClean = function() {
        return angular.equals(original, $scope.bom);
    };

    $scope.saveBom = function(model) {
        if (bomId <= 0) {
            services.createBom(model).then(function() {
                $location.path('/');
            });
        }
        else {
            services.updateBom(bomId, model).then(function() {
                $location.path('/');
            });
        }
    };
    
    $scope.bom.partList = [];
    
    $scope.addPart = function(part) {
        $scope.bom.partList.push(angular.copy(part));
        part.id = '';
        part.qty = '';
    };
});

app.controller('componentCtrl', function($scope) {
    $scope.modal = {
        title: "Title",
        content: "Hello Modal<br />This is a multiline message!",
        backdrop: "false",
        animation: 'am-fadeAndSlideLeft',
        placement: 'left'
    };

    $scope.alert = {title: 'Holy guacamole!', content: 'Best check yo self, you\'re not looking too good.', type: 'info'};


});

app.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
                when('/part-list', {
                    title: 'PartList',
                    templateUrl: 'partials/part/part-list.html',
                    controller: 'partListCtrl'
                })
                .when('/bom/detail/:bomId/add-parts', {
                    title: 'PartList',
                    templateUrl: 'partials/bom/bom-add-parts.html',
                    controller: 'bomAddPartsCtrl'
                })
                .when('/component', {
                    title: 'Component',
                    templateUrl: 'partials/component/component.html',
                    controller: 'componentCtrl'
                })
                .when('/part-detail/:partId', {
                    title: 'Part Detail',
                    templateUrl: 'partials/part/part-detail.html',
                    controller: 'partDetailCtrl',
                    resolve: {
                        part: function(services, $route) {
                            var partId = $route.current.params.partId;
                            return services.getPart(partId);
                        }
                    }
                })
                .when('/', {
                    title: 'Bom',
                    templateUrl: 'partials/bom/bom-list.html',
                    controller: 'bomListCtrl'
                })
                .when('/bom/detail/:bomId', {
                    title: 'BOM Detail',
                    templateUrl: 'partials/bom/bom-detail.html',
                    controller: 'bomDetailCtrl',
                    resolve: {
                        bom: function(services, $route) {
                            var bomId = $route.current.params.bomId;
                            if (bomId > 0) {
                                return services.getBom(bomId);
                            }
                             else {
                                 return { data: '' };
                            }
                        }
                    }
                })
                .otherwise({
                    redirectTo: '/'
                });
    }]);

app.run(['$location', '$rootScope', function($location, $rootScope) {
        $rootScope.$on('$routeChangeSuccess', function(event, current, previous) {
            $rootScope.title = current.$$route.title;
        });
    }]);

