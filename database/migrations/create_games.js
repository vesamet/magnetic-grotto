exports.up = function (knex) {
    return knex.schema.createTable('games', function (t) {
      t.increments('id').primary()
      t.string('invite_token').notNullable()
      t.json('game_board').notNullable()
      t.json('players').notNullable()
      t.integer('player_turn').notNullable()
      t.integer('winner').nullable()
      t.integer('is_full').notNullable();
      t.integer('player_resigned').nullable()
      t.integer('created_by').notNullable()
      t.string('created_at').notNullable()
      t.string('ended_at').nullable()
    })
  };
  
  exports.down = function (knex) {
    return knex.schema.dropTableIfExists('games')
  };