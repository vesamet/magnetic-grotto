exports.up = function (knex) {
    return knex.schema.createTable('games', function (t) {
      t.increments('id').primary()
      t.json('current_state').notNullable()
      t.integer('created_by').notNullable()
      t.string('created_at').notNullable()
      t.string('ended_at').nullable()
      t.json('players').notNullable()
    })
  };
  
  exports.down = function (knex) {
    return knex.schema.dropTableIfExists('games')
  };

        /*
      [
        {
          playerTurn: <id of the player>, 
          winner: <null or player id>,
          board: {
           [{"x":1,"y":1,"disk":0},{"x":2,"y":1,"disk":0},{"x":3,"y":1,"disk":0},{"x":4,"y":1,"disk":0},{"x":5,"y":1,"disk":0},{"x":6,"y":1,"disk":0},{"x":7,"y":1,"disk":0},{"x":8,"y":1,"disk":0},{"x":1,"y":2,"disk":0},{"x":2,"y":2,"disk":0},{"x":3,"y":2,"disk":0},{"x":4,"y":2,"disk":0},{"x":5,"y":2,"disk":0},{"x":6,"y":2,"disk":0},{"x":7,"y":2,"disk":0},{"x":8,"y":2,"disk":0},{"x":1,"y":3,"disk":0},{"x":2,"y":3,"disk":0},{"x":3,"y":3,"disk":0},{"x":4,"y":3,"disk":0},{"x":5,"y":3,"disk":0},{"x":6,"y":3,"disk":0},{"x":7,"y":3,"disk":0},{"x":8,"y":3,"disk":0},{"x":1,"y":4,"disk":0},{"x":2,"y":4,"disk":0},{"x":3,"y":4,"disk":0},{"x":4,"y":4,"disk":0},{"x":5,"y":4,"disk":0},{"x":6,"y":4,"disk":0},{"x":7,"y":4,"disk":0},{"x":8,"y":4,"disk":0},{"x":1,"y":5,"disk":0},{"x":2,"y":5,"disk":0},{"x":3,"y":5,"disk":0},{"x":4,"y":5,"disk":0},{"x":5,"y":5,"disk":0},{"x":6,"y":5,"disk":0},{"x":7,"y":5,"disk":0},{"x":8,"y":5,"disk":0},{"x":1,"y":6,"disk":0},{"x":2,"y":6,"disk":0},{"x":3,"y":6,"disk":0},{"x":4,"y":6,"disk":0},{"x":5,"y":6,"disk":0},{"x":6,"y":6,"disk":0},{"x":7,"y":6,"disk":0},{"x":8,"y":6,"disk":0},{"x":1,"y":7,"disk":0},{"x":2,"y":7,"disk":0},{"x":3,"y":7,"disk":0},{"x":4,"y":7,"disk":0},{"x":5,"y":7,"disk":0},{"x":6,"y":7,"disk":0},{"x":7,"y":7,"disk":0},{"x":8,"y":7,"disk":0},{"x":1,"y":8,"disk":0},{"x":2,"y":8,"disk":0},{"x":3,"y":8,"disk":0},{"x":4,"y":8,"disk":0},{"x":5,"y":8,"disk":0},{"x":6,"y":8,"disk":0},{"x":7,"y":8,"disk":0},{"x":8,"y":8,"disk":0}]
            ...
            var gameBoard = [];
for (i = 1; i < 8; i++) {
  for (e = 1; e < 8; e++) {
  space = {x: e, y: i, disk: 0};
    gameBoard.push(space);
}
} 
console.log(gameBoard);
--------------
var gameBoard = [];
for (i = 1; i <= 8; i++) {
  for (e = 1; e <= 8; e++) {
  space = {x: e, y: i, disk: 0};
    gameBoard.push(space);
}
} 
console.log(gameBoard);
$('.lil').html(JSON.stringify(gameBoard));
--------------
          }
        }
      ]
      */