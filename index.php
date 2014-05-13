<!DOCTYPE html>
<html lang="en" ng-app="LifeApp">
    <head>
        <title>Game of Life</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <META NAME="ROBOTS" CONTENT="NOINDEX,NOFOLLOW">
        <META name="keywords" content=""> 
        <META name="description" content=""> 

        <link REL="SHORTCUT ICON" HREF="favicon.ico">
        <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
        <script type="text/javascript" src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.9/angular.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/angular-strap/0.7.4/angular-strap.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.9/angular-sanitize.js"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.1.1/angular-cookies.min.js"></script>
        <script>
            var LifeApp = angular.module('LifeApp', ['ngCookies', '$strap.directives', 'ngSanitize']);

            var Point = function(x, y) {
                this.x = x;
                this.y = y;
            };

            LifeApp
                    .filter('nToBR', function() {
                        return function(text) {
                            return text.replace(/\n/g, '<br/>');
                        };
                    })
                    .directive('eatClick', function() {
                        return function(scope, element, attrs) {
                            $(element).click(function(event) {
                                event.preventDefault();
                            });
                        }
                    })
                    .directive('eatPropagation', function() {
                        return function(scope, element, attrs) {
                            $(element).click(function(event) {
                                event.cancelBubble = true;
                                event.stopPropagation && event.stopPropagation();
                            });
                        }
                    })

            function LifeAppController($scope) {
                $scope.gameInterval = 2000 // 2 sec;
                $scope.gameStatus = 0; // 0 - pause; 1 - play

                $scope.currentStates = {"44": {"43": 1, "44": 1, "49": 1, "50": 1, "51": 0}, "45": {"43": 1, "45": 1, "48": 1, "50": 1}, "46": {"43": 1, "48": 0, "50": 1}, "47": {"48": 0}, "48": {"48": 0}, "49": {"48": 0}, "52": {"43": 1, "44": 0, "45": 0, "50": 1}, "53": {"43": 1, "44": 0, "45": 1, "46": 0, "48": 1, "50": 1}, "54": {"43": 1, "44": 1, "45": 0, "49": 1, "50": 1}}; // x,y position states 1/0 alive/not
                $scope.preliminarStates = {};

                $scope.w = 100;
                $scope.h = 100;
                $scope.s = 5;

                $scope.cellw = 8;
                $scope.cellh = 8;

                $scope.posx = 0; //Math.round(Math.pow(2, 64) / 2);
                $scope.posy = 0; // Math.round(Math.pow(2, 64) / 2);

                $scope.itemsx = [];
                $scope.itemsy = [];

                $scope.life_objects = {};
                $scope.interval;

                $scope.elmscount = 0;



                // init canvas
                $scope.canvas = document.getElementById('lifeCanvas');
                $scope.context = $scope.canvas.getContext('2d');


                $scope.prerun = function() {
                    $scope.gameStatus && $scope.run();
                }

                $scope.run = function() {
                    $scope.gameStatus = 1;
                    $scope.interval && clearInterval($scope.interval);
                    $scope.interval = setInterval($scope.draw, Math.round($scope.gameInterval / $scope.s));
                }

                $scope.restartScene = function() {
                    $scope.canvas.width = $scope.cellw * $scope.w;
                    $scope.canvas.height = $scope.cellh * $scope.h;

                    var canvasOffset = $($scope.canvas).offset();
                    $scope.offsetX = canvasOffset.left;
                    $scope.offsetY = canvasOffset.top;


                    $scope.drawGrid();
                    $scope.redraw();
                }

                $scope.handleMouseDown = function(e) {
                    var alreadyHandeled = {};

                    var getCellXY = function(event) {
                        var mouseX = parseInt(event.pageX - $scope.offsetX),
                                mouseY = parseInt(event.pageY - $scope.offsetY);
                        var x = Math.floor((mouseX - $scope.posx * $scope.cellw) / $scope.cellw),
                                y = Math.floor((mouseY - $scope.posy * $scope.cellh) / $scope.cellh);

                        return new Point(x, y);
                    }
                    var handleEvent = function(point) {
                        if (!alreadyHandeled[point.x] || !alreadyHandeled[point.x][point.y]) {
                            $scope.toggleLife(point.x, point.y);
                            $scope.renderCell(point.x, point.y);
                            if (!alreadyHandeled[point.x])
                                alreadyHandeled[point.x] = {};
                            alreadyHandeled[point.x][point.y] = 1;
                        }
                    }

                    $($scope.canvas).mousemove(function(e) {
                        handleEvent(getCellXY(e));
                    });

                    handleEvent(getCellXY(e));
                }

                // handle MouseDown Event
                $($scope.canvas).mousedown($scope.handleMouseDown);

                $('body').mouseup(function(e) {
                    $($scope.canvas).unbind('mousemove');
                });
                
                $(window).resize(function(e) {
                    $scope.restartScene();
                });


                $scope.renderCell = function(x, y, forceColor) {
                    $scope.context.beginPath();
                    $scope.context.lineWidth = 0;

                    var startx = (x * 1 + $scope.posx) * $scope.cellw;
                    var starty = (y * 1 + $scope.posy) * $scope.cellh;

                    $scope.context.rect(startx + 1, starty + 1, $scope.cellw - 2, $scope.cellh - 2);
                    $scope.context.fillStyle = (forceColor ? forceColor : ($scope.currentStates[x] && $scope.currentStates[x][y] == 1 ? 'black' : 'white'));
                    $scope.context.fill();
                };

                $scope.drawGrid = function() {
                    $scope.context.strokeStyle = "black";
                    $scope.context.lineWidth = .5;
                    for (var x = 0; x <= $scope.w; x++) {
                        $scope.context.beginPath();
                        $scope.context.moveTo(x * $scope.cellw, 0);
                        $scope.context.lineTo(x * $scope.cellw, $scope.canvas.height);
                        $scope.context.stroke();
                    }
                    $scope.context.strokeStyle = "black";
                    for (var y = 0; y <= $scope.h; y++) {
                        $scope.context.beginPath();
                        $scope.context.moveTo(0, y * $scope.cellh);
                        $scope.context.lineTo($scope.canvas.width, y * $scope.cellh);
                        $scope.context.stroke();
                    }
                }


                $scope.pause = function() {
                    $scope.gameStatus = 0;
                    $scope.interval && clearInterval($scope.interval);
                }

                $scope.toggleLife = function(x, y) {
                    if (!$scope.currentStates[x]) {
                        $scope.currentStates[x] = {};
                    }
                    $scope.currentStates[x][y] = $scope.currentStates[x][y] == 1 ? 0 : 1;

                    $scope.elmscount = $scope.elmscount + ($scope.currentStates[x][y] ? 1 : -1);

                    setTimeout(function() {
                        $scope.$apply();
                    });
                }

                $scope.redraw = function() {
                    angular.forEach($scope.currentStates, function(alivex, x) {
                        angular.forEach($scope.currentStates[x], function(alivey, y) {
                            $scope.renderCell(x, y);
                        })
                    })
                }

                $scope.draw = function(force) {
                    if ($scope.gameStatus || force) { // if not paused
                        $scope.preliminarStates = {};
                        $scope.context.clearRect(0, 0, $scope.canvas.width, $scope.canvas.height);
                        
                        var copy = $scope.currentStates;
                        angular.forEach(copy, function(alivex, x) {
                            angular.forEach(copy[x], function(alivey, y) {
                                $scope.countEveryOne(x, y);
                            });
                        });

                        $scope.currentStates = $scope.preliminarStates;

                        $scope.elmscount = 0;
                        angular.forEach($scope.currentStates, function(alivex, x) {
                            angular.forEach($scope.currentStates[x], function(alivey, y) {
                                if (!alivey || alivey == 2) {
                                    delete $scope.currentStates[x][y];
                                    var cnt = 0;
                                    angular.forEach($scope.currentStates[x], function(d, di) {
                                        cnt++;
                                        return false;
                                    })
                                    if (cnt == 0) {
                                        delete $scope.currentStates[x];
                                    }
                                } else {
                                    $scope.elmscount++;
                                }
                                $scope.renderCell(x, y);
                            });
                        });
                        
                        $scope.drawGrid();
                    }
                }

                $scope.getNeighboursListScore = function(x, y, skip_neighbours) {
                    var score = 0;
                    for (var i = 1; i <= 3; i++) {
                        for (var j = 1; j <= 3; j++) {
                            if (!(i == 2 && j == 2)) {
                                var target = {x: Number(x) + i - 2, y: Number(y) + j - 2};
                                var locscore = ($scope.currentStates[target.x] == null || $scope.currentStates[target.x][target.y] == null || !$scope.currentStates[target.x][target.y]) ? 0 : 1; // $scope.countEveryOne(x - i, y - j);
                                if (!skip_neighbours && ($scope.currentStates[target.x] == null || !$scope.currentStates[target.x][target.y])) {
                                    $scope.countEveryOne(target.x, target.y, 1);
                                }
                                score += locscore;
                            }
                        }
                    }

                    return score;
                }

                $scope.setPreliminarState = function(x, y, value) {
                    if (!$scope.preliminarStates[x])
                        $scope.preliminarStates[x] = {};
                    $scope.preliminarStates[x][y] = value;
                }

                $scope.countEveryOne = function(x, y, skip_neighbours) {
                    if ($scope.preliminarStates[x] == null || $scope.preliminarStates[x][y] == null) {
                        $scope.setPreliminarState(x, y, 2);
//                        $scope.renderCell(x, y, 'red');
                        var nbScore = $scope.getNeighboursListScore(x, y, skip_neighbours); // neighbours score;
                        if (!$scope.currentStates[x] || !$scope.currentStates[x][y]) {
                            if (nbScore == 3) {
                                $scope.setPreliminarState(x, y, 1);
                            } else {
                                $scope.setPreliminarState(x, y, null);
                            }
                        } else if ($scope.currentStates[x][y] == 1) {
                            if ([3, 2].indexOf(nbScore) != -1) {
                                $scope.setPreliminarState(x, y, 1);
                            } else {
                                $scope.setPreliminarState(x, y, 0);
                            }
                        } else {
                            $scope.setPreliminarState(x, y, null);
                        }
                    } else {
//                        $scope.renderCell(x, y, 'violet');
                    }
                }

                $scope.moveView = function(dx, dy) {
                    $scope.posx = $scope.posx - dx;
                    $scope.posy = $scope.posy - dy;

                    // reset for changing view position
                    $scope.context.clearRect(0, 0, $scope.canvas.width, $scope.canvas.height);
                    $scope.drawGrid();
                    $scope.redraw();
                }

                $scope.clean = function() {
                    if (!confirm("All unsaved changes will be lost. Continue?"))
                        return;
                    $scope.currentStates = {};
                    $scope.restartScene();
                }


                // game
                $scope.game = {
                    download: function() {
                        $("#download").submit();
                    },
                    upload: function() {
                        if (confirm("All unsaved changes will be lost. Continue?"))
                            $("#uploadfilename").change(function() {
                                var form = $("#upload");
                                var data = new FormData(form.get(0));
                                var files = $("[type=file]", form);

                                files.each(function(fi, fileToUpload) {
                                    var ins = fileToUpload.files.length;
                                    for (var x = 0; x < ins; x++)
                                    {
                                        data.append($(fileToUpload).attr("name"), fileToUpload.files[x]);
                                    }
                                })

                                $.ajax({
                                    url: form.attr('ng-action') || form.attr('action'),
                                    data: data,
                                    cache: false,
                                    contentType: false,
                                    dataType: 'json',
                                    processData: false,
                                    type: 'POST',
                                    success: function(data) {
                                        if (data.error) {
                                            alert(data.error);
                                        } else {
                                            $scope.currentStates = JSON.parse(data.currentStates);
                                            $scope.w = data.w * 1;
                                            $scope.h = data.h * 1;
                                            $scope.posx = data.posx * 1;
                                            $scope.posy = data.posy * 1;
                                            $scope.s = data.s * 1;
                                            $scope.restartScene();
                                            $scope.redraw();
                                        }
                                    }
                                });
                            }).click();
                    }
                }


                // start
                $scope.restartScene();
            }


        </script>
        <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css"/>
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
    </head>
    <body ng-controller="LifeAppController" id="LifeAppController">
        <div id="main-container" class="text-center">
<!--            <table class="table table-bordered td-w10">
                <tbody>
                    <tr ng-repeat="itemx in itemsx">
                        <td ng-repeat="itemy in itemsy[itemx]">
                            <a title="{{itemx}}x{{itemy}} ({{currentStates[itemx][itemy]}})" class="cell alive{{currentStates[itemx][itemy]}}" href="#cell-toggle" eat-click ng-click="toggleLife(itemx, itemy)">
                                {{itemx}}x{{itemy}}
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>-->
            <table align="center">
                <tbody>
                    <tr>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(-1, -1)" eat-click><i class="fa fa-long-arrow-up fa-rotate-305 fa-fw"></i></button>
                        </td>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(0, -1)" eat-click><i class="fa fa-long-arrow-up fa-fw"></i></button>
                        </td>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(1, -1)" eat-click><i class="fa fa-long-arrow-up fa-rotate-45 fa-fw"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(-1, 0)" eat-click><i class="fa fa-long-arrow-left fa-fw"></i></button>
                        </td>
                        <td>
                            <canvas id="lifeCanvas"></canvas>
                        </td>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(1, 0)" eat-click><i class="fa fa-long-arrow-right fa-fw"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(-1, 1)" eat-click><i class="fa fa-long-arrow-up fa-rotate-215 fa-fw"></i></button>
                        </td>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(0, 1)" eat-click><i class="fa fa-long-arrow-down fa-fw"></i></button>
                        </td>
                        <td>
                            <button class="btn btn-default" ng-mousedown="moveView(1, 1)" eat-click><i class="fa fa-long-arrow-up fa-rotate-135 fa-fw"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <nav class="navbar navbar-inverse navbar-fixed-bottom" role="navigation">
                <div class="container">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="#">Game of Life {{w}}x{{h}}</a>
                        <small class="text-left">by Anton Shashok</small>
                    </div>
                    <form class="navbar-form navbar-right">
                        <div class="form-group">
                            <nobr>
                                <input type="number" min="1" max="50" class="form-control" ng-model="s" ng-change="prerun()" data-toggle="tooltip" data-placement="top" title="speed">
                                &raquo;
                                <input type="number" min=5 max="1000" class="form-control" ng-model="w" ng-change="restartScene()" data-toggle="tooltip" data-placement="top" title="Nr of cols">
                                x
                                <input type="number" min=5 max="1000" class="form-control" ng-model="h" ng-change="restartScene()" data-toggle="tooltip" data-placement="top" title="Nr of rows">
                            </nobr>
                        </div>
                    </form>
                    <ul class="nav navbar-nav navbar-left" ng-if="!gameStatus">
                        <li class="dropdown text-left">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-save"></i> Load / Save <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="" ng-click="game.download()" eat-click href="#download"><i class="fa fa-download"></i> download game</a>
                                    <form class="hide" target="_blank" method="post" action="download.php" id="download">
                                        <textarea name="currentStates">{{currentStates}}</textarea>
                                        <input type="text" value="{{w}}" name="w">
                                        <input type="text" value="{{h}}" name="h">
                                        <input type="text" value="{{posx}}" name="posx">
                                        <input type="text" value="{{posy}}" name="posy">
                                        <input type="text" value="{{s}}" name="s">
                                    </form>
                                </li>
                                <li>
                                    <a class="" ng-click="game.upload()" eat-click href="#load"><i class="fa fa-upload"></i> load game</a>
                                    <form class="hide" method="post" action="upload.php" id="upload" enctype="multipart/form-data">
                                        <input type="hidden" name="MAX_FILE_SIZE" value="1194304" /> 
                                        <input type="file" name="game" id="uploadfilename">
                                    </form>
                                </li>
                                <li class="divider"></li>
                                <li class="disabled">
                                    <a class="disabled" ng-click="game.cloud_download()" eat-click href="#cloud-save"><i class="fa fa-cloud-download"></i> save to cloud</a>
                                </li>
                                <li class="disabled">
                                    <a class="disabled" ng-click="game.cloud_upload()" eat-click href="#cloud-load"><i class="fa fa-cloud-upload"></i> load from cloud</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li data-toggle="tooltip" data-placement="top" title="clean desk">
                            <a class="" ng-click="clean()" eat-click ng-show="elmscount && !gameStatus" href="#gen"><i class="fa fa-fire-extinguisher text-danger"></i></a>
                        </li>
                        <li data-toggle="tooltip" data-placement="top" title="next generation">
                            <a class="" ng-click="draw(true)" eat-click ng-show="!gameStatus" href="#gen"><i class="fa fa-step-forward"></i></a>
                        </li>
                        <li data-toggle="tooltip" data-placement="top" title="start life">
                            <a class="" ng-click="run()" eat-click ng-show="!gameStatus" href="#play"><i class="fa fa-play"></i></a>
                        </li>
                        <li data-toggle="tooltip" data-placement="top" title="pause life">
                            <a class="" ng-click="pause()" eat-click ng-show="gameStatus" href="#pause"><i class="fa fa-pause"></i></a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <script>
                    $("[data-toggle=tooltip]").tooltip();
        </script>
    </body>
</html>

<?
flush();
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
if ($ip != "192.168.255.94")
    @mail("peedjack@gmail.com", "life game user detected", "IP: " . $ip);
?>