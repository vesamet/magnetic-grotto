<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Medoo\Medoo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

require '../vendor/autoload.php';

$config['debug'] = true;
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['errorHandler'] = function ($config) {
    return function ($request, $response, $exception) use ($config) {
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->withJson(['Something went wrong!']);
    };
};
$app = new \Slim\App(['settings' => $config]);

//Connect to the database via Medoo
$container = $app->getContainer();
$container['database'] = function () {
    return new Medoo([
        'database_type' => 'mysql',
        'database_name' => 'magnetic-grotto',
        'server' => 'localhost',
        'username' => 'root',
        'password' => ''
    ]);
};

//Dummy endpoint for testing
$app->get('/test', function (Request $request, Response $response, array $args) {

    return $response->getBody()->write(Uuid::uuid4()->toString());
});

//🌕👉 E N D P O I N T S 👈🌕

//🌕 PLAYERS 
//👉 Create
function createPlayer($database, $name)
{
    $createPlayer = $database->insert('players', [
        "id" => null,
        "name" => strtolower(trim($name)),
        "created_at" => Medoo::raw('UNIX_TIMESTAMP()')
    ]);
    return $database->id();
}
$app->post('/player', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    if (empty($body['name'])) {
        return $response->withJson(array(
            "error" => "No name defined."
        ), 400);
    }
    $name = filter_var($body['name'], FILTER_SANITIZE_STRING);
    if (strlen($name) > 25) {
        return $response->withJson(array(
            "error" => "Your name should not have more than 25 characters."
        ), 400);
    }
    $player = createPlayer($this->database, $name);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    }
    return $response->withJson($player);
});

//👉 Read
function getPlayers($database)
{
    $players = $database->select('players', "*");
    return $players;
}
$app->get('/players', function (Request $request, Response $response, array $args) {
    $players = getPlayers($this->database);
    return $response->withJson($players);
});

//👉 ReadOne
function getPlayer($database, $playerId)
{
    $player = $database->select('players', "*", ["id" => $playerId]);
    return $player;
}
$app->get('/player/{playerId}', function (Request $request, Response $response, array $args) {
    $playerId = $args['playerId'];
    $player = getPlayer($this->database, $playerId);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    }
    return $response->withJson($player);
});

//🌕 GAMES
//👉 Create
function createGame($database, $playerId)
{
    $playerToken = Uuid::uuid4()->toString();
    $inviteToken = Uuid::uuid4()->toString();
    $createGame = $database->insert('games', [
        "invite_token" => $inviteToken,
        "players" => json_encode(array([
            "playerId" => $playerId,
            "playerRole" => 1,
            "playerToken" => $playerToken
        ])),
        "player_turn" => 1,
        "is_full" => 0,
        "game_board" => json_encode(json_decode('[{"x":1,"y":1,"disc":0},{"x":2,"y":1,"disc":0},{"x":3,"y":1,"disc":0},{"x":4,"y":1,"disc":0},{"x":5,"y":1,"disc":0},{"x":6,"y":1,"disc":0},{"x":7,"y":1,"disc":0},{"x":8,"y":1,"disc":0},{"x":1,"y":2,"disc":0},{"x":2,"y":2,"disc":0},{"x":3,"y":2,"disc":0},{"x":4,"y":2,"disc":0},{"x":5,"y":2,"disc":0},{"x":6,"y":2,"disc":0},{"x":7,"y":2,"disc":0},{"x":8,"y":2,"disc":0},{"x":1,"y":3,"disc":0},{"x":2,"y":3,"disc":0},{"x":3,"y":3,"disc":0},{"x":4,"y":3,"disc":0},{"x":5,"y":3,"disc":0},{"x":6,"y":3,"disc":0},{"x":7,"y":3,"disc":0},{"x":8,"y":3,"disc":0},{"x":1,"y":4,"disc":0},{"x":2,"y":4,"disc":0},{"x":3,"y":4,"disc":0},{"x":4,"y":4,"disc":0},{"x":5,"y":4,"disc":0},{"x":6,"y":4,"disc":0},{"x":7,"y":4,"disc":0},{"x":8,"y":4,"disc":0},{"x":1,"y":5,"disc":0},{"x":2,"y":5,"disc":0},{"x":3,"y":5,"disc":0},{"x":4,"y":5,"disc":0},{"x":5,"y":5,"disc":0},{"x":6,"y":5,"disc":0},{"x":7,"y":5,"disc":0},{"x":8,"y":5,"disc":0},{"x":1,"y":6,"disc":0},{"x":2,"y":6,"disc":0},{"x":3,"y":6,"disc":0},{"x":4,"y":6,"disc":0},{"x":5,"y":6,"disc":0},{"x":6,"y":6,"disc":0},{"x":7,"y":6,"disc":0},{"x":8,"y":6,"disc":0},{"x":1,"y":7,"disc":0},{"x":2,"y":7,"disc":0},{"x":3,"y":7,"disc":0},{"x":4,"y":7,"disc":0},{"x":5,"y":7,"disc":0},{"x":6,"y":7,"disc":0},{"x":7,"y":7,"disc":0},{"x":8,"y":7,"disc":0},{"x":1,"y":8,"disc":0},{"x":2,"y":8,"disc":0},{"x":3,"y":8,"disc":0},{"x":4,"y":8,"disc":0},{"x":5,"y":8,"disc":0},{"x":6,"y":8,"disc":0},{"x":7,"y":8,"disc":0},{"x":8,"y":8,"disc":0}]')),
        "created_by" => $playerId,
        "created_at" => Medoo::raw('UNIX_TIMESTAMP()')
    ]);
    return [
        "playerToken" => $playerToken,
        "inviteToken" => $inviteToken
    ];
}

$app->post('/game', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    //retrieve player id who is requesting the creation of a new game
    $playerId = $body['playerId'];
    //Validate player id format
    if (empty($playerId)) {
        return $response->withJson(array(
            "error" => "No player id given for the game initialization."
        ), 400);
    }
    if (!is_numeric($playerId)) {
        return $response->withJson(array(
            "error" => "The id given isn't in a valid format."
        ), 400);
    }
    //Check if player exists
    $playerExists = getPlayer($this->database, $playerId);
    if (empty($playerExists)) {
        return $response->withJson(array(
            "error" => "No player with the given id exists."
        ), 400);
    }
    //Create the game
    $game = createGame($this->database, $playerId);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson($game);
    }
});
//👉 Read
function getGames($database)
{
    $games = $database->select('games', "*");
    return $games;
}
$app->get('/games', function (Request $request, Response $response, array $args) {
    $games = getGames($this->database);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson($games);
    }
});

//👉 ReadOne
function getGame($database, $gameId)
{
    $game = $database->select('games', "*", ["id" => $gameId]);
    return $game;
}
$app->get('/game/{gameId}', function (Request $request, Response $response, array $args) {
    $gameId = $args['gameId'];
    $game = getPlayer($this->database, $gameId);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson($game);
    }
});

//👉 ReadOneByInviteToken
function getGameByInviteToken($database, $inviteToken)
{
    $game = $database->select('games', "*", ["invite_token" => $inviteToken]);
    return $game;
}
$app->get('/game/token/{inviteToken}', function (Request $request, Response $response, array $args) {
    $inviteToken = $args['inviteToken'];
    $game = getGameByInviteToken($this->database, $inviteToken);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson($game);
    }
});


//👉 acceptInvite
function addNewPlayerToGame($database, $inviteToken, $name)
{
    //Create the user
    $createPlayer = createPlayer($database, $name);
    if ($database->error()[0] != "00000") {
        return $database->error();
    };
    $playerId = $database->id();
    //Retrieve data related to the given game
    $gameData = getGameByInviteToken($database, $inviteToken);
    if ($database->error()[0] != "00000") {
        return $database->error();
    };
    $playersData = json_decode($gameData[0]['players']);
    //Add it to the given game and assign it a token.
    $playerToken = Uuid::uuid4()->toString();
    $playersData[] = (object) array(
        "playerId" => $playerId,
        "playerRole" => 2,
        "playerToken" => $playerToken
    );
    $database->update("games", [
        "players" => json_encode($playersData),
        "is_full" => 1,
    ], [
        "invite_token" => $inviteToken
    ]);
    if ($database->error()[0] != "00000") {
        return $database->error();
    } else {
        return [
            "inviteToken" => $inviteToken,
            "playerToken" => $playerToken
        ];
    }
}
$app->post('/acceptInvite', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    //Validate name type
    $inviteToken = $body['inviteToken'];
    if (empty($body['name'])) {
        return $response->withJson(array(
            "error" => "No name defined."
        ), 400);
    }
    $name = filter_var($body['name'], FILTER_SANITIZE_STRING);
    //Validate name length
    if (strlen($name) > 25) {
        return $response->withJson(array(
            "error" => "Your name should not have more than 25 characters."
        ), 400);
    }
    //Validate invite token
    $acceptInvite = $this->database->select('games', ["invite_token", "is_full", "winner"], [
        "invite_token" => $inviteToken
    ]);
    if (empty($acceptInvite)) {
        return $response->withJson(array(
            "error" => "Sorry, but no game is related to this invitation token."
        ), 400);
    }
    //Validate if game is open to invite
    if ($acceptInvite[0]['is_full'] == 1) {
        return $response->withJson(array(
            "error" => "Sorry, but someone already joined this game."
        ), 400);
    }
    //Validate if game is already won
    if ($acceptInvite[0]['winner'] != null) {
        return $response->withJson(array(
            "error" => "Sorry, but this game has already been played."
        ), 400);
    }

    //Add player to the game
    $addNewPlayerToGame = addNewPlayerToGame($this->database, $inviteToken, $name);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson($addNewPlayerToGame);
    }
});

//👉 getState
function getGameState($database, $inviteToken, $playerToken)
{
    //Retrieve data related to the given game
    $gameData = getGameByInviteToken($database, $inviteToken);
    if ($database->error()[0] != "00000") {
        return $database->error();
    }
    //Retrieve players names
    $gameData[0]['playerNames'] = [];
    foreach (json_decode($gameData[0]['players'], true) as $key => $value) {
        $playerName = $database->select('players', ["name"], [
            "id" => $value['playerId']
        ]);
        if (empty($playerName)) {
            $playerName = 'No name';
        } else {
            $playerName = [$value['playerId'] => $playerName[0]['name']];
        }
        //add player names to the game state
        array_push($gameData[0]['playerNames'], $playerName);
    }

    if ($database->error()[0] != "00000") {
        return $database->error();
    } else {
        return $gameData[0];
    }
}

$app->post('/game/getState', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    //Validate playerToken
    if (empty($body['playerToken'])) {
        return $response->withJson(array(
            "error" => "No player token given."
        ), 400);
    }
    $playerToken = $body['playerToken'];
    //Validate invite token
    if (empty($body['inviteToken'])) {
        return $response->withJson(array(
            "error" => "No invite token given."
        ), 400);
    }
    $inviteToken = $body['inviteToken'];
    $gameState = $this->database->select('games', ["invite_token", "is_full", "winner", "players"], [
        "invite_token" => $inviteToken
    ]);
    if (empty($gameState)) {
        return $response->withJson(array(
            "error" => "Sorry, but no game is related to this invitation token."
        ), 400);
    }
    //Validate if the player is in the game
    $playersData = json_decode($gameState[0]['players']);
    $isValid = 0;
    foreach ($playersData as $key => $player) {
        if ($player->playerToken == $playerToken) {
            $isValid = 1;
        }
    }
    if ($isValid == 0) {
        return $response->withJson(array(
            "error" => "Sorry, but you are not an active player of this game."
        ), 400);
    }

    //Retrieve game state
    $getGameState = getGameState($this->database, $inviteToken, $playerToken);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson($getGameState);
    }
});

//👉 Resign
$app->post('/game/resign', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    //Validate playerToken
    if (empty($body['playerToken'])) {
        return $response->withJson(array(
            "error" => "No player token given."
        ), 400);
    }
    $playerToken = $body['playerToken'];
    //Validate invite token
    if (empty($body['inviteToken'])) {
        return $response->withJson(array(
            "error" => "No invite token given."
        ), 400);
    }
    $inviteToken = $body['inviteToken'];
    //Validate invite token
    $gameState = $this->database->select('games', ["invite_token", "is_full", "winner", "players"], [
        "invite_token" => $inviteToken
    ]);
    if (empty($gameState)) {
        return $response->withJson(array(
            "error" => "Sorry, but no game is related to this invitation token."
        ), 400);
    }

    //Validate if the player is in the game
    $playersData = json_decode($gameState[0]['players']);
    $isValid = 0;
    $playerId = 0;
    foreach ($playersData as $key => $player) {
        if ($player->playerToken == $playerToken) {
            $isValid = 1;
            $playerRole = $player->playerRole;
        }
    }
    if ($isValid == 0) {
        return $response->withJson(array(
            "error" => "Sorry, but you are not an active player of this game."
        ), 400);
    }

    //change the resign status of the game
    $this->database->update("games", [
        "player_resigned" => $playerRole
    ], [
        "invite_token" => $inviteToken
    ]);

    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson('Successfully resigned.');
    }
});


//🌕 MOVES
//👉 read
function getGameMoves($database, $gameId)
{
    //Retrieve moves related to the given game
    $gameMoves = $database->select('moves', '*', [
        "game_id" => $gameId
    ]);
    if ($database->error()[0] != "00000") {
        return $database->error();
    } else {
        return $gameMoves;
    }
}
$app->post('/moves/getMoves', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();

    //Validate invite token
    if (empty($body['inviteToken'])) {
        return $response->withJson(array(
            "error" => "No invite token given."
        ), 400);
    }
    $inviteToken = $body['inviteToken'];
    $gameId = $this->database->select('games', "id", [
        "invite_token" => $inviteToken
    ]);
    if (empty($gameId)) {
        return $response->withJson(array(
            "error" => "Sorry, but no game is related to this invitation token."
        ), 400);
    }
    //Retrieve game moves
    $getGameMoves = getGameMoves($this->database, $gameId[0]);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson($getGameMoves);
    }
});

//👉 Perform move
$app->post('/move', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    //Validate playerToken
    if (empty($body['playerToken'])) {
        return $response->withJson(array(
            "error" => "No player token given."
        ), 400);
    }
    $playerToken = $body['playerToken'];
    //Validate invite token
    if (empty($body['inviteToken'])) {
        return $response->withJson(array(
            "error" => "No invite token given."
        ), 400);
    }
    $inviteToken = $body['inviteToken'];
    //Validate boardRow
    if (empty($body['boardRow'])) {
        return $response->withJson(array(
            "error" => "No row given."
        ), 400);
    }
    if (
        $body['boardRow'] > 8 ||
        $body['boardRow'] < 1
    ) {
        return $response->withJson(array(
            "error" => "The given row is invalid."
        ), 400);
    }
    $boardRow = $body['boardRow'];
    //Validate boardCol
    if (empty($body['boardCol'])) {
        return $response->withJson(array(
            "error" => "No collumn given."
        ), 400);
    }
    if (
        $body['boardCol'] != "left" &&
        $body['boardCol'] != "right"
    ) {
        return $response->withJson(array(
            "error" => "The given collumn is invalid."
        ), 400);
    }
    $boardCol = $body['boardCol'];
    //Validate invite token
    $gameState = $this->database->select('games', "*", [
        "invite_token" => $inviteToken
    ]);
    if (empty($gameState)) {
        return $response->withJson(array(
            "error" => "Sorry, but no game is related to this invitation token."
        ), 400);
    }

    //Validate if the player is in the game
    $playersData = json_decode($gameState[0]['players']);
    $isValid = 0;
    $playerId = 0;
    $playerRole = 0;
    foreach ($playersData as $key => $player) {
        if ($player->playerToken == $playerToken) {
            $isValid = 1;
            $playerRole = $player->playerRole;
            $playerId = $player->playerId;
        }
    }
    if ($isValid == 0) {
        return $response->withJson(array(
            "error" => "Sorry, but you are not an active player of this game."
        ), 400);
    }
    //Validate if it's the turn of the player to play
    if ($gameState[0]['player_turn'] != $playerRole) {
        return $response->withJson(array(
            "error" => "Sorry, but it's not your turn yet."
        ), 400);
    }

    //Validate if the game is already won
    if (!empty($gameState[0]['winner'])) {
        return $response->withJson(array(
            "error" => "Sorry, but this game already has a winner."
        ), 400);
    }

    /*
    Validate move
    */
    function getDisc($gameBoard, $coordx, $coordy)
    {
        foreach ($gameBoard as $key => $cell) {
            if (
                $cell['x'] == $coordx &&
                $cell['y'] == $coordy
            ) {
                return $cell['disc'];
            }
        }
    }
    $gameBoard = json_decode($gameState[0]['game_board'], true);
    // Define where the disc would land
    $coordy = $boardRow;
    $coordx = 0;
    if ($boardCol == "left") {
        for ($colCount = 8; $colCount >= 1; $colCount--) {
            if (getDisc($gameBoard, $colCount, $coordy) == 0) {
                $coordx = $colCount;
                break;
            }
        }
    } elseif ($boardCol == "right") {
        for ($colCount = 1; $colCount <= 8; $colCount++) {
            if (getDisc($gameBoard, $colCount, $coordy) == 0) {
                $coordx = $colCount;
                break;
            }
        }
    }
    if ($coordx == 0) {
        //There is no empty cell on the row
        return $response->withJson(array(
            "error" => "The row has no space for more disc."
        ), 500);
    }
    //Insert move in db
    $this->database->insert('moves', [
        "game_id" => $gameState[0]['id'],
        "played_by" => $playerId,
        "performed_at" => Medoo::raw('UNIX_TIMESTAMP()'),
        "coord_x" => $coordx,
        "coord_y" => $coordy,
    ]);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    }
    //update game board (the new disc and player turn)
    $updatedGameBoard = $gameBoard;
    foreach ($updatedGameBoard as $key => &$cell) {
        if (
            $cell['x'] == $coordx &&
            $cell['y'] == $coordy
        ) {
            $cell['disc'] = $playerRole;
        }
    }
    $updatedPlayerTurn = 0;
    if ($gameState[0]['player_turn'] == 1) {
        $updatedPlayerTurn = 2;
    } else {
        $updatedPlayerTurn = 1;
    }

    /*
    Winner validation check
    */
    $winner = null;
    //Horizontal check
    //count towards the left of current disc
    $discCount = 0;
    for ($colCount = $coordx; $colCount >= 1; $colCount--) {
        if (getDisc($updatedGameBoard, $colCount, $coordy) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $colCount, $coordy) == $playerRole) {
            $discCount++;
        }
    }
    //count towards the right of current disc
    for ($colCount = $coordx; $colCount <= 8; $colCount++) {
        if (getDisc($updatedGameBoard, $colCount, $coordy) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $colCount, $coordy) == $playerRole) {
            $discCount++;
        }
    }
    if ($discCount > 5) {
        $winner = $playerRole;
    }

    //Vertical check
    //count towards the bottom of current disc
    $discCount = 0;
    for ($rowCount = $coordy; $rowCount >= 1; $rowCount--) {
        if (getDisc($updatedGameBoard, $coordx, $rowCount) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $coordx, $rowCount) == $playerRole) {
            $discCount++;
        }
    }
    //count towards the top of current disc
    for ($rowCount = $coordy; $rowCount <= 8; $rowCount++) {
        if (getDisc($updatedGameBoard, $coordx, $rowCount) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $coordx, $rowCount) == $playerRole) {
            $discCount++;
        }
    }
    if ($discCount > 5) {
        $winner = $playerRole;
    }


    // Diagonal check from bottom right to top left
    // count towards the bottom right of current disc
    $discCount = 0;
    $debugArray = [];
    $checkCoordx = $coordx;
    for ($rowCount = $coordy; $rowCount >= -1; $rowCount--) {
        if (getDisc($updatedGameBoard, $checkCoordx - 1, $rowCount - 1) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $checkCoordx - 1, $rowCount - 1) == $playerRole) {
            $discCount++;
            $debugArray[] = ["direction" => "UP", "x" => $checkCoordx - 1, "y" => $rowCount - 1];
        }
        $checkCoordx--;
    }
    //count towards the top left of current disc
    $checkCoordx = $coordx;
    for ($rowCount = $coordy; $rowCount <= 9; $rowCount++) {
        if (getDisc($updatedGameBoard, $checkCoordx + 1, $rowCount + 1) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $checkCoordx + 1, $rowCount + 1) == $playerRole) {
            $discCount++;
            $debugArray[] = ["direction" => "DOWN", "x" => $checkCoordx + 1, "y" => $rowCount + 1];
        }
        $checkCoordx++;
    }
    if ($discCount > 3) {
        $winner = $playerRole;
    }

    // Diagonal check from bottom left to top right
    // count towards the bottom left of current disc
    $discCount = 0;
    $debugArray = [];
    $checkCoordx = $coordx;
    for ($rowCount = $coordy; $rowCount >= -1; $rowCount--) {
        if (getDisc($updatedGameBoard, $checkCoordx + 1, $rowCount - 1) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $checkCoordx + 1, $rowCount - 1) == $playerRole) {
            $discCount++;
            $debugArray[] = ["direction" => "UP", "x" => $checkCoordx + 1, "y" => $rowCount - 1];
        }
        $checkCoordx++;
    }
    //count towards the top right of current disc
    $checkCoordx = $coordx;
    for ($rowCount = $coordy; $rowCount <= 9; $rowCount++) {
        if (getDisc($updatedGameBoard, $checkCoordx - 1, $rowCount + 1) != $playerRole) {
            break;
        } elseif (getDisc($updatedGameBoard, $checkCoordx - 1, $rowCount + 1) == $playerRole) {
            $discCount++;
            $debugArray[] = ["direction" => "DOWN", "x" => $checkCoordx - 1, "y" => $rowCount + 1];
        }
        $checkCoordx--;
    }
    if ($discCount > 3) {
        $winner = $playerRole;
    }

    //insert updated gameboard in database
    $this->database->update("games", [
        "game_board" => json_encode($updatedGameBoard),
        //"player_turn" => $updatedPlayerTurn,
        "player_turn" => $updatedPlayerTurn,
        "winner" => $winner
    ], [
        "invite_token" => $inviteToken
    ]);
    if ($this->database->error()[0] != "00000") {
        return $response->withJson(array(
            "error" => "An internal error occured."
        ), 500);
    } else {
        return $response->withJson(['coordx' => $coordx, 'coordy' => $coordy, 'gb' => json_encode($debugArray)]);
    }
});

//Run the API
$app->run();




/*
Common errors solving:

Slim message: Message: Identifier "" is not defined.
Php error Undefined variable: database
Problem: $this->$database->select
Solution: $this->database->select

Slim message: cannot use stdclass in foreach loop
Problem: json_decode($gameData[0]['players'])
Solution: json_decode($gameData[0]['players'], true)
*/
