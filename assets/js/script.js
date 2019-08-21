/*
Summary
0.Game initialization
1.Game board display
2.Controls
3.Display effect

*/
/*
0.Game initialization
*/
//Define configuration variables
const API_ADDRESS = 'http://localhost/magnetic-grotto-game/api';


//Load ambient sound
document.getElementById('ambient-sound').volume=0.2;
document.getElementById('ambient-sound').play();
/*Redirect to Game board if the playerId and gameBoard as
already been defined.*/
if (localStorage.getItem("gameToken") !== null) {
    show('.game-board');
}

//Game board refresh loop
window.setInterval(function () {
    if (localStorage.getItem("gameToken") !== null) {
        getGameBoardState();
    } else {
        clearInterval(window);
    }
}, 2500);

//Notification and error display function
function notify(message) {
    $(".notification").removeClass("d-none")
    $(".notification").html(message);
}
function closeNotification() {
    $(".notification").removeClass("d-none").addClass("d-none");
    $(".notification").html("");
}

//Alert display function
function alert(message) {
    $(".alert").removeClass("d-none")
    $(".alert").html(message);
}
function closeAlert() {
    $(".alert").removeClass("d-none").addClass("d-none");
    $(".alert").html("");
}

//Redirections
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};
var token = getUrlParameter('token');
if (token != undefined) {
    $('.game-invite-name-input').val('');
    $('.game-invite-gametoken-input').val(token);
    clearGame();
    show('.game-invite');
}

//Clear game function
function clearGame() {
    clearInterval(window);
    $(".players-info").removeClass("d-none").addClass("d-none");
    $(".game-menu").removeClass("d-none").addClass("d-none");
    localStorage.removeItem("gameToken");
    localStorage.removeItem("playerToken");
}

//initialize particles-overlay
// ParticlesJS Config.
// ParticlesJS Config.
particlesJS("particles-overlay", {
    "particles": {
        "number": {
            "value": 400,
            "density": {
                "enable": true,
                "value_area": 800
            }
        },
        "color": {
            "value": "#ffea00"
        },
        "shape": {
            "type": "circle",
            "stroke": {
                "width": 0,
                "color": "#000000"
            },
            "polygon": {
                "nb_sides": 5
            },
            "image": {
                "src": "img/github.svg",
                "width": 100,
                "height": 100
            }
        },
        "opacity": {
            "value": 0.5,
            "random": true,
            "anim": {
                "enable": false,
                "speed": 1,
                "opacity_min": 0.1,
                "sync": false
            }
        },
        "size": {
            "value": 5,
            "random": true,
            "anim": {
                "enable": false,
                "speed": 40,
                "size_min": 0.1,
                "sync": false
            }
        },
        "line_linked": {
            "enable": false,
            "distance": 500,
            "color": "#ffffff",
            "opacity": 0.4,
            "width": 2
        },
        "move": {
            "enable": true,
            "speed": 1,
            "direction": "bottom",
            "random": true,
            "straight": false,
            "out_mode": "out",
            "bounce": false,
            "attract": {
                "enable": false,
                "rotateX": 600,
                "rotateY": 1200
            }
        }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": {
                "enable": true,
                "mode": "bubble"
            },
            "onclick": {
                "enable": true,
                "mode": "repulse"
            },
            "resize": true
        },
        "modes": {
            "grab": {
                "distance": 400,
                "line_linked": {
                    "opacity": 0.5
                }
            },
            "bubble": {
                "distance": 400,
                "size": 4,
                "duration": 0.3,
                "opacity": 1,
                "speed": 3
            },
            "repulse": {
                "distance": 200,
                "duration": 0.4
            },
            "push": {
                "particles_nb": 4
            },
            "remove": {
                "particles_nb": 2
            }
        }
    },
    "retina_detect": true
});


//Init game Board
initGameBoard();
function initGameBoard() {
    var gameBoard = [];
    for (i = 1; i <= 8; i++) {
        for (e = 1; e <= 8; e++) {
            var discType = 0;
            //var discType = Math.floor(Math.random() * 3);
            space = { x: e, y: i, disc: discType };
            gameBoard.push(space);
        }
    }
    $('.game-board').html('');
    //Display arrows buttons so the user can place discs
    function arrowLeft(collumnCount) {
        $('.game-board').append(`<img src="assets/img/disc-place-left.svg" 
    class="disc arrow" row="`+ collumnCount + `" col="right" draggable="false" 
    onmousedown="return false" style="user-drag: none">`);
    }
    function arrowRight(collumnCount) {
        $('.game-board').append(`<img src="assets/img/disc-place-right.svg" 
    class="disc arrow" row="`+ collumnCount + `" col="left" draggable="false" 
    onmousedown="return false" style="user-drag: none">`);
    }
    //Define row size. (collumnSize = rowSize)
    rowSize = 8;
    rowCount = 1; //counter for the iteration
    collumnCount = 1;
    gameBoard.forEach(function (cell) {
        if (rowCount == 1) {
            arrowRight(collumnCount)
        }
        discType = 'disc-empty';
        //Place the cell with it's current disc (if any) on the game board
        $('.game-board').append(`<img src="assets/img/` + discType +
            `.svg" class="disc bounce animated" draggable="false" onmousedown="return false" style="user-drag: none" coordx="`
            + cell.x + `" coordy="` + cell.y + `">`);

        if (rowCount == 8) {
            arrowLeft(collumnCount);
            $('.game-board').append('<br>');
            if (cell.x != "8" && cell.y != "8") {
                arrowRight(collumnCount + 1)
            }
            rowCount = 0;
            collumnCount++
        }
        rowCount++;
    });
}
/*
1.Game board display
*/

function getGameBoardState() {
    //Dummy gameBoard for test purpose
    //Retrieve GameBoard data
    var gameBoard;
    $.ajax({
        type: "POST",
        url: API_ADDRESS + "/game/getState",
        data: {
            inviteToken: localStorage.getItem('gameToken'),
            playerToken: localStorage.getItem('playerToken')
        },
        success: function (game) {
            //update disks
            gameBoard = JSON.parse(game.game_board);
            var playSound = 0;
            gameBoard.forEach(function (cell) {
                if (cell.disc !== 0) {
                    var discType;
                    switch (cell.disc) {
                        case 1:
                            discType = 'disc-blue';
                            break;
                        case 2:
                            discType = 'disc-red';
                            break;
                        default:
                            discType = 'disc-empty';
                    }
                    discType = 'assets/img/' + discType + '.svg';
                    var previousDiscType = $('.disc[coordx="' + cell.x + '"][coordy="' + cell.y + '"]').attr('src');

                    //change it's disc type
                    $('.disc[coordx="' + cell.x + '"][coordy="' + cell.y + '"]').attr('src', discType);
                    //play animation
                    if (previousDiscType != discType) {
                        $('.disc[coordx="' + cell.x + '"][coordy="' + cell.y + '"]').removeClass('bounce animated');
                        setTimeout(function () {
                            $('.disc[coordx="' + cell.x + '"][coordy="' + cell.y + '"]').addClass("bounce animated");
                        }, 100);
                        playSound = 1;
                    }
                }
            });
            if (playSound == 1) {
                if (game.winner == 1 ||
                    game.winner == 2) {
                    document.getElementById('win-sound').volume=0.5;
                    document.getElementById('win-sound').play();
                } else {
                    document.getElementById('coin-sound').volume=0.5;
                    document.getElementById('coin-sound').play();
                }
            }

            //Display player name and turn
            $(".player1-info").html('');
            $(".player2-info").html('');
            $(".player1-info").append(game.playerNames[0][Object.keys(game.playerNames[0])[0]].toUpperCase());
            if (game.playerNames[1] !== undefined) {
                $(".player2-info").append(game.playerNames[1][Object.keys(game.playerNames[1])[0]].toUpperCase());
            } else {
                $(".player2-info").append('Waiting for a<br> second player');
            }
            $(".players-info").removeClass("d-none");

            if (game.player_turn == 1 && game.winner == null) {
                $(".player1-info").append('<br>Your Turn');
            }
            if (game.player_turn == 2 && game.winner == null) {
                $(".player2-info").append('<br>Your Turn');
            }
            if (game.winner == 1) {
                $(".player1-info").append('<br><strong>YOU WON !</strong>');
            }
            if (game.winner == 2) {
                $(".player2-info").append('<br><strong>YOU WON !</strong>');
            }


            //Display game menu
            $(".game-menu").removeClass("d-none");
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            notify(XMLHttpRequest.responseJSON.error);
            localStorage.removeItem("gameToken");
            localStorage.removeItem("playerToken");
        }
    });
}

/*
2.Controls
*/
//Change views
function clearViews() {
    $(".ui").removeClass("d-none").addClass("d-none");
}
function show(section) {
    clearViews();
    closeNotification()
    $(section).removeClass("d-none");
}

//Create game
$(".btn-create-game").on("click", function () {

    $.ajax({
        type: "POST",
        url: API_ADDRESS + "/player",
        data: {
            name: $('.game-creation-name-input').val()
        },
        success: function (playerId) {
            $.ajax({
                type: "POST",
                url: API_ADDRESS + "/game",
                data: {
                    playerId: Number(playerId)
                },
                success: function (game) {
                    localStorage.setItem('gameToken', game.inviteToken);
                    localStorage.setItem('playerToken', game.playerToken);
                    alert(`Share this link to your friend to invite him to the game:<br>
                    <span class="c-dark-red">`+ window.location.href + `?token=` + game.inviteToken + `</span><br>
                    You may play your first move while you wait for him.<br>
                    Good luck!
                    <br><br>
                    <p class="close-alert">Close & Play</p>`);
                    initGameBoard();
                    getGameBoardState();
                    show('.game-board');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    notify(XMLHttpRequest.responseJSON.error);
                }
            });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            notify(XMLHttpRequest.responseJSON.error);
        }
    });
});

//Accept Invite
$(".btn-accept-invite").on("click", function () {
    $.ajax({
        type: "POST",
        url: API_ADDRESS + "/acceptInvite",
        data: {
            name: $('.game-invite-name-input').val(),
            inviteToken: $('.game-invite-gametoken-input').val()
        },
        success: function (game) {
            localStorage.setItem('playerToken', game.playerToken);
            localStorage.setItem('gameToken', game.inviteToken);
            initGameBoard();
            getGameBoardState();
            show('.game-board');
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            notify(XMLHttpRequest.responseJSON.error);
        }
    });
});
//Show game creation
$(".btn-show-game-creation").on("click", function () {
    $('.game-creation-name-input').val('')
    show('.game-creation');
});
//Show game invite
$(".btn-show-game-invite").on("click", function () {
    $('.game-invite-name-input').val('');
    show('.game-invite');
});
//Show tutorial
$(".btn-show-tutorial").on("click", function () {
    show('.tutorial');
});
//Close notification
$(".notification").on("click", function () {
    closeNotification();
});
//Close alert
$('body').on('click', '.close-alert', function () {
    closeAlert();
});

//Resign
$(".btn-resign").on("click", function () {

    $.ajax({
        type: "POST",
        url: API_ADDRESS + "/game/resign",
        data: {
            inviteToken: localStorage.getItem('gameToken'),
            playerToken: localStorage.getItem('playerToken')
        },
        success: function () {
            clearGame()
            show('.game-creation');
            notify('Successfully Resigned.');
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            clearGame()
            show('.game-creation');
            notify(XMLHttpRequest.responseJSON.error);
        }
    });
});

//Add disk / perform a move
$('body').on('click', '.arrow', function () {
    $.ajax({
        type: "POST",
        url: API_ADDRESS + "/move",
        data: {
            inviteToken: localStorage.getItem('gameToken'),
            playerToken: localStorage.getItem('playerToken'),
            boardRow: $(this).attr("row"),
            boardCol: $(this).attr("col")
        },
        success: function () {
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            notify(XMLHttpRequest.responseJSON.error);
        }
    });
});

/*
3.Display effect
*/
//Discs & arrows highlight
$('body').on('mouseenter', '.arrow', function () {
    $(".disc[coordy=" + $(this).attr("row") + "]").removeClass('disc-highlight').addClass('disc-highlight');
});
$('body').on('mouseleave', '.arrow', function () {
    $(".disc").removeClass('disc-highlight');
});