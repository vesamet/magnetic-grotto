/*
Summary
0.Game initialization
1.Game board display
2.Controls
*/
/*
0.Game initialization
*/
//Define configuration variables
const API_ADDRESS = 'http://localhost/magnetic-grotto-game/api';

//Redirect to Game board if the playerId and gameBoard as
//already been defined.
if (localStorage.getItem("playerId") !== null &&
    localStorage.getItem("gameToken") !== null) {
    getGameBoardState();
    show('.game-board');
}

//Notification and error display function
function notify(message) {
    $(".notification").removeClass("d-none")
    $(".notification").html(message);
}

/*
1.Game board display
*/

function getGameBoardState() {
    //Dummy gameBoard for test purpose
    var gameBoard = [];
    for (i = 1; i <= 8; i++) {
        for (e = 1; e <= 8; e++) {
            var diskType = 0;
            //var diskType = Math.floor(Math.random() * 3);
            space = { x: e, y: i, disk: diskType };
            gameBoard.push(space);
        }
    }
    $('.array').html(JSON.stringify(gameBoard));

    rowSize = 8;
    rowCount = 1;
    gameBoard.forEach(function (cell) {
        var diskColor = 'disk-empty';
        if (cell.disk == 1) {
            diskColor = 'disk-blue';
        } else if (cell.disk == 2) {
            diskColor = 'disk-red';
        }

        $('.game-board').append('<img src="assets/img/' + diskColor + '.svg" class="disk" coordx="'+ cell.x +'" coordy="'+ cell.y +'">');
        if (rowCount == 8) {
            $('.game-board').append('<br>');
            rowCount = 0;
        }
        rowCount++;
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
            localStorage.setItem('playerId', Number(playerId));
            $.ajax({
                type: "POST",
                url: API_ADDRESS + "/game",
                data: {
                    playerId: Number(playerId)
                },
                success: function (gameToken) {
                    localStorage.setItem('gameToken', gameToken);
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
});
//Show game creation
$(".btn-show-game-creation").on("click", function () {
    $('.game-creation-name-input').val('')
    show('.game-creation');
});
//Show game invite
$(".btn-show-game-invite").on("click", function () {
    $('.game-invite-name-input').val('')
    $('.game-invite-gametoken-input').val('')
    show('.game-invite');
});
//Show tutorial
$(".btn-show-tutorial").on("click", function () {
    show('.tutorial');
});
//Close notification
$(".notification").on("click", function () {
    $(".notification").addClass("d-none")
});