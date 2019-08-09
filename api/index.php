<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Medoo\Medoo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

require '../vendor/autoload.php';

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

$app->get('/test', function (Request $request, Response $response, array $args) {

    return $response->getBody()->write(Uuid::uuid4()->toString());
});

//🌕 PLAYERS 
//👉 Create
function createPlayer($database, $name)
{
    $createPlayer = $database->insert('players', [
        "id" => null,
        "name" => strtolower(trim($name)),
        "wins" => 0,
        "defeats" => 0,
        "created_at" => strval(time())
    ]);
    return $createPlayer;
}
$app->post('/player', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    $name = filter_var($body['name'], FILTER_SANITIZE_STRING);
    if (empty($body['name'])) {
        return $response->withJson(array(
            "error:" => "No name defined."
        ), 400);
    }
    if (strlen($body['name']) > 25) {
        return $response->withJson(array(
            "error:" => "Your name should not have more than 25 characters."
        ), 400);
    }
    $player = createPlayer($this->database, $name);

    return $response->write(json_encode($player));
});

//👉 Read
function getPlayers($database)
{
    $players = $database->select('players', "*");
    return $players;
}
$app->get('/players', function (Request $request, Response $response, array $args) {
    $players = getPlayers($this->database);
    return $response->write(json_encode($players));
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
    return $response->write(json_encode($player));
});

//🌕 GAMES
//👉 Create
function createGame($database, $playerId)
{
    $playerToken = Uuid::uuid4()->toString();
    $createGame = $database->insert('games', [
        "id" => null,
        "current_state" => json_encode([
            "player_turn"=> 1,
            "game_board"=> json_decode('[{"x":1,"y":1,"disk":0},{"x":2,"y":1,"disk":0},{"x":3,"y":1,"disk":0},{"x":4,"y":1,"disk":0},{"x":5,"y":1,"disk":0},{"x":6,"y":1,"disk":0},{"x":7,"y":1,"disk":0},{"x":8,"y":1,"disk":0},{"x":1,"y":2,"disk":0},{"x":2,"y":2,"disk":0},{"x":3,"y":2,"disk":0},{"x":4,"y":2,"disk":0},{"x":5,"y":2,"disk":0},{"x":6,"y":2,"disk":0},{"x":7,"y":2,"disk":0},{"x":8,"y":2,"disk":0},{"x":1,"y":3,"disk":0},{"x":2,"y":3,"disk":0},{"x":3,"y":3,"disk":0},{"x":4,"y":3,"disk":0},{"x":5,"y":3,"disk":0},{"x":6,"y":3,"disk":0},{"x":7,"y":3,"disk":0},{"x":8,"y":3,"disk":0},{"x":1,"y":4,"disk":0},{"x":2,"y":4,"disk":0},{"x":3,"y":4,"disk":0},{"x":4,"y":4,"disk":0},{"x":5,"y":4,"disk":0},{"x":6,"y":4,"disk":0},{"x":7,"y":4,"disk":0},{"x":8,"y":4,"disk":0},{"x":1,"y":5,"disk":0},{"x":2,"y":5,"disk":0},{"x":3,"y":5,"disk":0},{"x":4,"y":5,"disk":0},{"x":5,"y":5,"disk":0},{"x":6,"y":5,"disk":0},{"x":7,"y":5,"disk":0},{"x":8,"y":5,"disk":0},{"x":1,"y":6,"disk":0},{"x":2,"y":6,"disk":0},{"x":3,"y":6,"disk":0},{"x":4,"y":6,"disk":0},{"x":5,"y":6,"disk":0},{"x":6,"y":6,"disk":0},{"x":7,"y":6,"disk":0},{"x":8,"y":6,"disk":0},{"x":1,"y":7,"disk":0},{"x":2,"y":7,"disk":0},{"x":3,"y":7,"disk":0},{"x":4,"y":7,"disk":0},{"x":5,"y":7,"disk":0},{"x":6,"y":7,"disk":0},{"x":7,"y":7,"disk":0},{"x":8,"y":7,"disk":0},{"x":1,"y":8,"disk":0},{"x":2,"y":8,"disk":0},{"x":3,"y":8,"disk":0},{"x":4,"y":8,"disk":0},{"x":5,"y":8,"disk":0},{"x":6,"y":8,"disk":0},{"x":7,"y":8,"disk":0},{"x":8,"y":8,"disk":0}]'),
            "winner"=> 0
        ]),
        "created_by" => $playerId,
        "players" => json_encode(array([
            $playerId => $playerToken
        ])),
        "created_at" => strval(time())
    ]);
    return $createGame;
}
$app->post('/game', function (Request $request, Response $response, array $args) {
    $body = $request->getParsedBody();
    $playerId = $body['playerId'];
    if (!is_numeric($playerId)) {
        return $response->withJson(array(
            "error:" => "The id given isn't in a valid format."
        ), 400);
    }
    if (empty($playerId)) {
        return $response->withJson(array(
            "error:" => "No player id given for the game initialization."
        ), 400);
    }
    $game = createGame($this->database, $playerId);
    return $response->write(json_encode($game)); //$this->$database->id()
});
//👉 Read
function getGames($database)
{
    $games = $database->select('games', "*");
    return $games;
}
$app->get('/games', function (Request $request, Response $response, array $args) {
    $games = getGames($this->database);
    return $response->write(json_encode($games));
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
    return $response->write(json_encode($game));
});

$app->run();
