'use strict'
function LifeAppController($scope) {

    // INIT CONCTROLLER VARS
    $scope.gameInterval = 2000 // 2 sec;
    $scope.gameStatus = 0; // 0 - pause; 1 - play

    $scope.currentStates = {"44": {"43": 1, "44": 1, "49": 1, "50": 1, "51": 0}, "45": {"43": 1, "45": 1, "48": 1, "50": 1}, "46": {"43": 1, "48": 0, "50": 1}, "47": {"48": 0}, "48": {"48": 0}, "49": {"48": 0}, "52": {"43": 1, "44": 0, "45": 0, "50": 1}, "53": {"43": 1, "44": 0, "45": 1, "46": 0, "48": 1, "50": 1}, "54": {"43": 1, "44": 1, "45": 0, "49": 1, "50": 1}}; // x,y position states 1/0 alive/not
    $scope.preliminarStates = {};

    $scope.games = {
        "Gosper’s Glide Gun": {"23": {"22": 1, "23": 1}, "24": {"22": 1, "23": 1}, "33": {"22": 1, "23": 1, "24": 1}, "34": {"21": 1, "25": 1}, "35": {"20": 1, "26": 1}, "36": {"20": 1, "26": 1}, "37": {"23": 1}, "38": {"21": 1, "25": 1}, "39": {"22": 1, "23": 1, "24": 1}, "40": {"23": 1}, "43": {"20": 1, "21": 1, "22": 1}, "44": {"20": 1, "21": 1, "22": 1}, "45": {"19": 1, "23": 1}, "47": {"18": 1, "19": 1, "23": 1, "24": 1}, "57": {"20": 1, "21": 1}, "58": {"20": 1, "21": 1}}
    };

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

    // INIT canvas
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


    // game functions
    $scope.game = {
        download: function() {
            // download file
            $("#download").submit();
        },
        upload: function() {
            // upload file with ajax
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
                            }
                        }
                    });
                }).click();
        }
    }

    $scope.loadGame = function(data) {
        if (!confirm("All unsaved changes will be lost. Continue?"))
            return;
        $scope.currentStates = data;
        $scope.restartScene();
    }


    // start
    $scope.restartScene();
}