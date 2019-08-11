<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Medoo\Medoo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

require '../vendor/autoload.php';

//Init the API
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
        "created_at" => strval(time())
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
        "created_at" => strval(time())
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
            $playerName = [ $value['playerId'] => $playerName[0]['name']];
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
